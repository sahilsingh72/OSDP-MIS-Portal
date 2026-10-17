<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BatchParticipant;
use App\Models\District;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParticipantManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $creatorUser;
    protected User $otherUser;

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

        // 2. Creator User with participants.manage permission
        $this->creatorUser = User::firstOrCreate(
            ['email' => 'creator_test@osdp.gov.in'],
            [
                'name' => 'Creator Coordinator',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $role = Role::findOrCreate('district-coordinator');
        $role->givePermissionTo(['participants.view', 'participants.manage']);
        if (!$this->creatorUser->hasRole('district-coordinator')) {
            $this->creatorUser->assignRole('district-coordinator');
        }

        // 3. Other User with participants.manage permission
        $this->otherUser = User::firstOrCreate(
            ['email' => 'other_test@osdp.gov.in'],
            [
                'name' => 'Other Coordinator',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        if (!$this->otherUser->hasRole('district-coordinator')) {
            $this->otherUser->assignRole('district-coordinator');
        }
    }

    /**
     * Test creator can update participant they created.
     */
    public function test_creator_can_update_their_participant(): void
    {
        $participant = Participant::create([
            'name' => 'Original Name',
            'designation' => 'Original Designation',
            'office_name' => 'Original GP',
            'department' => 'P&RD',
            'phone_masked' => '98******01',
            'email' => 'orig@test.com',
            'created_by' => $this->creatorUser->id,
        ]);

        $response = $this->actingAs($this->creatorUser)->put("/participants/{$participant->id}", [
            'name' => 'Updated Name By Creator',
            'designation' => 'Senior Officer',
            'office_name' => 'Updated Office',
            'department' => 'P&RD',
            'phone' => '9876543210',
            'email' => 'updated@test.com',
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $participant->refresh();

        $this->assertEquals('Updated Name By Creator', $participant->name);
        $this->assertEquals('Senior Officer', $participant->designation);
        $this->assertEquals('98******10', $participant->phone_masked);
    }

    /**
     * Test another user cannot update someone else's participant.
     */
    public function test_other_user_cannot_update_someone_elses_participant(): void
    {
        $participant = Participant::create([
            'name' => 'Protected Participant',
            'designation' => 'Executive',
            'office_name' => 'HQ Office',
            'created_by' => $this->creatorUser->id,
        ]);

        $response = $this->actingAs($this->otherUser)->put("/participants/{$participant->id}", [
            'name' => 'Hacked Name',
            'designation' => 'Hacked',
            'office_name' => 'Hacked',
        ]);

        $response->assertStatus(403);
        $participant->refresh();
        $this->assertEquals('Protected Participant', $participant->name);
    }

    /**
     * Test super admin can update ANY participant.
     */
    public function test_super_admin_can_update_any_participant(): void
    {
        $participant = Participant::create([
            'name' => 'Created By Someone Else',
            'designation' => 'Field Staff',
            'office_name' => 'Remote GP',
            'created_by' => $this->creatorUser->id,
        ]);

        $response = $this->actingAs($this->superAdmin)->put("/participants/{$participant->id}", [
            'name' => 'Overridden By Super Admin',
            'designation' => 'Promoted Staff',
            'office_name' => 'Central Office',
        ]);

        $response->assertRedirect();
        $participant->refresh();
        $this->assertEquals('Overridden By Super Admin', $participant->name);
    }

    /**
     * Test creator can soft-delete and restore their participant.
     */
    public function test_creator_can_soft_delete_and_restore_their_participant(): void
    {
        $participant = Participant::create([
            'name' => 'Deletable Participant',
            'designation' => 'Operator',
            'office_name' => 'Block Office',
            'created_by' => $this->creatorUser->id,
        ]);

        $this->assertFalse($participant->trashed());

        // 1. Soft delete
        $delResponse = $this->actingAs($this->creatorUser)->delete("/participants/{$participant->id}");
        $delResponse->assertRedirect();

        $participant->refresh();
        $this->assertTrue($participant->trashed());
        $this->assertNotNull($participant->deleted_at);

        // Active scope does not include it
        $this->assertNull(Participant::find($participant->id));
        // withTrashed or onlyTrashed includes it
        $this->assertNotNull(Participant::onlyTrashed()->find($participant->id));

        // 2. Restore
        $restoreResponse = $this->actingAs($this->creatorUser)->post("/participants/{$participant->id}/restore");
        $restoreResponse->assertRedirect();

        $participant->refresh();
        $this->assertFalse($participant->trashed());
        $this->assertNull($participant->deleted_at);
        $this->assertNotNull(Participant::find($participant->id));
    }

    /**
     * Test other user cannot delete or restore someone else's participant.
     */
    public function test_other_user_cannot_delete_or_restore_someone_elses_participant(): void
    {
        $participant = Participant::create([
            'name' => 'Creator Owns This',
            'designation' => 'Specialist',
            'office_name' => 'District Office',
            'created_by' => $this->creatorUser->id,
        ]);

        // Attempt delete
        $response = $this->actingAs($this->otherUser)->delete("/participants/{$participant->id}");
        $response->assertStatus(403);
        $this->assertFalse($participant->fresh()->trashed());

        // Soft delete directly by creator
        $participant->delete();
        $this->assertTrue($participant->fresh()->trashed());

        // Other user attempts restore
        $restoreResponse = $this->actingAs($this->otherUser)->post("/participants/{$participant->id}/restore");
        $restoreResponse->assertStatus(403);
        $this->assertTrue($participant->fresh()->trashed());
    }

    /**
     * Test super admin can soft-delete and restore ANY participant.
     */
    public function test_super_admin_can_soft_delete_and_restore_any_participant(): void
    {
        $participant = Participant::create([
            'name' => 'Universal Target',
            'designation' => 'Staff',
            'office_name' => 'Branch Office',
            'created_by' => $this->creatorUser->id,
        ]);

        // Super admin deletes
        $delResponse = $this->actingAs($this->superAdmin)->delete("/participants/{$participant->id}");
        $delResponse->assertRedirect();
        $this->assertTrue($participant->fresh()->trashed());

        // Super admin restores
        $restoreResponse = $this->actingAs($this->superAdmin)->post("/participants/{$participant->id}/restore");
        $restoreResponse->assertRedirect();
        $this->assertFalse($participant->fresh()->trashed());
    }

    /**
     * Test historical batch participant and attendance relations are not broken by soft delete.
     */
    public function test_soft_deleting_participant_preserves_batch_attendance_history(): void
    {
        $batch = TrainingBatch::first();

        $participant = Participant::create([
            'name' => 'Historical Attendee',
            'designation' => 'Gram Rozgar Sevak',
            'office_name' => 'Panchayat Office',
            'created_by' => $this->creatorUser->id,
        ]);

        $bp = BatchParticipant::create([
            'training_batch_id' => $batch->id,
            'participant_id' => $participant->id,
            'serial_number' => 99,
        ]);

        AttendanceRecord::create([
            'training_batch_id' => $batch->id,
            'batch_participant_id' => $bp->id,
            'morning_present' => true,
            'afternoon_present' => true,
            'marked_by' => $this->creatorUser->id,
        ]);

        // Soft delete the participant
        $participant->delete();
        $this->assertTrue($participant->fresh()->trashed());

        // BatchParticipant relation with withTrashed should still resolve participant
        $bp->refresh();
        $this->assertNotNull($bp->participant);
        $this->assertEquals('Historical Attendee', $bp->participant->name);
        $this->assertTrue($bp->participant->trashed());

        // Attendance screen renders without throwing ModelNotFoundException and does NOT show soft-deleted participant
        $response = $this->actingAs($this->superAdmin)->get("/batches/{$batch->id}/attendance");
        $response->assertStatus(200);
        $response->assertDontSee('Historical Attendee');

        // When restored, participant reappears in attendance
        $participant->restore();
        $batch->recalculateCounts();
        $restoreResponse = $this->actingAs($this->superAdmin)->get("/batches/{$batch->id}/attendance");
        $restoreResponse->assertStatus(200);
        $restoreResponse->assertSee('Historical Attendee');
    }

    /**
     * Test single entry form can register participant with email and enroll into batch.
     */
    public function test_single_entry_adds_participant_with_email_and_enrolls_in_batch(): void
    {
        $batch = TrainingBatch::first();
        $uid = uniqid();
        $name = "Single Entry Trainee {$uid}";
        $email = "single_{$uid}@odisha.gov.in";

        $response = $this->actingAs($this->superAdmin)->post("/batches/{$batch->id}/participants", [
            'name' => $name,
            'designation' => 'Panchayat Officer',
            'office_name' => 'Khurda Sadar GP',
            'phone' => '9861099887',
            'email' => $email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Participant details added and enrolled into batch successfully. Please mark attendance manually on Form 2 below.');

        $participant = Participant::where('name', $name)->first();
        $this->assertNotNull($participant);
        $this->assertEquals('Panchayat Officer', $participant->designation);
        $this->assertEquals('Khurda Sadar GP', $participant->office_name);
        $this->assertEquals($email, $participant->email);
        $this->assertEquals('98******87', $participant->phone_masked);
        $this->assertEquals($this->superAdmin->id, $participant->created_by);

        // Verify enrolled in batch
        $bp = BatchParticipant::where('training_batch_id', $batch->id)
            ->where('participant_id', $participant->id)
            ->first();
        $this->assertNotNull($bp);

        // Verify attendance is strictly UNMARKED (not auto-marked as present)
        $att = $bp->attendanceRecord;
        $this->assertNotNull($att);
        $this->assertFalse((bool)$att->morning_present);
        $this->assertFalse((bool)$att->afternoon_present);
    }

    /**
     * Test district coordinator can edit and delete participant enrolled in a batch in their district.
     */
    public function test_district_coordinator_can_edit_and_delete_participant_in_assigned_batch(): void
    {
        $batch = TrainingBatch::first();
        $district = $batch->district;

        // Assign district to creatorUser
        $this->creatorUser->assignedDistricts()->sync([$district->id]);

        // Participant created by someone else (otherUser)
        $participant = Participant::create([
            'name' => 'Trainee in Khordha Batch',
            'designation' => 'PEO',
            'office_name' => 'Khordha GP',
            'created_by' => $this->otherUser->id,
        ]);

        // Enroll in batch
        BatchParticipant::create([
            'training_batch_id' => $batch->id,
            'participant_id' => $participant->id,
            'serial_number' => 101,
        ]);

        // District Coordinator can update participant because of batch assignment
        $updateResp = $this->actingAs($this->creatorUser)->put("/participants/{$participant->id}", [
            'name' => 'Updated By DC',
            'designation' => 'Executive Officer',
            'office_name' => 'Khordha GP',
        ]);
        $updateResp->assertRedirect();
        $this->assertEquals('Updated By DC', $participant->fresh()->name);

        // District Coordinator can soft-delete participant because of batch assignment
        $delResp = $this->actingAs($this->creatorUser)->delete("/participants/{$participant->id}");
        $delResp->assertRedirect();
        $this->assertTrue($participant->fresh()->trashed());

        // District Coordinator can restore participant
        $restoreResp = $this->actingAs($this->creatorUser)->post("/participants/{$participant->id}/restore");
        $restoreResp->assertRedirect();
        $this->assertFalse($participant->fresh()->trashed());
    }

    /**
     * Test field trainer can edit and delete participant enrolled in their assigned batch.
     */
    public function test_field_trainer_can_edit_and_delete_participant_in_assigned_batch(): void
    {
        $batch = TrainingBatch::first();

        // Create a trainer user
        $trainer = User::create([
            'name' => 'Master Trainer Test',
            'email' => 'trainer_batch_test@osdp.gov.in',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $role = Role::findOrCreate('field-trainer');
        $role->givePermissionTo(['participants.view', 'participants.manage']);
        $trainer->assignRole('field-trainer');

        // Assign trainer to this batch
        $batch->trainers()->sync([$trainer->id]);

        // Participant created by someone else
        $participant = Participant::create([
            'name' => 'Trainee Under Trainer',
            'designation' => 'GRS',
            'office_name' => 'Balianta GP',
            'created_by' => $this->superAdmin->id,
        ]);

        // Enroll in trainer's batch
        BatchParticipant::create([
            'training_batch_id' => $batch->id,
            'participant_id' => $participant->id,
            'serial_number' => 102,
        ]);

        // Trainer can update participant
        $updateResp = $this->actingAs($trainer)->put("/participants/{$participant->id}", [
            'name' => 'Updated By Trainer',
            'designation' => 'Senior GRS',
            'office_name' => 'Balianta GP',
        ]);
        $updateResp->assertRedirect();
        $this->assertEquals('Updated By Trainer', $participant->fresh()->name);

        // Trainer can delete participant
        $delResp = $this->actingAs($trainer)->delete("/participants/{$participant->id}");
        $delResp->assertRedirect();
        $this->assertTrue($participant->fresh()->trashed());

        // Trainer can restore participant
        $restoreResp = $this->actingAs($trainer)->post("/participants/{$participant->id}/restore");
        $restoreResp->assertRedirect();
        $this->assertFalse($participant->fresh()->trashed());
    }

    /**
     * Test field monitor can edit and delete participant enrolled in their verified batch.
     */
    public function test_field_monitor_can_edit_and_delete_participant_in_assigned_batch(): void
    {
        $batch = TrainingBatch::first();

        // Create a monitor user
        $monitor = User::create([
            'name' => 'Field Monitor Test',
            'email' => 'monitor_batch_test@osdp.gov.in',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $role = Role::findOrCreate('field-monitor');
        $role->givePermissionTo(['participants.view', 'participants.manage']);
        $monitor->assignRole('field-monitor');

        // Assign monitor to this batch's district
        $monitor->assignedDistricts()->sync([$batch->district_id]);

        // Participant created by someone else
        $participant = Participant::create([
            'name' => 'Trainee Inspected By Monitor',
            'designation' => 'Operator',
            'office_name' => 'Jatni GP',
            'created_by' => $this->superAdmin->id,
        ]);

        // Enroll in batch
        BatchParticipant::create([
            'training_batch_id' => $batch->id,
            'participant_id' => $participant->id,
            'serial_number' => 103,
        ]);

        // Monitor can update participant
        $updateResp = $this->actingAs($monitor)->put("/participants/{$participant->id}", [
            'name' => 'Updated By Monitor',
            'designation' => 'Verified Operator',
            'office_name' => 'Jatni GP',
        ]);
        $updateResp->assertRedirect();
        $this->assertEquals('Updated By Monitor', $participant->fresh()->name);

        // Monitor can delete participant
        $delResp = $this->actingAs($monitor)->delete("/participants/{$participant->id}");
        $delResp->assertRedirect();
        $this->assertTrue($participant->fresh()->trashed());

        // Monitor can restore participant
        $restoreResp = $this->actingAs($monitor)->post("/participants/{$participant->id}/restore");
        $restoreResp->assertRedirect();
        $this->assertFalse($participant->fresh()->trashed());
    }

    /**
     * Test unrelated user cannot edit or delete participant in another district's batch.
     */
    public function test_unrelated_user_cannot_edit_or_delete_participant_in_other_district_batch(): void
    {
        $batch = TrainingBatch::first();

        // Set otherUser to an unrelated district
        $otherDistrict = District::where('id', '!=', $batch->district_id)->first();
        $this->otherUser->assignedDistricts()->sync([$otherDistrict->id]);

        // Participant created by superAdmin and enrolled in $batch
        $participant = Participant::create([
            'name' => 'Unrelated Participant',
            'designation' => 'Staff',
            'office_name' => 'Remote GP',
            'created_by' => $this->superAdmin->id,
        ]);

        BatchParticipant::create([
            'training_batch_id' => $batch->id,
            'participant_id' => $participant->id,
            'serial_number' => 104,
        ]);

        // otherUser attempts update -> 403
        $updateResp = $this->actingAs($this->otherUser)->put("/participants/{$participant->id}", [
            'name' => 'Unauthorized Edit',
            'designation' => 'Staff',
            'office_name' => 'Remote GP',
        ]);
        $updateResp->assertStatus(403);
        $this->assertEquals('Unrelated Participant', $participant->fresh()->name);

        // otherUser attempts delete -> 403
        $delResp = $this->actingAs($this->otherUser)->delete("/participants/{$participant->id}");
        $delResp->assertStatus(403);
        $this->assertFalse($participant->fresh()->trashed());
    }
}
