<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\District;
use App\Models\FieldTrainer;
use App\Models\GramPanchayat;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FieldTrainerManagementTest extends TestCase
{
    /**
     * Test DLC can view trainers index page scoped to their district.
     */
    public function test_dlc_can_view_trainers_index_scoped_to_district(): void
    {
        $dlc = User::role('district-coordinator')->first();
        $this->assertNotNull($dlc, 'District coordinator user must exist');

        $response = $this->actingAs($dlc)->get('/trainers');

        $response->assertStatus(200);
        $response->assertSee('Field Trainers');
        $response->assertSee('Add Field Trainer');
    }

    /**
     * Test DLC can create a field trainer in their assigned district.
     * Verifies that trainer profile is saved in field_trainers table and user account is saved in users table.
     */
    public function test_dlc_can_create_field_trainer_in_assigned_district(): void
    {
        $dlc = User::role('district-coordinator')->first();
        $districtId = $dlc->assignedDistricts->first()->id;

        $block = Block::where('district_id', $districtId)->first();
        $this->assertNotNull($block, 'Block must exist for assigned district');

        $gp = GramPanchayat::where('block_id', $block->id)->first();
        $this->assertNotNull($gp, 'GP must exist for block');

        $email = 'ft_' . uniqid() . '@example.com';
        $phone = '98' . rand(10000000, 99999999);

        $response = $this->actingAs($dlc)->post('/trainers', [
            'name' => 'Test Field Trainer',
            'email' => $email,
            'phone' => $phone,
            'designation' => 'Lead Trainer',
            'department' => 'Skill Development',
            'district_id' => $districtId,
            'block_id' => $block->id,
            'gram_panchayat_id' => $gp->id,
            'password' => 'Trainer@12345',
            'password_confirmation' => 'Trainer@12345',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/trainers');
        $response->assertSessionHas('status');

        // 1. Verify User login account was created in users table
        $createdUser = User::where('email', $email)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('Test Field Trainer', $createdUser->name);
        $this->assertEquals($phone, $createdUser->phone);
        $this->assertTrue($createdUser->hasRole('field-trainer'));
        $this->assertTrue($createdUser->is_active);

        // 2. Verify dedicated Field Trainer record was created in field_trainers table
        $trainerProfile = FieldTrainer::where('user_id', $createdUser->id)->first();
        $this->assertNotNull($trainerProfile);
        $this->assertEquals('Test Field Trainer', $trainerProfile->name);
        $this->assertEquals($email, $trainerProfile->email);
        $this->assertEquals($phone, $trainerProfile->phone);
        $this->assertEquals('Lead Trainer', $trainerProfile->designation);
        $this->assertEquals('Skill Development', $trainerProfile->department);
        $this->assertEquals($districtId, $trainerProfile->district_id);
        $this->assertEquals($block->id, $trainerProfile->block_id);
        $this->assertEquals($gp->id, $trainerProfile->gram_panchayat_id);
        $this->assertTrue($trainerProfile->is_active);

        // 3. Verify User-FieldTrainer relation
        $this->assertEquals($trainerProfile->id, $createdUser->fieldTrainer->id);
        $this->assertEquals($createdUser->id, $trainerProfile->user->id);

        // 4. Logout DLC and verify trainer can log in with created credentials
        auth()->logout();

        $loginResponse = $this->post('/login', [
            'email' => $email,
            'password' => 'Trainer@12345',
        ]);
        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($createdUser);
    }

    /**
     * Test DLC cannot create field trainer in an unassigned district.
     */
    public function test_dlc_cannot_create_field_trainer_in_unassigned_district(): void
    {
        $dlc = User::role('district-coordinator')->first();
        $assignedIds = $dlc->assignedDistricts->pluck('id')->toArray();

        $unassignedDistrict = District::whereNotIn('id', $assignedIds)->first();
        $this->assertNotNull($unassignedDistrict, 'Unassigned district must exist');

        $block = Block::where('district_id', $unassignedDistrict->id)->first();
        $gp = GramPanchayat::where('block_id', $block->id)->first();

        $email = 'unauth_' . uniqid() . '@example.com';
        $phone = '97' . rand(10000000, 99999999);

        $response = $this->actingAs($dlc)->post('/trainers', [
            'name' => 'Unauthorized District Trainer',
            'email' => $email,
            'phone' => $phone,
            'designation' => 'Trainer',
            'department' => 'Education',
            'district_id' => $unassignedDistrict->id,
            'block_id' => $block->id,
            'gram_panchayat_id' => $gp->id,
        ]);

        $response->assertSessionHasErrors(['district_id']);
        $this->assertDatabaseMissing('users', ['email' => $email]);
        $this->assertDatabaseMissing('field_trainers', ['email' => $email]);
    }

    /**
     * Test validation rules ensure all required fields are present and valid.
     */
    public function test_field_trainer_creation_validation_requires_all_fields(): void
    {
        $pmu = User::role('pmu')->first();
        $this->assertNotNull($pmu, 'PMU user must exist');

        $response = $this->actingAs($pmu)->post('/trainers', []);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'phone',
            'designation',
            'department',
            'district_id',
            'block_id',
            'gram_panchayat_id',
        ]);
    }

    /**
     * Test hierarchy validation: block must belong to district, GP must belong to block.
     */
    public function test_hierarchy_validation_fails_for_mismatched_block_or_gp(): void
    {
        $pmu = User::role('pmu')->first();

        $districts = District::take(2)->get();
        $district1 = $districts[0];
        $district2 = $districts[1];

        $blockFromDistrict2 = Block::where('district_id', $district2->id)->first();
        $gpFromDistrict2 = GramPanchayat::where('block_id', $blockFromDistrict2->id)->first();

        $email = 'mismatch_' . uniqid() . '@example.com';

        // Submit district1 with block from district2
        $response = $this->actingAs($pmu)->post('/trainers', [
            'name' => 'Mismatch Trainer',
            'email' => $email,
            'phone' => '96' . rand(10000000, 99999999),
            'designation' => 'Trainer',
            'department' => 'Panchayati Raj',
            'district_id' => $district1->id,
            'block_id' => $blockFromDistrict2->id,
            'gram_panchayat_id' => $gpFromDistrict2->id,
        ]);

        $response->assertSessionHasErrors(['block_id']);
        $this->assertDatabaseMissing('users', ['email' => $email]);
        $this->assertDatabaseMissing('field_trainers', ['email' => $email]);
    }

    /**
     * Test PMU can create trainer in any district statewide.
     */
    public function test_pmu_can_create_field_trainer_statewide(): void
    {
        $pmu = User::role('pmu')->first();
        $district = District::first();
        $block = Block::where('district_id', $district->id)->first();
        $gp = GramPanchayat::where('block_id', $block->id)->first();

        $email = 'pmu_created_' . uniqid() . '@example.com';
        $phone = '95' . rand(10000000, 99999999);

        $response = $this->actingAs($pmu)->post('/trainers', [
            'name' => 'PMU State Trainer',
            'email' => $email,
            'phone' => $phone,
            'designation' => 'Master Trainer',
            'department' => 'Agriculture',
            'district_id' => $district->id,
            'block_id' => $block->id,
            'gram_panchayat_id' => $gp->id,
        ]);

        $response->assertRedirect('/trainers');
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'phone' => $phone,
        ]);
        $this->assertDatabaseHas('field_trainers', [
            'email' => $email,
            'phone' => $phone,
            'department' => 'Agriculture',
            'district_id' => $district->id,
        ]);
    }

    /**
     * Test unauthorized users cannot access trainer management.
     */
    public function test_unauthorized_user_cannot_access_or_create_trainer(): void
    {
        $trainer = User::role('field-trainer')->first();
        if ($trainer) {
            $response = $this->actingAs($trainer)->get('/trainers/create');
            $response->assertStatus(403);
        }

        auth()->logout();

        $guestResponse = $this->get('/trainers');
        $guestResponse->assertRedirect('/login');
    }

    /**
     * Test authorized user can edit field trainer and toggle active status.
     * Verifies that changes sync across both field_trainers table and users table.
     */
    public function test_dlc_can_update_trainer_and_toggle_status(): void
    {
        $dlc = User::role('district-coordinator')->first();
        $districtId = $dlc->assignedDistricts->first()->id;
        $block = Block::where('district_id', $districtId)->first();
        $gp = GramPanchayat::where('block_id', $block->id)->first();

        $email = 'edit_' . uniqid() . '@example.com';
        $phone = '94' . rand(10000000, 99999999);

        // Create User account
        $userAccount = User::create([
            'name' => 'Original Name',
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make('Trainer@12345'),
            'is_active' => true,
        ]);
        $userAccount->assignRole('field-trainer');
        $userAccount->assignedDistricts()->sync([$districtId]);

        // Create FieldTrainer profile
        $trainer = FieldTrainer::create([
            'user_id' => $userAccount->id,
            'name' => 'Original Name',
            'email' => $email,
            'phone' => $phone,
            'designation' => 'Trainer Level 1',
            'department' => 'IT',
            'district_id' => $districtId,
            'block_id' => $block->id,
            'gram_panchayat_id' => $gp->id,
            'is_active' => true,
        ]);

        // Test Edit View
        $editView = $this->actingAs($dlc)->get("/trainers/{$trainer->id}/edit");
        $editView->assertStatus(200);
        $editView->assertSee('Original Name');

        $updatedPhone = '93' . rand(10000000, 99999999);

        // Test Update
        $updateResponse = $this->actingAs($dlc)->put("/trainers/{$trainer->id}", [
            'name' => 'Updated Name',
            'email' => $trainer->email,
            'phone' => $updatedPhone,
            'designation' => 'Senior Trainer',
            'department' => 'Digital Literacy',
            'district_id' => $districtId,
            'block_id' => $block->id,
            'gram_panchayat_id' => $gp->id,
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect('/trainers');
        $trainer->refresh();
        $userAccount->refresh();

        // Check FieldTrainer record updated
        $this->assertEquals('Updated Name', $trainer->name);
        $this->assertEquals('Senior Trainer', $trainer->designation);
        $this->assertEquals('Digital Literacy', $trainer->department);
        $this->assertEquals($updatedPhone, $trainer->phone);

        // Check linked User account updated
        $this->assertEquals('Updated Name', $userAccount->name);
        $this->assertEquals($updatedPhone, $userAccount->phone);

        // Test Toggle Status
        $toggleResponse = $this->actingAs($dlc)->post("/trainers/{$trainer->id}/toggle-status");
        $toggleResponse->assertRedirect();
        $trainer->refresh();
        $userAccount->refresh();

        $this->assertFalse($trainer->is_active);
        $this->assertFalse($userAccount->is_active);
    }
}
