<?php

namespace Tests\Feature;

use App\Models\BatchParticipant;
use App\Models\Participant;
use App\Models\ParticipantAssessment;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParticipantAssessmentModuleTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $coordinator;
    protected User $trainer;
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

        // 2. Coordinator
        $this->coordinator = User::firstOrCreate(
            ['email' => 'dc_assessment_test@osdp.gov.in'],
            [
                'name' => 'DC Assessment Tester',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $roleDc = Role::findOrCreate('district-coordinator');
        $roleDc->givePermissionTo(['assessments.view', 'assessments.manage', 'batches.view']);
        if (!$this->coordinator->hasRole('district-coordinator')) {
            $this->coordinator->assignRole('district-coordinator');
        }

        // 3. Unauthorized User
        $this->unauthorizedUser = User::firstOrCreate(
            ['email' => 'noperm_assessment@osdp.gov.in'],
            [
                'name' => 'No Perm User',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $emptyRole = Role::findOrCreate('empty-role-test');
        $emptyRole->syncPermissions([]);
        $this->unauthorizedUser->syncRoles(['empty-role-test']);

        // 4. Batch & Participant setup
        $existing = TrainingBatch::first();
        $this->batch = TrainingBatch::create([
            'batch_code' => 'ASS-TEST-' . uniqid(),
            'training_plan_id' => $existing->training_plan_id,
            'training_tier_id' => $existing?->training_tier_id ?? 1,
            'district_id' => $existing?->district_id ?? 1,
            'status' => 'scheduled',
            'scheduled_date' => now()->toDateString(),
            'planned_participants' => 30,
        ]);

        $this->participant = Participant::create([
            'name' => 'Assessment Trainee Test',
            'phone' => '98765' . rand(10000, 99999),
            'designation' => 'Panchayat Executive Officer',
            'office_name' => 'Test GP Office',
            'district_id' => $this->batch->district_id,
        ]);

        $this->batchParticipant = BatchParticipant::create([
            'training_batch_id' => $this->batch->id,
            'participant_id' => $this->participant->id,
            'serial_number' => 1,
        ]);
    }

    /**
     * Test Assessment Entry navigation sidebar link is visible for authorized users.
     */
    public function test_assessment_entry_sidebar_item_visible_for_authorized_users(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Assessment Entry');
        $response->assertSee(route('assessments.index'));
    }

    /**
     * Test unauthorized user cannot access Assessment Entry module.
     */
    public function test_unauthorized_user_cannot_access_assessment_entry(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)->get(route('assessments.index'));
        $response->assertForbidden();

        $response2 = $this->actingAs($this->unauthorizedUser)->get(route('assessments.batch', $this->batch));
        $response2->assertForbidden();
    }

    /**
     * Test authorized user can view assessments directory and batch entry roster.
     */
    public function test_authorized_user_can_view_assessment_index_and_batch_roster(): void
    {
        $indexResponse = $this->actingAs($this->superAdmin)->get(route('assessments.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee($this->batch->batch_code);

        $batchResponse = $this->actingAs($this->superAdmin)->get(route('assessments.batch', $this->batch));
        $batchResponse->assertOk();
        $batchResponse->assertSee($this->batch->batch_code);
        $batchResponse->assertSee('Assessment Trainee Test');
        $batchResponse->assertSee('Panchayat Executive Officer');
        $batchResponse->assertSee('Part A: Pre-Assessment');
        $batchResponse->assertSee('Part B: Post-Assessment');
    }

    /**
     * Test transcribing Part A (Pre-Assessment) answers and verifying score calculation.
     */
    public function test_transcribe_pre_assessment_answers_and_calculate_score(): void
    {
        // 4 'Yes' and 2 'No'
        $payload = [
            'pre_q1' => true,
            'pre_q2' => true,
            'pre_q3' => false,
            'pre_q4' => true,
            'pre_q5' => false,
            'pre_q6' => true,
        ];

        $response = $this->actingAs($this->superAdmin)->postJson(
            route('assessments.store', [$this->batch, $this->batchParticipant]),
            $payload
        );

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'assessment' => [
                'pre_score' => 4,
                'pre_completed' => true,
            ],
        ]);

        $this->assertDatabaseHas('participant_assessments', [
            'training_batch_id' => $this->batch->id,
            'batch_participant_id' => $this->batchParticipant->id,
            'pre_q1' => true,
            'pre_q2' => true,
            'pre_q3' => false,
            'pre_score' => 4,
            'pre_completed' => true,
        ]);
    }

    /**
     * Test transcribing Part B (Post-Assessment) answers and verifying correct answers score calculation.
     */
    public function test_transcribe_post_assessment_answers_and_calculate_score(): void
    {
        // Correct answers: Q1: B, Q2: C, Q3: B, Q4: C, Q5: A, Q6: C
        // We will provide: Q1: B, Q2: C, Q3: B, Q4: C, Q5: B (incorrect), Q6: C => Expected score 5/6
        $payload = [
            'post_q1' => 'B',
            'post_q2' => 'C',
            'post_q3' => 'B',
            'post_q4' => 'C',
            'post_q5' => 'B', // incorrect (correct is A: 30 days)
            'post_q6' => 'C',
        ];

        $response = $this->actingAs($this->superAdmin)->postJson(
            route('assessments.store', [$this->batch, $this->batchParticipant]),
            $payload
        );

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'assessment' => [
                'post_score' => 5,
                'post_completed' => true,
            ],
        ]);

        $this->assertDatabaseHas('participant_assessments', [
            'training_batch_id' => $this->batch->id,
            'batch_participant_id' => $this->batchParticipant->id,
            'post_q1' => 'B',
            'post_q5' => 'B',
            'post_score' => 5,
            'post_completed' => true,
        ]);
    }

    /**
     * Test transcribing both Pre and Post Assessment answers together.
     */
    public function test_transcribe_both_pre_and_post_together(): void
    {
        $payload = [
            'pre_q1' => true,
            'pre_q2' => true,
            'pre_q3' => true,
            'pre_q4' => false,
            'pre_q5' => false,
            'pre_q6' => false, // Pre score: 3/6

            'post_q1' => 'B',
            'post_q2' => 'C',
            'post_q3' => 'B',
            'post_q4' => 'C',
            'post_q5' => 'A',
            'post_q6' => 'C', // Post score: 6/6 (all correct)
        ];

        $response = $this->actingAs($this->superAdmin)->postJson(
            route('assessments.store', [$this->batch, $this->batchParticipant]),
            $payload
        );

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'assessment' => [
                'pre_score' => 3,
                'pre_completed' => true,
                'post_score' => 6,
                'post_completed' => true,
            ],
        ]);
    }

    /**
     * Test deleting an entered participant assessment.
     */
    public function test_delete_participant_assessment(): void
    {
        $assessment = ParticipantAssessment::create([
            'training_batch_id' => $this->batch->id,
            'batch_participant_id' => $this->batchParticipant->id,
            'participant_id' => $this->participant->id,
            'pre_score' => 5,
            'pre_completed' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->deleteJson(
            route('assessments.destroy', [$this->batch, $assessment])
        );

        $response->assertOk();
        $this->assertDatabaseMissing('participant_assessments', [
            'id' => $assessment->id,
        ]);
    }
}
