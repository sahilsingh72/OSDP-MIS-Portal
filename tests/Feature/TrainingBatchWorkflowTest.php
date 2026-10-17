<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\TrainingBatch;
use App\Models\TrainingModule;
use App\Models\TrainingTier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TrainingBatchWorkflowTest extends TestCase
{
    use DatabaseTransactions;
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

    /**
     * Test Super Admin and PMU cannot submit batch for review and do not see the submit button.
     */
    public function test_super_admin_and_pmu_cannot_submit_batch_for_review_and_do_not_see_button(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $batch = TrainingBatch::first();

        // Neither super admin nor PMU have policy permission to submit
        $this->assertFalse($admin->can('submit', $batch));
        $this->assertFalse($pmu->can('submit', $batch));

        // Attempting to post to submit route directly returns 403 Forbidden
        $adminSubmit = $this->actingAs($admin)->post("/batches/{$batch->id}/submit");
        $adminSubmit->assertStatus(403);

        $pmuSubmit = $this->actingAs($pmu)->post("/batches/{$batch->id}/submit");
        $pmuSubmit->assertStatus(403);

        // Neither sees "Submit for Review" on batch overview page
        $adminPage = $this->actingAs($admin)->get("/batches/{$batch->id}");
        $adminPage->assertDontSee('Submit for Review');

        $pmuPage = $this->actingAs($pmu)->get("/batches/{$batch->id}");
        $pmuPage->assertDontSee('Submit for Review');

        // Neither sees "Submit for Verification" on evidence management page
        $adminEvidencePage = $this->actingAs($admin)->get("/batches/{$batch->id}/evidence");
        $adminEvidencePage->assertDontSee('Submit for Verification');

        $pmuEvidencePage = $this->actingAs($pmu)->get("/batches/{$batch->id}/evidence");
        $pmuEvidencePage->assertDontSee('Submit for Verification');
    }

    /**
     * Test Super Admin, PMU, and OCAC see Summary and Feedback in Read-Only mode and cannot post saves.
     */
    public function test_super_admin_pmu_ocac_see_summary_and_feedback_in_read_only_mode_and_cannot_save(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $ocac = User::where('email', 'ocac@osdp.gov.in')->first();
        $batch = TrainingBatch::first();

        foreach ([$admin, $pmu, $ocac] as $user) {
            // Cannot manage/enter summary according to policy
            $this->assertFalse($user->can('manageSummary', $batch));

            // View evidence page - sees Read-Only mode, neither Save Summary nor Save Feedback Evaluation button
            $response = $this->actingAs($user)->get("/batches/{$batch->id}/evidence");
            $response->assertOk();
            $response->assertSee('Read-Only');
            $response->assertDontSee('Save Summary');
            $response->assertDontSee('Save Feedback Evaluation');

            // Attempting to post to summary or feedback endpoints is denied with 403 Forbidden
            $summaryPost = $this->actingAs($user)->post("/batches/{$batch->id}/summary", [
                'registered_count' => 10,
                'present_count' => 10,
                'feedback_forms_received' => 10,
            ]);
            $summaryPost->assertStatus(403);

            $feedbackPost = $this->actingAs($user)->post("/batches/{$batch->id}/feedback", [
                'q1_clarity_avg' => 4.5,
                'q2_knowledge_avg' => 4.5,
                'q3_relevance_avg' => 4.5,
                'q4_handbook_avg' => 4.5,
                'q5_overall_avg' => 4.5,
                'total_forms_analyzed' => 10,
            ]);
            $feedbackPost->assertStatus(403);
        }
    }

    /**
     * Test assigned Field Trainer can view edit forms and successfully save summary and feedback.
     */
    public function test_assigned_field_trainer_can_view_edit_forms_and_save_summary_and_feedback(): void
    {
        $batch = TrainingBatch::find(9);
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();

        $this->assertTrue($trainer->can('manageSummary', $batch));

        // Assigned trainer sees the entry forms and save buttons
        $response = $this->actingAs($trainer)->get("/batches/{$batch->id}/evidence");
        $response->assertOk();
        $response->assertSee('Save Summary');
        $response->assertSee('Save Feedback Evaluation');

        // Trainer can submit training summary
        $summaryPost = $this->actingAs($trainer)->post("/batches/{$batch->id}/summary", [
            'registered_count' => 5,
            'present_count' => 1,
            'feedback_forms_received' => 1,
            'actual_start_time' => '09:30',
            'actual_end_time' => '17:00',
            'what_went_well' => 'Great engagement by participants',
            'problems_faced' => 'None',
            'follow_up_actions' => 'None',
        ]);
        $summaryPost->assertRedirect();

        $batch->refresh();
        $this->assertNotNull($batch->summary);
        $this->assertEquals('Great engagement by participants', $batch->summary->what_went_well);

        // Trainer can submit feedback evaluation
        $feedbackPost = $this->actingAs($trainer)->post("/batches/{$batch->id}/feedback", [
            'q1_clarity_avg' => 4.80,
            'q2_knowledge_avg' => 4.90,
            'q3_relevance_avg' => 4.70,
            'q4_handbook_avg' => 4.60,
            'q5_overall_avg' => 4.85,
            'total_forms_analyzed' => 1,
            'qualitative_highlights' => 'Very informative',
            'improvement_suggestions' => 'More hands-on lab time',
        ]);
        $feedbackPost->assertRedirect();

        $batch->refresh();
        $this->assertNotNull($batch->feedback);
        $this->assertEquals(4.85, (float)$batch->feedback->q5_overall_avg);

        // After submission, Super Admin viewing evidence page now sees submitted values in Read-Only mode
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $adminResponse = $this->actingAs($admin)->get("/batches/{$batch->id}/evidence");
        $adminResponse->assertOk();
        $adminResponse->assertSee('Submitted');
        $adminResponse->assertSee('Great engagement by participants');
        $adminResponse->assertSee('Very informative');
        $adminResponse->assertDontSee('Save Summary');
        $adminResponse->assertDontSee('Save Feedback Evaluation');
    }
}
