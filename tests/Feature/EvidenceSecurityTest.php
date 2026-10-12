<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\TrainingBatch;
use App\Models\TrainingUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceSecurityTest extends TestCase
{
    /**
     * Test uploading an invalid executable or script file is strictly rejected.
     */
    public function test_uploading_invalid_file_type_is_rejected(): void
    {
        Storage::fake('evidence');
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $batch = TrainingBatch::whereHas('trainers', function ($q) use ($trainer) {
            $q->where('users.id', $trainer->id);
        })->first();

        $batch->update(['status' => TrainingBatch::STATUS_AWAITING_EVIDENCE]);

        $invalidFile = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');

        $response = $this->actingAs($trainer)->post("/batches/{$batch->id}/evidence", [
            'evidence_type' => 'attendance_sheet',
            'file' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    /**
     * Test unauthorized user from another district cannot download private evidence.
     */
    public function test_unauthorized_user_cannot_download_private_evidence(): void
    {
        Storage::fake('evidence');
        $cuttack = District::where('code', 'CTC')->first();
        $cuttackBatch = TrainingBatch::where('district_id', $cuttack->id)->first();
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();

        // Create an evidence upload record
        $upload = TrainingUpload::create([
            'training_batch_id' => $cuttackBatch->id,
            'evidence_type' => TrainingUpload::TYPE_ATTENDANCE,
            'original_filename' => 'attendance_signed.pdf',
            'file_path' => "batches/{$cuttackBatch->batch_code}/test.pdf",
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'sample content'),
            'uploaded_by' => $trainer->id,
            'version' => 1,
            'is_valid' => true,
        ]);

        // DC of Khordha attempts to download Cuttack batch evidence
        $dcKhordha = User::where('email', 'dc.khordha@osdp.gov.in')->first();
        $response = $this->actingAs($dcKhordha)->get("/evidence/{$upload->id}/download");

        $response->assertStatus(403);
    }
}
