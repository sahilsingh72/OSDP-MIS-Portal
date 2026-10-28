<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\District;
use App\Models\GramPanchayat;
use App\Models\Participant;
use App\Models\SystemSetting;
use App\Models\TrainingBatch;
use App\Models\TrainingModule;
use App\Models\TrainingPlan;
use App\Models\TrainingTier;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    /**
     * Display the public OSDP 2.0 Capacity Building Portal landing page.
     */
    public function index(): View
    {
        $activePlan = TrainingPlan::where('status', 'active')->first() ?? TrainingPlan::latest()->first();
        $totalDistricts = District::count() ?: 30;
        $totalBlocks = Block::count() ?: 314;
        $totalGps = GramPanchayat::count() ?: 6798;
        $targetBatches = $activePlan?->target_batches ?? 2149;
        $targetParticipants = $activePlan?->target_participants ?? 85960;

        $totalBatches = TrainingBatch::count();
        $scheduledBatches = TrainingBatch::whereIn('status', [
            TrainingBatch::STATUS_SCHEDULED,
            TrainingBatch::STATUS_IN_PROGRESS,
            TrainingBatch::STATUS_AWAITING_EVIDENCE,
            TrainingBatch::STATUS_SUBMITTED,
            TrainingBatch::STATUS_UNDER_REVIEW,
            TrainingBatch::STATUS_VERIFIED,
        ])->count();
        $verifiedBatches = TrainingBatch::where('status', TrainingBatch::STATUS_VERIFIED)->count();
        $registeredOfficials = Participant::count();

        $modules = TrainingModule::orderBy('module_number')->get();
        $tiers = TrainingTier::orderBy('id')->get();

        $recentBatches = TrainingBatch::with(['tier', 'district', 'venue'])
            ->whereIn('status', [
                TrainingBatch::STATUS_SCHEDULED,
                TrainingBatch::STATUS_IN_PROGRESS,
                TrainingBatch::STATUS_SUBMITTED,
                TrainingBatch::STATUS_VERIFIED,
            ])
            ->latest('scheduled_date')
            ->limit(4)
            ->get();

        return view('welcome', compact(
            'activePlan',
            'totalDistricts',
            'totalBlocks',
            'totalGps',
            'targetBatches',
            'targetParticipants',
            'totalBatches',
            'scheduledBatches',
            'verifiedBatches',
            'registeredOfficials',
            'modules',
            'tiers',
            'recentBatches'
        ));
    }
}
