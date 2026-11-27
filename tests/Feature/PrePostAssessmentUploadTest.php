<?php

namespace Tests\Feature;

use App\Models\TrainingBatch;
use App\Models\TrainingUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrePostAssessmentUploadTest extends TestCase
{
    /**
     * Test authorized trainer can upload Pre-Assessment PDF (Part A) and Post-Assessment PDF (Part B).
     */
    public function test_trainer_can_upload_pre_and_post_assessment_pdfs(): void
    {
        Storage::fake('evidence');
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $batch = TrainingBatch::whereHas('trainers', function ($q) use ($trainer) {
            $q->where('users.id', $trainer->id);
        })->first();

        $batch->update(['status' => TrainingBatch::STATUS_AWAITING_EVIDENCE]);

        // Clean any existing test uploads for these types
        TrainingUpload::where('training_batch_id', $batch->id)
            ->whereIn('evidence_type', [TrainingUpload::TYPE_PRE_ASSESSMENT, TrainingUpload::TYPE_POST_ASSESSMENT])
            ->delete();

        // 1. Upload Pre-Assessment Form (all participants in one PDF)
        $preFile = UploadedFile::fake()->create('batch_pre_assessment_all.pdf', 800, 'application/pdf');
        $preResponse = $this->actingAs($trainer)->post("/batches/{$batch->id}/evidence", [
            'evidence_type' => TrainingUpload::TYPE_PRE_ASSESSMENT,
            'file' => $preFile,
            'remarks' => 'Pre-assessment forms for all 30 participants scanned into single PDF',
        ]);

        $preResponse->assertRedirect();
        $this->assertDatabaseHas('training_uploads', [
            'training_batch_id' => $batch->id,
            'evidence_type' => TrainingUpload::TYPE_PRE_ASSESSMENT,
            'original_filename' => 'batch_pre_assessment_all.pdf',
            'is_valid' => true,
        ]);

        // 2. Upload Post-Assessment Form (all participants in one PDF)
        $postFile = UploadedFile::fake()->create('batch_post_assessment_all.pdf', 900, 'application/pdf');
        $postResponse = $this->actingAs($trainer)->post("/batches/{$batch->id}/evidence", [
            'evidence_type' => TrainingUpload::TYPE_POST_ASSESSMENT,
            'file' => $postFile,
            'remarks' => 'Post-assessment forms for all 30 participants scanned into single PDF',
        ]);

        $postResponse->assertRedirect();
        $this->assertDatabaseHas('training_uploads', [
            'training_batch_id' => $batch->id,
            'evidence_type' => TrainingUpload::TYPE_POST_ASSESSMENT,
            'original_filename' => 'batch_post_assessment_all.pdf',
            'is_valid' => true,
        ]);

        // 3. Verify batch evidence status includes pre_assessment and post_assessment
        $evidenceStatus = $batch->fresh()->evidence_status;
        $this->assertEquals(8, $evidenceStatus['total_required']);
        $this->assertTrue($evidenceStatus['items']['pre_assessment']);
        $this->assertTrue($evidenceStatus['items']['post_assessment']);

        // 4. Verify evidence center page renders both items and shows locked state for them
        $viewResponse = $this->actingAs($trainer)->get("/batches/{$batch->id}/evidence");
        $viewResponse->assertOk();
        $viewResponse->assertSee('Pre-Assessment Forms (Part A)');
        $viewResponse->assertSee('Post-Assessment Forms (Part B)');
        $viewResponse->assertSee('batch_pre_assessment_all.pdf');
        $viewResponse->assertSee('batch_post_assessment_all.pdf');
    }

    /**
     * Test uploading non-PDF file for Pre or Post Assessment is strictly rejected.
     */
    public function test_non_pdf_file_for_assessments_is_rejected(): void
    {
        Storage::fake('evidence');
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $batch = TrainingBatch::whereHas('trainers', function ($q) use ($trainer) {
            $q->where('users.id', $trainer->id);
        })->first();

        $batch->update(['status' => TrainingBatch::STATUS_AWAITING_EVIDENCE]);

        TrainingUpload::where('training_batch_id', $batch->id)
            ->whereIn('evidence_type', [TrainingUpload::TYPE_PRE_ASSESSMENT, TrainingUpload::TYPE_POST_ASSESSMENT])
            ->delete();

        // Image file instead of PDF
        $imgFile = UploadedFile::fake()->create('pre_assessment.jpg', 300, 'image/jpeg');
        $response = $this->actingAs($trainer)->post("/batches/{$batch->id}/evidence", [
            'evidence_type' => TrainingUpload::TYPE_PRE_ASSESSMENT,
            'file' => $imgFile,
        ]);

        $response->assertSessionHasErrors('file');

        // Video file instead of PDF
        $videoFile = UploadedFile::fake()->create('post_assessment.mp4', 1500, 'video/mp4');
        $response2 = $this->actingAs($trainer)->post("/batches/{$batch->id}/evidence", [
            'evidence_type' => TrainingUpload::TYPE_POST_ASSESSMENT,
            'file' => $videoFile,
        ]);

        $response2->assertSessionHasErrors('file');
    }
}
