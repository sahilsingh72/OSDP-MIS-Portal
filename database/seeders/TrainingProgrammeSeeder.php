<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\BatchFeedback;
use App\Models\BatchParticipant;
use App\Models\BatchReview;
use App\Models\BatchSummary;
use App\Models\Block;
use App\Models\District;
use App\Models\FieldVerification;
use App\Models\GramPanchayat;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Models\TrainingModule;
use App\Models\TrainingPlan;
use App\Models\TrainingTier;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TrainingProgrammeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $pmu = User::where('email', 'pmu@osdp.gov.in')->first();
        $dcKhordha = User::where('email', 'dc.khordha@osdp.gov.in')->first();
        $trainer1 = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $trainer2 = User::where('email', 'trainer.patnaik@osdp.gov.in')->first();
        $monitor = User::where('email', 'monitor.das@osdp.gov.in')->first();

        $khordha = District::where('code', 'KHD')->first();
        $cuttack = District::where('code', 'CTC')->first();

        $tierDirectorate = TrainingTier::where('code', 'directorate')->first();
        $tierDistrict = TrainingTier::where('code', 'district')->first();
        $tierBlock = TrainingTier::where('code', 'block')->first();
        $tierGP = TrainingTier::where('code', 'gram_panchayat')->first();

        $venues = Venue::all();
        $modules = TrainingModule::all();

        // 1. Create Master Training Plan
        $plan = TrainingPlan::updateOrCreate(
            ['financial_year' => '2026-2027'],
            [
                'title' => 'OSDP 2.0 Statewide Capacity Building & Training Rollout',
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
                'target_batches' => 2149,
                'target_participants' => 41620,
                'status' => 'active',
                'remarks' => 'Approved by OCAC under RFP reference OCAC/OSDP/2026/01.',
                'created_by' => $pmu?->id ?? $admin?->id,
            ]
        );

        // 2. Create Common Pool of Sample Participants
        $participantData = [
            ['name' => 'Manoj Kumar Tripathy', 'designation' => 'Panchayat Executive Officer', 'department' => 'Panchayati Raj & Drinking Water', 'office_name' => 'Chandaka GP'],
            ['name' => 'Priyanka Priyadarshini', 'designation' => 'Welfare Assistant', 'department' => 'ST & SC Development', 'office_name' => 'Bhubaneswar Block'],
            ['name' => 'Ranjan Kumar Sahoo', 'designation' => 'Data Entry Operator', 'department' => 'Revenue & Disaster Management', 'office_name' => 'Jatni Tehsil'],
            ['name' => 'Sunita Mohapatra', 'designation' => 'Block IT Manager', 'department' => 'Electronics & IT', 'office_name' => 'Kantapada Block'],
            ['name' => 'Debasis Das', 'designation' => 'Assistant Section Officer', 'department' => 'Agriculture & FE', 'office_name' => 'Directorate of Agriculture'],
            ['name' => 'Lopamudra Nayak', 'designation' => 'District Programme Officer', 'department' => 'W & CD', 'office_name' => 'Collectorate Khordha'],
            ['name' => 'Ashok Kumar Behera', 'designation' => 'Panchayat Executive Officer', 'department' => 'Panchayati Raj', 'office_name' => 'Kantapada GP'],
            ['name' => 'Rashmita Mishra', 'designation' => 'Junior Assistant', 'department' => 'General Administration', 'office_name' => 'Collectorate Cuttack'],
            ['name' => 'Sanjay Rout', 'designation' => 'Gram Rozgar Sevak', 'department' => 'Panchayati Raj', 'office_name' => 'Mendhasala GP'],
            ['name' => 'Gitanjali Pradhan', 'designation' => 'Statistical Field Surveyor', 'department' => 'Planning & Convergence', 'office_name' => 'Khordha Sub-division'],
        ];

        $participantModels = [];
        foreach ($participantData as $pd) {
            $participantModels[] = Participant::firstOrCreate(
                ['name' => $pd['name'], 'office_name' => $pd['office_name']],
                $pd
            );
        }

        // 3. Batch 1: Directorate Level (Verified & Completed)
        $batch1 = TrainingBatch::updateOrCreate(
            ['batch_code' => 'OSDP-DIR-2026-0001'],
            [
                'training_plan_id' => $plan->id,
                'training_tier_id' => $tierDirectorate->id,
                'district_id' => $khordha->id,
                'block_id' => Block::where('district_id', $khordha->id)->where('name', 'Bhubaneswar')->value('id'),
                'venue_id' => $venues->first()->id,
                'scheduled_date' => Carbon::now()->subDays(6)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '17:30:00',
                'actual_start_time' => '10:15:00',
                'actual_end_time' => '17:40:00',
                'status' => TrainingBatch::STATUS_VERIFIED,
                'planned_participants' => 30,
                'registered_participants' => 28,
                'present_participants' => 27,
                'submission_deadline' => Carbon::now()->subDays(4),
                'submitted_at' => Carbon::now()->subDays(5),
                'verified_at' => Carbon::now()->subDays(4),
                'remarks' => 'Directorate Level Batch completed successfully. Full evidence pack received.',
                'created_by' => $pmu?->id,
            ]
        );
        $batch1->trainers()->sync([$trainer1->id => ['role' => 'primary'], $trainer2->id => ['role' => 'co-trainer']]);
        $batch1->modules()->sync($modules->pluck('id'));

        // Attach participants & attendance to Batch 1
        foreach (array_slice($participantModels, 0, 8) as $idx => $p) {
            $bp = BatchParticipant::firstOrCreate([
                'training_batch_id' => $batch1->id,
                'participant_id' => $p->id,
            ], ['serial_number' => $idx + 1]);

            AttendanceRecord::updateOrCreate([
                'training_batch_id' => $batch1->id,
                'batch_participant_id' => $bp->id,
            ], [
                'morning_present' => true,
                'afternoon_present' => ($idx !== 3), // one participant absent in afternoon
                'remarks' => 'Verified on paper Form 2',
                'marked_by' => $trainer1->id,
            ]);
        }

        // Summary for Batch 1
        BatchSummary::updateOrCreate(
            ['training_batch_id' => $batch1->id],
            [
                'registered_count' => 28,
                'present_count' => 27,
                'feedback_forms_received' => 27,
                'actual_start_time' => '10:15:00',
                'actual_end_time' => '17:40:00',
                'pre_post_assessment_done' => true,
                'what_went_well' => 'Interactive case study simulation; active participation by department heads.',
                'problems_faced' => 'None. Projector and audio systems functioned properly.',
                'follow_up_actions' => 'Circulate digital copy of Data Policy to all participants.',
                'submitted_by_name' => $trainer1->name,
                'checked_by_name' => $dcKhordha->name,
                'checked_date' => Carbon::now()->subDays(5)->toDateString(),
            ]
        );

        // Feedback for Batch 1
        BatchFeedback::updateOrCreate(
            ['training_batch_id' => $batch1->id],
            [
                'q1_clarity_avg' => 4.80,
                'q2_knowledge_avg' => 4.90,
                'q3_relevance_avg' => 4.75,
                'q4_handbook_avg' => 4.85,
                'q5_overall_avg' => 4.82,
                'total_forms_analyzed' => 27,
                'qualitative_highlights' => 'Exceptional clarity on inter-departmental data sharing rules.',
                'improvement_suggestions' => 'Include more department-specific data flow diagrams.',
            ]
        );

        // Reviews for Batch 1
        BatchReview::firstOrCreate([
            'training_batch_id' => $batch1->id,
            'decision' => BatchReview::DECISION_PACK_CHECKED,
        ], [
            'reviewer_id' => $dcKhordha->id,
            'role_at_review' => 'district_coordinator',
            'remarks' => 'Physical attendance sheet and feedback forms checked. Pack complete.',
            'checklist_snapshot' => ['attendance_verified' => true, 'photos_verified' => true],
        ]);

        BatchReview::firstOrCreate([
            'training_batch_id' => $batch1->id,
            'decision' => BatchReview::DECISION_VERIFIED,
        ], [
            'reviewer_id' => $pmu->id,
            'role_at_review' => 'pmu',
            'remarks' => 'Evidence thoroughly verified against RFP requirements. Batch accepted for milestone settlement.',
            'checklist_snapshot' => ['all_6_items_valid' => true],
        ]);

        // 4. Batch 2: Block Level in Cuttack (Under Review / Submitted)
        $batch2 = TrainingBatch::updateOrCreate(
            ['batch_code' => 'OSDP-BLK-CTC-0014'],
            [
                'training_plan_id' => $plan->id,
                'training_tier_id' => $tierBlock->id,
                'district_id' => $cuttack->id,
                'block_id' => Block::where('district_id', $cuttack->id)->where('name', 'Kantapada')->value('id'),
                'venue_id' => Venue::where('district_id', $cuttack->id)->where('name', 'like', '%Kantapada%')->value('id'),
                'scheduled_date' => Carbon::now()->subDays(2)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '17:30:00',
                'actual_start_time' => '10:30:00',
                'actual_end_time' => '17:15:00',
                'status' => TrainingBatch::STATUS_SUBMITTED,
                'planned_participants' => 30,
                'registered_participants' => 30,
                'present_participants' => 29,
                'submission_deadline' => Carbon::now()->addHours(6),
                'submitted_at' => Carbon::now()->subHours(10),
                'remarks' => 'Block training conducted. Pack submitted by Field Trainer.',
                'created_by' => $pmu?->id,
            ]
        );
        $batch2->trainers()->sync([$trainer1->id => ['role' => 'primary']]);
        $batch2->modules()->sync($modules->whereIn('module_number', [1, 2, 3, 4, 7])->pluck('id'));

        // Batch 2 Summary
        BatchSummary::updateOrCreate(
            ['training_batch_id' => $batch2->id],
            [
                'registered_count' => 30,
                'present_count' => 29,
                'feedback_forms_received' => 29,
                'actual_start_time' => '10:30:00',
                'actual_end_time' => '17:15:00',
                'pre_post_assessment_done' => true,
                'what_went_well' => 'High engagement on frontline data register management.',
                'problems_faced' => 'Brief 15-minute power cut; resumed smoothly.',
                'follow_up_actions' => 'Ensure GP data operators receive printed quick-reference SOP cards.',
                'submitted_by_name' => $trainer1->name,
                'checked_by_name' => 'Smita Dash',
                'checked_date' => Carbon::now()->subHours(12)->toDateString(),
            ]
        );

        // 5. Batch 3: Gram Panchayat Cluster Batch (Awaiting Evidence / In Progress)
        $batch3 = TrainingBatch::updateOrCreate(
            ['batch_code' => 'OSDP-GP-KHD-0042'],
            [
                'training_plan_id' => $plan->id,
                'training_tier_id' => $tierGP->id,
                'district_id' => $khordha->id,
                'block_id' => Block::where('district_id', $khordha->id)->where('name', 'Bhubaneswar')->value('id'),
                'venue_id' => $venues->first()->id,
                'scheduled_date' => Carbon::now()->subDay()->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
                'actual_start_time' => '10:00:00',
                'actual_end_time' => '14:15:00',
                'status' => TrainingBatch::STATUS_AWAITING_EVIDENCE,
                'planned_participants' => 29,
                'registered_participants' => 28,
                'present_participants' => 26,
                'submission_deadline' => Carbon::now()->addHours(24),
                'remarks' => 'Cluster GP session covering Chandaka and Mendhasala Gram Panchayats.',
                'created_by' => $dcKhordha?->id,
            ]
        );
        $batch3->trainers()->sync([$trainer2->id => ['role' => 'primary']]);
        $gps = GramPanchayat::whereIn('name', ['Chandaka', 'Mendhasala'])->pluck('id');
        $batch3->clusterGps()->sync($gps);
        $batch3->modules()->sync($modules->whereIn('module_number', [1, 2, 4, 7])->pluck('id'));

        // 6. Batch 4: Upcoming Scheduled Batch
        $batch4 = TrainingBatch::updateOrCreate(
            ['batch_code' => 'OSDP-DST-CTC-0005'],
            [
                'training_plan_id' => $plan->id,
                'training_tier_id' => $tierDistrict->id,
                'district_id' => $cuttack->id,
                'block_id' => Block::where('district_id', $cuttack->id)->where('name', 'Cuttack Sadar')->value('id'),
                'venue_id' => Venue::where('district_id', $cuttack->id)->where('name', 'like', '%Collectorate%')->value('id'),
                'scheduled_date' => Carbon::now()->addDays(3)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '17:30:00',
                'status' => TrainingBatch::STATUS_SCHEDULED,
                'planned_participants' => 40,
                'registered_participants' => 0,
                'present_participants' => 0,
                'remarks' => 'District level batch scheduled for Cuttack district officials.',
                'created_by' => $pmu?->id,
            ]
        );
        $batch4->trainers()->sync([$trainer1->id => ['role' => 'primary'], $trainer2->id => ['role' => 'co-trainer']]);
        $batch4->modules()->sync($modules->pluck('id'));

        // 7. Field Verification on Batch 2 by Independent Monitor
        FieldVerification::firstOrCreate([
            'training_batch_id' => $batch2->id,
            'monitor_id' => $monitor->id,
        ], [
            'visit_date' => Carbon::now()->subDays(2)->toDateString(),
            'session_in_progress' => true,
            'verified_headcount' => 29,
            'banner_displayed' => true,
            'kits_distributed' => true,
            'tea_lunch_provided' => true,
            'training_quality_rating' => 'good',
            'observations' => 'Session observed in progress. Trainer demonstrated Module 3 and 4 clearly. All participants had handbooks.',
            'corrective_actions_needed' => 'Ensure backup projector bulb available on site.',
        ]);
    }
}
