<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\District;
use App\Models\FieldTrainer;
use App\Models\GramPanchayat;
use App\Models\TotBatch;
use App\Models\TotBatchTrainer;
use App\Models\TotVenue;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TotBatchModuleTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $pmu;
    protected User $dlc;
    protected User $ocac;
    protected District $district;
    protected Block $block;
    protected GramPanchayat $gp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::role('super-admin')->firstOrFail();
        $this->pmu = User::role('pmu')->firstOrFail();
        $this->dlc = User::role('district-coordinator')->firstOrFail();
        $this->ocac = User::role('ocac')->firstOrFail();

        $this->district = $this->dlc->assignedDistricts->first() ?: District::firstOrFail();
        $this->block = Block::where('district_id', $this->district->id)->firstOrFail();
        $this->gp = GramPanchayat::where('block_id', $this->block->id)->firstOrFail();
    }

    protected function createFieldTrainer(string $suffix): FieldTrainer
    {
        $email = "tot_ft_{$suffix}_" . uniqid() . '@osdp.gov.in';
        $phone = '98' . rand(10000000, 99999999);

        $user = User::create([
            'name' => "TOT Candidate {$suffix}",
            'email' => $email,
            'phone' => $phone,
            'designation' => 'Master Trainer',
            'department' => 'OSDP Training Cell',
            'district_id' => $this->district->id,
            'block_id' => $this->block->id,
            'gram_panchayat_id' => $this->gp->id,
            'password' => Hash::make('Trainer@12345'),
            'is_active' => true,
        ]);
        $user->assignRole('field-trainer');

        return FieldTrainer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $email,
            'phone' => $phone,
            'designation' => 'Master Trainer',
            'department' => 'OSDP Training Cell',
            'district_id' => $this->district->id,
            'block_id' => $this->block->id,
            'gram_panchayat_id' => $this->gp->id,
            'is_active' => true,
            'created_by' => $this->superAdmin->id,
        ]);
    }

    public function test_tot_subsidebar_is_rendered_under_field_trainers_for_authorized_users(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('trainers.index'));

        $response->assertOk();
        $response->assertSee('Field Trainers');
        $response->assertSee('Trainers Directory');
        $response->assertSee('TOT');
        $response->assertSee(route('trainers.tot.index'));
    }

    public function test_can_create_new_tot_venue_inline_and_reuse_existing_tot_venues(): void
    {
        // 1. Create first batch by creating a new TOT venue inline
        $newVenueName = 'IIIT Seminar Hall ' . uniqid();
        $code1 = 'TOT-VEN1-' . rand(100, 999);

        $res1 = $this->actingAs($this->superAdmin)->post(route('trainers.tot.store'), [
            'batch_code' => $code1,
            'training_date' => now()->toDateString(),
            'venue_mode' => 'new',
            'venue_name' => $newVenueName,
            'venue_address' => 'Gothapatna Campus',
            'district_id' => $this->district->id,
        ]);

        $batch1 = TotBatch::where('batch_code', $code1)->firstOrFail();
        $res1->assertRedirect(route('trainers.tot.show', $batch1));

        $createdVenue = TotVenue::where('name', $newVenueName)->first();
        $this->assertNotNull($createdVenue);
        $this->assertEquals($createdVenue->id, $batch1->tot_venue_id);
        $this->assertEquals($newVenueName, $batch1->venue_name);
        $this->assertEquals('Gothapatna Campus', $batch1->venue_address);

        // 2. Verify TOT batches index lists the existing TOT venue & "+ Create New Venue" option
        $indexRes = $this->actingAs($this->pmu)->get(route('trainers.tot.index'));
        $indexRes->assertOk();
        $indexRes->assertSee('Existing TOT Venue');
        $indexRes->assertSee('+ Create New Venue');
        $indexRes->assertSee($newVenueName);

        // 3. Create second batch selecting the existing TOT venue
        $code2 = 'TOT-VEN2-' . rand(100, 999);
        $res2 = $this->actingAs($this->pmu)->post(route('trainers.tot.store'), [
            'batch_code' => $code2,
            'training_date' => now()->toDateString(),
            'venue_mode' => 'existing',
            'tot_venue_id' => $createdVenue->id,
        ]);

        $batch2 = TotBatch::where('batch_code', $code2)->firstOrFail();
        $res2->assertRedirect(route('trainers.tot.show', $batch2));
        $this->assertEquals($createdVenue->id, $batch2->tot_venue_id);
        $this->assertEquals($newVenueName, $batch2->venue_name);
        $this->assertEquals('Gothapatna Campus', $batch2->venue_address);

        // 4. Validation requires existing tot_venue_id when venue_mode is existing
        $badExisting = $this->actingAs($this->pmu)->post(route('trainers.tot.store'), [
            'batch_code' => 'TOT-BAD-' . rand(100, 999),
            'training_date' => now()->toDateString(),
            'venue_mode' => 'existing',
            'tot_venue_id' => '',
        ]);
        $badExisting->assertSessionHasErrors('tot_venue_id');
    }

    public function test_only_super_admin_and_pmu_can_create_edit_and_delete_tot_batches(): void
    {
        $code1 = 'TOT-SA-' . rand(100, 999);
        $saCreate = $this->actingAs($this->superAdmin)->post(route('trainers.tot.store'), [
            'batch_code' => $code1,
            'title' => 'State Master Trainer Orientation',
            'training_date' => now()->toDateString(),
            'venue_name' => 'OCAC Auditorium, Acharya Vihar',
            'venue_address' => '2nd Floor Conference Wing',
            'district_id' => $this->district->id,
            'remarks' => '1-day state TOT program',
        ]);

        $batch = TotBatch::where('batch_code', $code1)->firstOrFail();
        $saCreate->assertRedirect(route('trainers.tot.show', $batch));
        $this->assertEquals('OCAC Auditorium, Acharya Vihar', $batch->venue_name);

        // PMU can create & update
        $code2 = 'TOT-PMU-' . rand(100, 999);
        $pmuCreate = $this->actingAs($this->pmu)->post(route('trainers.tot.store'), [
            'batch_code' => $code2,
            'training_date' => now()->toDateString(),
            'venue_name' => 'SIRD Main Hall',
        ]);
        $pmuBatch = TotBatch::where('batch_code', $code2)->firstOrFail();
        $pmuCreate->assertRedirect(route('trainers.tot.show', $pmuBatch));

        $pmuUpdate = $this->actingAs($this->pmu)->put(route('trainers.tot.update', $pmuBatch), [
            'batch_code' => $code2,
            'title' => 'Updated PMU TOT',
            'training_date' => now()->toDateString(),
            'venue_name' => 'SIRD Annex Hall B',
            'status' => TotBatch::STATUS_COMPLETED,
        ]);
        $pmuUpdate->assertRedirect();
        $this->assertEquals('SIRD Annex Hall B', $pmuBatch->fresh()->venue_name);
        $this->assertEquals(TotBatch::STATUS_COMPLETED, $pmuBatch->fresh()->status);

        // DLC, OCAC, and Field Trainer cannot create, update, or delete TOT batches
        foreach ([$this->dlc, $this->ocac] as $readOnlyUser) {
            $this->actingAs($readOnlyUser)->post(route('trainers.tot.store'), [
                'batch_code' => 'TOT-FORBID-' . rand(100, 999),
                'training_date' => now()->toDateString(),
                'venue_name' => 'Unauthorized Hall',
            ])->assertForbidden();

            $this->actingAs($readOnlyUser)->put(route('trainers.tot.update', $batch), [
                'batch_code' => $code1,
                'training_date' => now()->toDateString(),
                'venue_name' => 'Hacked Hall',
            ])->assertForbidden();

            $this->actingAs($readOnlyUser)->delete(route('trainers.tot.destroy', $batch))
                ->assertForbidden();
        }

        $trainer = $this->createFieldTrainer('NonAdmin');
        $this->actingAs($trainer->user)->get(route('trainers.tot.index'))->assertForbidden();

        // Super Admin can delete
        $this->actingAs($this->superAdmin)->delete(route('trainers.tot.destroy', $pmuBatch))
            ->assertRedirect(route('trainers.tot.index'));
        $this->assertDatabaseMissing('tot_batches', ['id' => $pmuBatch->id]);
    }

    public function test_enroll_trainers_prevent_duplicates_and_remove_from_batch(): void
    {
        $t1 = $this->createFieldTrainer('Alpha');
        $t2 = $this->createFieldTrainer('Beta');

        $batch = TotBatch::create([
            'batch_code' => 'TOT-ENROLL-' . uniqid(),
            'training_date' => now()->toDateString(),
            'venue_name' => 'District Collectorate Hall',
            'district_id' => $this->district->id,
            'created_by' => $this->pmu->id,
        ]);

        // DLC cannot enroll trainers
        $this->actingAs($this->dlc)->post(route('trainers.tot.enroll', $batch), [
            'trainer_ids' => [$t1->id],
        ])->assertForbidden();

        // PMU enrolls t1 & t2
        $res = $this->actingAs($this->pmu)->post(route('trainers.tot.enroll', $batch), [
            'trainer_ids' => [$t1->id, $t2->id],
        ]);
        $res->assertRedirect();
        $this->assertEquals(2, TotBatchTrainer::where('tot_batch_id', $batch->id)->count());

        // Re-submitting t1 safely skips duplicates
        $this->actingAs($this->pmu)->post(route('trainers.tot.enroll', $batch), [
            'trainer_ids' => [$t1->id],
        ])->assertRedirect();
        $this->assertEquals(2, TotBatchTrainer::where('tot_batch_id', $batch->id)->count());

        // Remove t2
        $this->actingAs($this->pmu)->delete(route('trainers.tot.remove-trainer', [$batch, $t2]))
            ->assertRedirect();
        $this->assertEquals(1, TotBatchTrainer::where('tot_batch_id', $batch->id)->count());
        $this->assertDatabaseMissing('tot_batch_trainers', [
            'tot_batch_id' => $batch->id,
            'field_trainer_id' => $t2->id,
        ]);
    }

    public function test_one_day_trainer_attendance_and_readonly_views(): void
    {
        $t1 = $this->createFieldTrainer('PresentOne');
        $t2 = $this->createFieldTrainer('AbsentTwo');

        $batch = TotBatch::create([
            'batch_code' => 'TOT-ATT-' . uniqid(),
            'title' => '1-Day Khordha TOT',
            'training_date' => now()->toDateString(),
            'venue_name' => 'DRDA Conference Room',
            'district_id' => $this->district->id,
            'created_by' => $this->superAdmin->id,
        ]);

        $e1 = TotBatchTrainer::create([
            'tot_batch_id' => $batch->id,
            'field_trainer_id' => $t1->id,
            'is_present' => false,
        ]);
        $e2 = TotBatchTrainer::create([
            'tot_batch_id' => $batch->id,
            'field_trainer_id' => $t2->id,
            'is_present' => false,
        ]);

        // DLC cannot mark attendance
        $this->actingAs($this->dlc)->post(route('trainers.tot.attendance', $batch), [
            'attendance' => [
                $e1->id => ['is_present' => '1'],
            ],
        ])->assertForbidden();

        // Super Admin marks 1-day attendance and completes batch
        $saveRes = $this->actingAs($this->superAdmin)->post(route('trainers.tot.attendance', $batch), [
            'attendance' => [
                $e1->id => ['is_present' => '1', 'remarks' => 'Attended full 1-day TOT'],
                $e2->id => ['remarks' => 'On leave'],
            ],
            'mark_completed' => '1',
        ]);
        $saveRes->assertRedirect();

        $this->assertDatabaseHas('tot_batch_trainers', [
            'id' => $e1->id,
            'is_present' => true,
            'remarks' => 'Attended full 1-day TOT',
            'marked_by' => $this->superAdmin->id,
        ]);
        $this->assertDatabaseHas('tot_batch_trainers', [
            'id' => $e2->id,
            'is_present' => false,
            'remarks' => 'On leave',
        ]);
        $this->assertEquals(TotBatch::STATUS_COMPLETED, $batch->fresh()->status);

        // Page renders venue, trainers, and 1-day attendance metrics
        $showRes = $this->actingAs($this->superAdmin)->get(route('trainers.tot.show', $batch));
        $showRes->assertOk();
        $showRes->assertSee('DRDA Conference Room');
        $showRes->assertSee($t1->name);
        $showRes->assertSee($t2->name);
        $showRes->assertSee('1 Present');

        // OCAC & DLC can view in read-only mode without Save button
        $ocacRes = $this->actingAs($this->ocac)->get(route('trainers.tot.show', $batch));
        $ocacRes->assertOk();
        $ocacRes->assertSee('DRDA Conference Room');
        $ocacRes->assertDontSee('Save 1-Day Trainer Attendance');

        $dlcRes = $this->actingAs($this->dlc)->get(route('trainers.tot.show', $batch));
        $dlcRes->assertOk();
        $dlcRes->assertSee($t1->name);
        $dlcRes->assertDontSee('Save 1-Day Trainer Attendance');

        // Trainers Directory shows TOT batch status badge
        $dirRes = $this->actingAs($this->superAdmin)->get(route('trainers.index', ['search' => $t1->email]));
        $dirRes->assertOk();
        $dirRes->assertSee($batch->batch_code);
        $dirRes->assertSee('Present');
    }
}
