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
        $this->assertEquals('9876543210', $participant->phone_masked);
        $this->assertEquals('9876543210', $participant->phone);
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
        $this->assertEquals('9861099887', $participant->phone_masked);
        $this->assertEquals('9861099887', $participant->phone);
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

    /**
     * Test user can add a single participant to the general directory.
     */
    public function test_user_can_add_single_participant_to_directory(): void
    {
        $batch = TrainingBatch::first();
        $uid = uniqid();
        $name = "Individual Trainee {$uid}";
        $email = "trainee_{$uid}@odisha.gov.in";

        $response = $this->actingAs($this->superAdmin)->post('/participants', [
            'name' => $name,
            'designation' => 'Executive Officer',
            'office_name' => 'Baranga GP Office',
            'department' => 'PR & DW',
            'phone' => '9861234567',
            'email' => $email,
            'batch_id' => $batch->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $participant = Participant::where('name', $name)->first();
        $this->assertNotNull($participant);
        $this->assertEquals('Executive Officer', $participant->designation);
        $this->assertEquals('Baranga GP Office', $participant->office_name);
        $this->assertEquals('PR & DW', $participant->department);
        $this->assertEquals('9861234567', $participant->phone_masked);
        $this->assertEquals('9861234567', $participant->phone);
        $this->assertEquals($email, $participant->email);
        $this->assertTrue((bool) $participant->is_active);
        $this->assertEquals($this->superAdmin->id, $participant->created_by);
    }

    /**
     * Test user can add a single participant and enroll directly into a batch.
     */
    public function test_user_can_add_single_participant_and_enroll_in_batch(): void
    {
        $batch = TrainingBatch::first();
        $initialRegistered = $batch->registered_participants;

        $uid = uniqid();
        $name = "Enrolled Trainee {$uid}";

        $response = $this->actingAs($this->superAdmin)->post('/participants', [
            'name' => $name,
            'designation' => 'Gram Panchayat Officer',
            'office_name' => 'Cuttack Sadar GP',
            'department' => 'Rural Dev',
            'phone' => '9437123456',
            'email' => "enrolled_{$uid}@odisha.gov.in",
            'batch_id' => $batch->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $participant = Participant::where('name', $name)->first();
        $this->assertNotNull($participant);
        $this->assertEquals('9437123456', $participant->phone_masked);
        $this->assertEquals('9437123456', $participant->phone);

        $bp = BatchParticipant::where('training_batch_id', $batch->id)
            ->where('participant_id', $participant->id)
            ->first();
        $this->assertNotNull($bp);
        $this->assertGreaterThan(0, $bp->serial_number);

        // Verify attendance record initialized unmarked
        $att = AttendanceRecord::where('training_batch_id', $batch->id)
            ->where('batch_participant_id', $bp->id)
            ->first();
        $this->assertNotNull($att);
        $this->assertFalse((bool) $att->morning_present);
        $this->assertFalse((bool) $att->afternoon_present);

        // Verify batch counts updated
        $batch->refresh();
        $this->assertEquals($initialRegistered + 1, $batch->registered_participants);
    }

    /**
     * Test single participant validation requires name, designation, and office.
     */
    public function test_single_participant_validation_rules(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/participants', [
            'name' => '',
            'designation' => '',
            'office_name' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'designation', 'office_name', 'batch_id']);
    }

    /**
     * Test participant contact number validation: must be 10 digits starting with 6, 7, 8, or 9.
     */
    public function test_participant_phone_validation_rules(): void
    {
        $batch = TrainingBatch::first();

        // 1. Valid numbers starting with 6, 7, 8, 9 pass
        $validPhones = ['9856565677', '8765432109', '7012345678', '6234567890'];
        foreach ($validPhones as $phone) {
            $response = $this->actingAs($this->superAdmin)->post('/participants', [
                'name' => 'Valid Phone ' . uniqid(),
                'designation' => 'Staff',
                'office_name' => 'GP Office',
                'phone' => $phone,
                'batch_id' => $batch->id,
            ]);
            $response->assertSessionHasNoErrors();
        }

        // 2. Invalid numbers fail with phone error:
        // - Starting with 1, 2, 3, 4, 5
        // - Fewer than 10 digits
        // - More than 10 digits
        // - Letters / symbols
        $invalidPhones = ['5987654321', '1234567890', '98565', '98565656771', '98565abcde'];
        foreach ($invalidPhones as $badPhone) {
            $response = $this->actingAs($this->superAdmin)->post('/participants', [
                'name' => 'Invalid Phone ' . uniqid(),
                'designation' => 'Staff',
                'office_name' => 'GP Office',
                'phone' => $badPhone,
                'batch_id' => $batch->id,
            ]);
            $response->assertSessionHasErrors('phone');
        }
    }

    /**
     * Test guest cannot store participant.
     */
    public function test_guest_cannot_store_participant(): void
    {
        $response = $this->post('/participants', [
            'name' => 'Guest Participant',
            'designation' => 'Staff',
            'office_name' => 'GP Office',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test OCAC does not see Trash / Deleted tab and cannot view trashed participants.
     */
    public function test_ocac_cannot_see_trash_tab_or_view_trashed_participants(): void
    {
        $ocac = User::firstOrCreate(
            ['email' => 'ocac@osdp.gov.in'],
            [
                'name' => 'OCAC Nodal Officer',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $ocacRole = Role::findOrCreate('ocac');
        $ocacRole->givePermissionTo('participants.view');
        if (!$ocac->hasRole('ocac')) {
            $ocac->assignRole('ocac');
        }

        // Create a trashed participant
        $trashedParticipant = Participant::create([
            'name' => 'Deleted Trainee XYZ',
            'designation' => 'Panchayat Officer',
            'office_name' => 'Block Office',
            'is_active' => false,
            'created_by' => $this->superAdmin->id,
        ]);
        $trashedParticipant->delete();

        // 1. OCAC viewing participants index should NOT see Trash / Deleted tab
        $response = $this->actingAs($ocac)->get('/participants');
        $response->assertOk();
        $response->assertSee('Active Participants');
        $response->assertDontSee('Trash / Deleted');

        // 2. OCAC querying ?status=trashed should be forced to active and NOT see deleted participant
        $trashedQueryResponse = $this->actingAs($ocac)->get('/participants?status=trashed');
        $trashedQueryResponse->assertOk();
        $trashedQueryResponse->assertDontSee('Deleted Trainee XYZ');
        $trashedQueryResponse->assertDontSee('Trash / Deleted');

        // 3. Super Admin viewing participants index DOES see Trash / Deleted tab
        $adminResponse = $this->actingAs($this->superAdmin)->get('/participants');
        $adminResponse->assertOk();
        $adminResponse->assertSee('Trash / Deleted');
    }

    /**
     * Test participant phone number is stored unmasked (e.g. 9856565677) without asterisks.
     */
    public function test_participant_phone_is_stored_unmasked_without_asterisks(): void
    {
        $batch = TrainingBatch::first();
        $uid = uniqid();
        $response = $this->actingAs($this->superAdmin)->post('/participants', [
            'name' => "Unmasked Trainee {$uid}",
            'designation' => 'Panchayat Officer',
            'office_name' => 'Khurda GP',
            'phone' => '9856565677',
            'email' => "unmasked_{$uid}@test.com",
            'batch_id' => $batch->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $participant = Participant::where('name', "Unmasked Trainee {$uid}")->first();
        $this->assertNotNull($participant);
        $this->assertEquals('9856565677', $participant->phone_masked);
        $this->assertEquals('9856565677', $participant->phone);
    }

    /**
     * Test participant list on batch attendance sheet is ordered by name ascending.
     */
    public function test_batch_attendance_participant_list_is_ordered_by_name_ascending(): void
    {
        $batch = TrainingBatch::first();
        // Clear existing batch participants for this test
        $batch->batchParticipants()->delete();

        $names = ['shayam', 'don', 'Manoj Kumar Dash', 'king'];
        foreach ($names as $idx => $name) {
            $p = Participant::create([
                'name' => $name,
                'designation' => 'Staff',
                'office_name' => 'GP Office',
                'is_active' => true,
                'created_by' => $this->superAdmin->id,
            ]);
            BatchParticipant::create([
                'training_batch_id' => $batch->id,
                'participant_id' => $p->id,
                'serial_number' => $idx + 1,
            ]);
        }

        $response = $this->actingAs($this->superAdmin)->get("/batches/{$batch->id}/attendance");
        $response->assertOk();

        // Check that names appear in case-insensitive alphabetical order: don, king, Manoj Kumar Dash, shayam
        $content = $response->getContent();
        $posDon = strpos($content, '<div>don</div>');
        $posKing = strpos($content, '<div>king</div>');
        $posManoj = strpos($content, '<div>Manoj Kumar Dash</div>');
        $posShayam = strpos($content, '<div>shayam</div>');

        $this->assertNotFalse($posDon, "'<div>don</div>' must exist in response");
        $this->assertNotFalse($posKing, "'<div>king</div>' must exist in response");
        $this->assertNotFalse($posManoj, "'<div>Manoj Kumar Dash</div>' must exist in response");
        $this->assertNotFalse($posShayam, "'<div>shayam</div>' must exist in response");

        $this->assertTrue($posDon < $posKing, "'don' must appear before 'king'");
        $this->assertTrue($posKing < $posManoj, "'king' must appear before 'Manoj Kumar Dash'");
        $this->assertTrue($posManoj < $posShayam, "'Manoj Kumar Dash' must appear before 'shayam'");
    }

    /**
     * Test editing participant can assign or change their training batch.
     */
    public function test_edit_participant_can_change_or_assign_batch(): void
    {
        $batch1 = TrainingBatch::first();
        $batch2 = TrainingBatch::skip(1)->first();
        $this->assertNotNull($batch1);
        $this->assertNotNull($batch2);

        $uid = uniqid();
        $participant = Participant::create([
            'name' => "Batch Move Trainee {$uid}",
            'designation' => 'PEO',
            'office_name' => 'Block Office',
            'phone_masked' => '9856565677',
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        // 1. Assign participant to Batch 1 via edit
        $response1 = $this->actingAs($this->superAdmin)->put("/participants/{$participant->id}", [
            'name' => $participant->name,
            'designation' => $participant->designation,
            'office_name' => $participant->office_name,
            'phone' => '9856565677',
            'batch_id' => $batch1->id,
            'is_active' => 1,
        ]);
        $response1->assertRedirect();

        $bp1 = BatchParticipant::where('participant_id', $participant->id)->first();
        $this->assertNotNull($bp1);
        $this->assertEquals($batch1->id, $bp1->training_batch_id);

        // 2. Change participant to Batch 2 via edit
        $response2 = $this->actingAs($this->superAdmin)->put("/participants/{$participant->id}", [
            'name' => $participant->name,
            'designation' => $participant->designation,
            'office_name' => $participant->office_name,
            'phone' => '9856565677',
            'batch_id' => $batch2->id,
            'is_active' => 1,
        ]);
        $response2->assertRedirect();

        $bp2 = BatchParticipant::where('participant_id', $participant->id)->first();
        $this->assertNotNull($bp2);
        $this->assertEquals($batch2->id, $bp2->training_batch_id);
        $this->assertEquals(0, BatchParticipant::where('participant_id', $participant->id)->where('training_batch_id', $batch1->id)->count());

        // 3. Unassign batch (move to general roster) via edit
        $response3 = $this->actingAs($this->superAdmin)->put("/participants/{$participant->id}", [
            'name' => $participant->name,
            'designation' => $participant->designation,
            'office_name' => $participant->office_name,
            'phone' => '9856565677',
            'batch_id' => '',
            'is_active' => 1,
        ]);
        $response3->assertRedirect();

        $this->assertEquals(0, BatchParticipant::where('participant_id', $participant->id)->count());
    }

    /**
     * Test Participants Directory shows batches first before a batch is selected.
     */
    public function test_participants_directory_shows_batches_first_before_batch_is_selected(): void
    {
        $batch = TrainingBatch::first();
        $this->assertNotNull($batch);

        $response = $this->actingAs($this->superAdmin)->get('/participants');
        $response->assertOk();
        $response->assertViewHas('showBatchesFirst', true);
        $response->assertSee('Step 1: Choose a Training Batch');
        $response->assertSee('Select Batch & View Trainees', false);
        $response->assertSee($batch->batch_code);
    }

    /**
     * Test selecting a batch shows participants roster for that specific batch.
     */
    public function test_selecting_batch_shows_participants_for_that_batch(): void
    {
        $batch = TrainingBatch::first();
        $this->assertNotNull($batch);

        $uid = uniqid();
        $participant = Participant::create([
            'name' => "Batch Specific Trainee {$uid}",
            'designation' => 'Panchayat Officer',
            'office_name' => 'Khurda GP Office',
            'phone_masked' => '9856565677',
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        BatchParticipant::create([
            'training_batch_id' => $batch->id,
            'participant_id' => $participant->id,
            'serial_number' => 1,
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/participants?batch_id={$batch->id}");
        $response->assertOk();
        $response->assertViewHas('showBatchesFirst', false);
        $response->assertSee('Active Batch Roster');
        $response->assertSee('Switch Batch');
        $response->assertSee("Batch Specific Trainee {$uid}");
    }

    /**
     * Test viewing all participants via all_participants parameter shows master roster.
     */
    public function test_view_all_participants_shows_master_roster(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/participants?all_participants=1');
        $response->assertOk();
        $response->assertViewHas('showBatchesFirst', false);
        $response->assertSee('All Participants Directory (General Master Roster)');
    }

    /**
     * Test soft-deleted participants are not included in the enrolled trainees count.
     */
    public function test_deleted_participants_are_not_included_in_enrolled_trainees_count(): void
    {
        $batch = TrainingBatch::first();
        $this->assertNotNull($batch);

        // Clear existing participants in this batch for deterministic count test
        $batch->batchParticipants()->delete();

        // Create 2 active participants
        $p1 = Participant::create([
            'name' => 'Active Trainee 1',
            'designation' => 'Staff',
            'office_name' => 'GP Office',
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);
        $p2 = Participant::create([
            'name' => 'Active Trainee 2',
            'designation' => 'Staff',
            'office_name' => 'GP Office',
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        // Create 1 deleted participant
        $p3 = Participant::create([
            'name' => 'Deleted Trainee 3',
            'designation' => 'Staff',
            'office_name' => 'GP Office',
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);

        BatchParticipant::create(['training_batch_id' => $batch->id, 'participant_id' => $p1->id, 'serial_number' => 1]);
        BatchParticipant::create(['training_batch_id' => $batch->id, 'participant_id' => $p2->id, 'serial_number' => 2]);
        BatchParticipant::create(['training_batch_id' => $batch->id, 'participant_id' => $p3->id, 'serial_number' => 3]);

        // Soft-delete participant 3
        $p3->delete();

        $response = $this->actingAs($this->superAdmin)->get('/participants');
        $response->assertOk();

        // Batches view must report 2 active participants, NOT 3
        $batches = $response->viewData('batches');
        $testedBatch = $batches->firstWhere('id', $batch->id);
        $this->assertNotNull($testedBatch);
        $this->assertEquals(2, $testedBatch->batch_participants_count);
    }

    /**
     * Test target training batch selection is strictly mandatory for both single add and bulk import.
     */
    public function test_batch_id_is_strictly_mandatory_for_single_add_and_bulk_upload(): void
    {
        // 1. Single participant without batch_id
        $singleResponse = $this->actingAs($this->superAdmin)->post('/participants', [
            'name' => 'Batchless Trainee',
            'designation' => 'Staff',
            'office_name' => 'GP Office',
            'phone' => '9876543210',
        ]);
        $singleResponse->assertSessionHasErrors(['batch_id' => 'Target training batch selection is mandatory.']);

        // 2. Bulk upload without batch_id
        $file = \Illuminate\Http\UploadedFile::fake()->create('participants.csv', 100, 'text/csv');
        $bulkResponse = $this->actingAs($this->superAdmin)->post('/participants/import', [
            'file' => $file,
        ]);
        $bulkResponse->assertSessionHasErrors(['batch_id' => 'Target training batch selection is mandatory.']);
    }

    /**
     * Test super admin cannot see the Add Participant or Bulk Upload options on participants index.
     */
    public function test_super_admin_does_not_see_add_participant_or_bulk_upload_options(): void
    {
        // Super admin should not see the add or bulk upload options
        $adminResponse = $this->actingAs($this->superAdmin)->get('/participants');
        $adminResponse->assertOk();
        $adminResponse->assertDontSee('Add Participant');
        $adminResponse->assertDontSee('Bulk Upload (Excel)');
        $adminResponse->assertDontSee('Add Trainee');

        // District coordinator or other authorized managers should see them
        $batch = TrainingBatch::first();
        $this->creatorUser->assignedDistricts()->sync([$batch->district_id]);

        $coordinatorResponse = $this->actingAs($this->creatorUser)->get('/participants');
        $coordinatorResponse->assertOk();
        $coordinatorResponse->assertSee('Add Participant');
        $coordinatorResponse->assertSee('Bulk Upload (Excel)');
        $coordinatorResponse->assertSee('Add Trainee');

        // Check Participant Attendance Register (/batches/{id}/attendance)
        $adminAttendanceResp = $this->actingAs($this->superAdmin)->get("/batches/{$batch->id}/attendance");
        $adminAttendanceResp->assertOk();
        $adminAttendanceResp->assertDontSee('Enroll Participants into Batch');
        $adminAttendanceResp->assertDontSee('Bulk Upload (Excel)');
        $adminAttendanceResp->assertDontSee('Single Entry');
        $adminAttendanceResp->assertDontSee('Import & Enroll');
        $adminAttendanceResp->assertDontSee('Add & Enroll');

        $coordAttendanceResp = $this->actingAs($this->creatorUser)->get("/batches/{$batch->id}/attendance");
        $coordAttendanceResp->assertOk();
        $coordAttendanceResp->assertSee('Enroll Participants into Batch');
        $coordAttendanceResp->assertSee('Bulk Upload (Excel)');
        $coordAttendanceResp->assertSee('Single Entry');
        $coordAttendanceResp->assertSee('Import & Enroll', false);
        $coordAttendanceResp->assertSee('Add & Enroll', false);
    }
}
