<?php

namespace Tests\Feature;

use App\Models\BatchFeedback;
use App\Models\BatchParticipant;
use App\Models\Participant;
use App\Models\ParticipantFeedback;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParticipantFeedbackModuleTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $coordinator;
    protected User $unauthorizedUser;
    protected TrainingBatch $batch;
    protected BatchParticipant $batchParticipant;
    protected Participant $participant;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Super Admin
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'admin@osdp.gov.in'],
            [
                'name' => 'State Super Admin',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        if (!$this->superAdmin->hasRole('super-admin')) {
            $this->superAdmin->assignRole('super-admin');
        }

        // 2. Coordinator with feedback permissions
        $this->coordinator = User::firstOrCreate(
            ['email' => 'dc_feedback_test@osdp.gov.in'],
            [
                'name' => 'DC Feedback Tester',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $role = Role::findOrCreate('district-coordinator');
        $role->givePermissionTo(['feedback.view', 'feedback.manage', 'batches.view', 'batches.update']);
        if (!$this->coordinator->hasRole('district-coordinator')) {
            $this->coordinator->assignRole('district-coordinator');
        }

        // 3. User with no feedback permissions
        $this->unauthorizedUser = User::firstOrCreate(
            ['email' => 'noperm_feedback@osdp.gov.in'],
            [
                'name' => 'No Perm User',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $emptyRole = Role::findOrCreate('viewer-only-test');
        $emptyRole->syncPermissions([]);
        $this->unauthorizedUser->syncRoles(['viewer-only-test']);

        // 4. Batch & Participant setup
        $existing = TrainingBatch::first();
        $this->batch = TrainingBatch::create([
            'batch_code' => 'FB-TEST-' . uniqid(),
            'training_plan_id' => $existing->training_plan_id,
            'training_tier_id' => $existing?->training_tier_id ?? 1,
            'district_id' => $existing?->district_id ?? 1,
            'status' => 'scheduled',
            'scheduled_date' => now()->toDateString(),
            'created_by' => $this->superAdmin->id,
        ]);
        $this->coordinator->assignedDistricts()->sync([$this->batch->district_id]);

        $this->participant = Participant::create([
            'name' => 'Feedback Trainee ' . uniqid(),
            'designation' => 'Panchayat Executive Officer',
            'office_name' => 'Kantapada GP',
            'department' => 'P&RD',
            'phone' => '9861234567',
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        $this->batchParticipant = BatchParticipant::create([
            'training_batch_id' => $this->batch->id,
            'participant_id' => $this->participant->id,
            'serial_number' => 1,
        ]);
    }

    /**
     * Test Super Admin can assign and revoke feedback permissions to specific roles via Permission Module.
     */
    public function test_super_admin_can_assign_and_revoke_feedback_permissions_to_role(): void
    {
        $role = Role::findOrCreate('test-field-assistant');
        $role->syncPermissions([]);

        // Super Admin assigns feedback permissions
        $response = $this->actingAs($this->superAdmin)->put("/roles/{$role->name}/permissions", [
            'permissions' => ['feedback.view', 'feedback.manage'],
        ]);
        $response->assertRedirect('/roles');

        $this->assertTrue($role->fresh()->hasPermissionTo('feedback.view'));
        $this->assertTrue($role->fresh()->hasPermissionTo('feedback.manage'));

        // Super Admin revokes feedback.manage, keeps feedback.view
        $response2 = $this->actingAs($this->superAdmin)->put("/roles/{$role->name}/permissions", [
            'permissions' => ['feedback.view'],
        ]);
        $response2->assertRedirect('/roles');

        $this->assertTrue($role->fresh()->hasPermissionTo('feedback.view'));
        $this->assertFalse($role->fresh()->hasPermissionTo('feedback.manage'));
    }

    /**
     * Test unauthorized user cannot view or manage feedback.
     */
    public function test_unauthorized_user_cannot_access_feedback_module(): void
    {
        // Guest redirected to login
        $this->get('/feedback')->assertRedirect('/login');
        $this->get("/batches/{$this->batch->id}/feedback-forms")->assertRedirect('/login');

        // User without permission gets 403 Forbidden
        $this->actingAs($this->unauthorizedUser)->get('/feedback')->assertForbidden();
        $this->actingAs($this->unauthorizedUser)->get("/batches/{$this->batch->id}/feedback-forms")->assertForbidden();

        // Direct POST without permission gets 403
        $this->actingAs($this->unauthorizedUser)->post("/batches/{$this->batch->id}/participants/{$this->batchParticipant->id}/feedback", [
            'q1_clarity' => 5,
            'q2_knowledge' => 5,
            'q3_relevance' => 5,
            'q4_handbook' => 5,
            'q5_overall' => 5,
        ])->assertForbidden();
    }

    /**
     * Test authorized user can view feedback batches directory and batch transcription view.
     */
    public function test_authorized_user_can_view_feedback_pages(): void
    {
        $response = $this->actingAs($this->coordinator)->get('/feedback');
        $response->assertOk();
        $response->assertSee('Participant Feedback Register');
        $response->assertSee($this->batch->batch_code);

        $batchResponse = $this->actingAs($this->coordinator)->get("/batches/{$this->batch->id}/feedback-forms");
        $batchResponse->assertOk();
        $batchResponse->assertSee($this->batch->batch_code);
        $batchResponse->assertSee($this->participant->name);
        $batchResponse->assertSee('PARTICIPANT FEEDBACK FORM');
    }

    /**
     * Test authorized coordinator can transcribe Form 1 physical feedback for enrolled participant.
     */
    public function test_authorized_user_can_transcribe_form_1_participant_feedback(): void
    {
        $response = $this->actingAs($this->coordinator)->post("/batches/{$this->batch->id}/participants/{$this->batchParticipant->id}/feedback", [
            'q1_clarity' => 5,
            'q2_knowledge' => 4,
            'q3_relevance' => 5,
            'q4_handbook' => 4,
            'q5_overall' => 5,
            'q6_most_useful' => 'Hands-on portal training and GP data entry examples',
            'q7_improvements' => 'Provide more printed handbooks',
            'q8_unanswered' => 'How to verify Aadhaar offline?',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // Verify participant_feedback entry
        $feedback = ParticipantFeedback::where('training_batch_id', $this->batch->id)
            ->where('batch_participant_id', $this->batchParticipant->id)
            ->first();

        $this->assertNotNull($feedback);
        $this->assertEquals(5, $feedback->q1_clarity);
        $this->assertEquals(4, $feedback->q2_knowledge);
        $this->assertEquals(5, $feedback->q3_relevance);
        $this->assertEquals(4, $feedback->q4_handbook);
        $this->assertEquals(5, $feedback->q5_overall);
        $this->assertEquals(4.60, (float) $feedback->average_rating);
        $this->assertEquals('Hands-on portal training and GP data entry examples', $feedback->q6_most_useful);
        $this->assertEquals($this->coordinator->id, $feedback->entered_by);

        // Verify batch_feedback aggregates automatically updated
        $batchFeedback = BatchFeedback::where('training_batch_id', $this->batch->id)->first();
        $this->assertNotNull($batchFeedback);
        $this->assertEquals(5.00, (float) $batchFeedback->q1_clarity_avg);
        $this->assertEquals(4.00, (float) $batchFeedback->q2_knowledge_avg);
        $this->assertEquals(5.00, (float) $batchFeedback->q5_overall_avg);
        $this->assertGreaterThanOrEqual(1, $batchFeedback->total_forms_analyzed);
        $this->assertStringContainsString('Hands-on portal training', $batchFeedback->qualitative_highlights);
    }

    /**
     * Test Form 1 ratings Q1-Q5 and IN YOUR OWN WORDS Q6-Q8 are strictly mandatory.
     */
    public function test_form_1_ratings_must_be_between_1_and_5_and_mandatory(): void
    {
        // 1. Missing ratings and missing open-ended text fail validation
        $response = $this->actingAs($this->coordinator)->post("/batches/{$this->batch->id}/participants/{$this->batchParticipant->id}/feedback", [
            'q1_clarity' => '',
            'q2_knowledge' => '',
            'q3_relevance' => '',
            'q4_handbook' => '',
            'q5_overall' => '',
            'q6_most_useful' => '',
            'q7_improvements' => '',
            'q8_unanswered' => '',
        ]);
        $response->assertSessionHasErrors([
            'q1_clarity', 'q2_knowledge', 'q3_relevance', 'q4_handbook', 'q5_overall',
            'q6_most_useful', 'q7_improvements', 'q8_unanswered'
        ]);

        // 2. Out of range ratings fail validation even if open-ended questions are provided
        $outOfRangeResponse = $this->actingAs($this->coordinator)->post("/batches/{$this->batch->id}/participants/{$this->batchParticipant->id}/feedback", [
            'q1_clarity' => 0,
            'q2_knowledge' => 6,
            'q3_relevance' => 10,
            'q4_handbook' => -1,
            'q5_overall' => 7,
            'q6_most_useful' => 'Useful',
            'q7_improvements' => 'None',
            'q8_unanswered' => 'None',
        ]);
        $outOfRangeResponse->assertSessionHasErrors(['q1_clarity', 'q2_knowledge', 'q3_relevance', 'q4_handbook', 'q5_overall']);
    }

    /**
     * Test user can delete participant feedback and batch aggregate recalculates.
     */
    public function test_authorized_user_can_delete_participant_feedback(): void
    {
        $feedback = ParticipantFeedback::create([
            'training_batch_id' => $this->batch->id,
            'batch_participant_id' => $this->batchParticipant->id,
            'participant_id' => $this->participant->id,
            'q1_clarity' => 5,
            'q2_knowledge' => 5,
            'q3_relevance' => 5,
            'q4_handbook' => 5,
            'q5_overall' => 5,
            'entered_by' => $this->coordinator->id,
        ]);

        $this->assertDatabaseHas('participant_feedback', ['id' => $feedback->id]);

        $response = $this->actingAs($this->coordinator)->delete("/batches/{$this->batch->id}/feedback-forms/{$feedback->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('participant_feedback', ['id' => $feedback->id]);
    }
}
