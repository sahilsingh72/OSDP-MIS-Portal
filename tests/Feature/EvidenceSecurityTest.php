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

    /**
     * Test authorized user can edit and replace evidence file with version bump.
     */
    public function test_authorized_user_can_edit_and_replace_evidence_file(): void
    {
        Storage::fake('evidence');
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $batch = TrainingBatch::whereHas('trainers', function ($q) use ($trainer) {
            $q->where('users.id', $trainer->id);
        })->first();

        $batch->update(['status' => TrainingBatch::STATUS_AWAITING_EVIDENCE]);

        $upload = TrainingUpload::create([
            'training_batch_id' => $batch->id,
            'evidence_type' => TrainingUpload::TYPE_ATTENDANCE,
            'original_filename' => 'old_attendance.pdf',
            'file_path' => "batches/{$batch->batch_code}/old.pdf",
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'old content'),
            'uploaded_by' => $trainer->id,
            'version' => 1,
            'is_valid' => true,
        ]);

        $newFile = UploadedFile::fake()->create('corrected_attendance.pdf', 500, 'application/pdf');

        $response = $this->actingAs($trainer)->put("/evidence/{$upload->id}", [
            'evidence_type' => TrainingUpload::TYPE_ATTENDANCE,
            'file' => $newFile,
            'remarks' => 'Replaced blurry page 2 with clean scan',
        ]);

        $response->assertRedirect();
        $upload->refresh();

        $this->assertEquals('corrected_attendance.pdf', $upload->original_filename);
        $this->assertEquals(2, $upload->version);
        $this->assertEquals('Replaced blurry page 2 with clean scan', $upload->remarks);
        $this->assertTrue(Storage::disk('evidence')->exists($upload->file_path));
    }

    /**
     * Test authorized user can edit evidence metadata without uploading new file.
     */
    public function test_authorized_user_can_edit_evidence_without_new_file(): void
    {
        Storage::fake('evidence');
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $batch = TrainingBatch::whereHas('trainers', function ($q) use ($trainer) {
            $q->where('users.id', $trainer->id);
        })->first();

        $batch->update(['status' => TrainingBatch::STATUS_AWAITING_EVIDENCE]);

        $upload = TrainingUpload::create([
            'training_batch_id' => $batch->id,
            'evidence_type' => TrainingUpload::TYPE_PHOTO_1,
            'original_filename' => 'classroom_front.jpg',
            'file_path' => "batches/{$batch->batch_code}/front.jpg",
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => 2048,
            'file_hash' => hash('sha256', 'front image'),
            'uploaded_by' => $trainer->id,
            'version' => 1,
            'is_valid' => true,
        ]);

        $response = $this->actingAs($trainer)->put("/evidence/{$upload->id}", [
            'evidence_type' => TrainingUpload::TYPE_PHOTO_2,
            'remarks' => 'Re-classified as trainer teaching photo',
        ]);

        $response->assertRedirect();
        $upload->refresh();

        $this->assertEquals(TrainingUpload::TYPE_PHOTO_2, $upload->evidence_type);
        $this->assertEquals('Re-classified as trainer teaching photo', $upload->remarks);
        $this->assertEquals(1, $upload->version);
    }

    /**
     * Test unauthorized user cannot edit evidence.
     */
    public function test_unauthorized_user_cannot_edit_evidence(): void
    {
        Storage::fake('evidence');
        $cuttack = District::where('code', 'CTC')->first();
        $cuttackBatch = TrainingBatch::where('district_id', $cuttack->id)->first();
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();

        $upload = TrainingUpload::create([
            'training_batch_id' => $cuttackBatch->id,
            'evidence_type' => TrainingUpload::TYPE_ATTENDANCE,
            'original_filename' => 'attendance.pdf',
            'file_path' => "batches/{$cuttackBatch->batch_code}/test.pdf",
            'mime_type' => 'application/pdf',
            'file_size_bytes' => 1024,
            'file_hash' => hash('sha256', 'content'),
            'uploaded_by' => $trainer->id,
            'version' => 1,
            'is_valid' => true,
        ]);

        $dcKhordha = User::where('email', 'dc.khordha@osdp.gov.in')->first();
        $response = $this->actingAs($dcKhordha)->put("/evidence/{$upload->id}", [
            'evidence_type' => TrainingUpload::TYPE_ATTENDANCE,
            'remarks' => 'Hacking attempt',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test cannot edit evidence on verified locked batch.
     */
    public function test_cannot_edit_evidence_on_verified_batch(): void
    {
        Storage::fake('evidence');
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $batch = TrainingBatch::whereHas('trainers', function ($q) use ($trainer) {
            $q->where('users.id', $trainer->id);
        })->first();

        $origStatus = $batch->status;
        try {
            $batch->update(['status' => TrainingBatch::STATUS_VERIFIED]);

            $upload = TrainingUpload::create([
                'training_batch_id' => $batch->id,
                'evidence_type' => TrainingUpload::TYPE_ATTENDANCE,
                'original_filename' => 'attendance.pdf',
                'file_path' => "batches/{$batch->batch_code}/test.pdf",
                'mime_type' => 'application/pdf',
                'file_size_bytes' => 1024,
                'file_hash' => hash('sha256', 'content'),
                'uploaded_by' => $trainer->id,
                'version' => 1,
                'is_valid' => true,
            ]);

            $response = $this->actingAs($trainer)->put("/evidence/{$upload->id}", [
                'evidence_type' => TrainingUpload::TYPE_ATTENDANCE,
                'remarks' => 'Attempt edit on locked batch',
            ]);

            $response->assertStatus(403);
        } finally {
            $batch->update(['status' => $origStatus]);
        }
    }
}
