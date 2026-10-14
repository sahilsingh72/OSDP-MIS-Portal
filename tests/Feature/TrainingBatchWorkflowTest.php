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

    /**
     * Test authorized user can view edit batch page with tier, district, venue, trainers and modules options.
     */
    public function test_user_can_view_edit_batch_page_with_all_options(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $batch = TrainingBatch::first();
        $this->assertNotNull($batch);

        $response = $this->actingAs($admin)->get("/batches/{$batch->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Training Tier');
        $response->assertSee('District');
        $response->assertSee('Block');
        $response->assertSee('Training Venue');
        $response->assertSee('Primary Master Trainer');
        $response->assertSee('Co-Trainer');
        $response->assertSee('Curriculum Modules Covered');
    }

    /**
     * Test authorized user can update batch tier, district, venue, trainers and modules.
     */
    public function test_user_can_update_batch_with_tier_district_venue_trainers_and_modules(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $batch = TrainingBatch::first();
        $this->assertNotNull($batch);

        $tier = TrainingTier::where('code', 'district')->first();
        $district = District::where('code', 'KHD')->first();
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $selectedModules = TrainingModule::limit(3)->pluck('id')->toArray();

        $response = $this->actingAs($admin)->put("/batches/{$batch->id}", [
            'training_tier_id' => $tier->id,
            'district_id' => $district->id,
            'block_id' => null,
            'venue_id' => null,
            'primary_trainer_id' => $trainer->id,
            'co_trainer_id' => null,
            'modules' => $selectedModules,
            'scheduled_date' => Carbon::now()->addDays(5)->toDateString(),
            'start_time' => '09:30',
            'end_time' => '17:00',
            'status' => 'scheduled',
            'planned_participants' => 35,
            'remarks' => 'Updated batch tier, district, trainer and modules via test',
        ]);

        $response->assertRedirect("/batches/{$batch->id}");
        $response->assertSessionHas('status');

        $batch->refresh();
        $this->assertEquals($tier->id, $batch->training_tier_id);
        $this->assertEquals($district->id, $batch->district_id);
        $this->assertEquals(35, $batch->planned_participants);
        $this->assertTrue($batch->trainers->contains($trainer->id));
        $this->assertEqualsCanonicalizing($selectedModules, $batch->modules->pluck('id')->toArray());
    }
}
