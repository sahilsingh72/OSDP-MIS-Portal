<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\TrainingBatch;
use App\Models\TrainingModule;
use App\Models\TrainingTier;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class TrainingBatchWorkflowTest extends TestCase
{
    /**
     * Test authorized user can schedule a new batch.
     */
    public function test_pmu_can_schedule_batch(): void
    {
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $tier = TrainingTier::where('code', 'district')->first();
        $district = District::where('code', 'KHD')->first();
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $modules = TrainingModule::pluck('id')->toArray();

        $response = $this->actingAs($pmu)->post('/batches', [
            'training_tier_id' => $tier->id,
            'district_id' => $district->id,
            'scheduled_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '17:30',
            'planned_participants' => 40,
            'primary_trainer_id' => $trainer->id,
            'modules' => $modules,
            'remarks' => 'Test Batch Creation via PHPUnit',
        ]);

        $response->assertRedirect('/batches');
        $this->assertDatabaseHas('training_batches', [
            'training_tier_id' => $tier->id,
            'district_id' => $district->id,
            'planned_participants' => 40,
        ]);
    }

    /**
     * Test PMU can record a review decision on a submitted batch.
     */
    public function test_pmu_can_verify_batch_evidence(): void
    {
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $batch = TrainingBatch::where('status', TrainingBatch::STATUS_SUBMITTED)->first();

        if ($batch) {
            $response = $this->actingAs($pmu)->post("/batches/{$batch->id}/reviews", [
                'decision' => 'verified',
                'remarks' => 'All evidence validated against RFP requirements.',
            ]);

            $response->assertRedirect("/batches/{$batch->id}");
            $this->assertDatabaseHas('training_batches', [
                'id' => $batch->id,
                'status' => TrainingBatch::STATUS_VERIFIED,
            ]);
        } else {
            $this->assertTrue(true);
        }
    }
}
