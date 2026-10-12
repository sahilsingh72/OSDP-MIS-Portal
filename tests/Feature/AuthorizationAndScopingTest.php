<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\TrainingBatch;
use App\Models\User;
use Tests\TestCase;

class AuthorizationAndScopingTest extends TestCase
{
    /**
     * Test guest cannot access protected batch management routes.
     */
    public function test_guest_cannot_access_batches(): void
    {
        $response = $this->get('/batches');
        $response->assertRedirect('/login');
    }

    /**
     * Test District Coordinator can only see batches within assigned district.
     */
    public function test_district_coordinator_cannot_view_batches_from_unassigned_districts(): void
    {
        $dcKhordha = User::where('email', 'dc.khordha@osdp.gov.in')->first();
        $cuttack = District::where('code', 'CTC')->first();

        // Batch located in Cuttack
        $cuttackBatch = TrainingBatch::where('district_id', $cuttack->id)->first();
        $this->assertNotNull($cuttackBatch);

        // Khordha DC attempts to access Cuttack batch
        $response = $this->actingAs($dcKhordha)->get("/batches/{$cuttackBatch->id}");

        $response->assertStatus(403);
    }

    /**
     * Test Field Trainer cannot approve or review their own batches.
     */
    public function test_field_trainer_cannot_review_batches(): void
    {
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $batch = TrainingBatch::whereHas('trainers', function ($q) use ($trainer) {
            $q->where('users.id', $trainer->id);
        })->first();
        $this->assertNotNull($batch);

        $response = $this->actingAs($trainer)->post("/batches/{$batch->id}/reviews", [
            'decision' => 'verified',
            'remarks' => 'Attempting self-approval',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test Super Admin and PMU have statewide access to any batch.
     */
    public function test_pmu_can_access_any_district_batch(): void
    {
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $batch = TrainingBatch::first();
        $this->assertNotNull($batch);

        $response = $this->actingAs($pmu)->get("/batches/{$batch->id}");
        $response->assertStatus(200);
    }
}
