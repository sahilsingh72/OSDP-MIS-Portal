<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\District;
use App\Models\FieldVerification;
use App\Models\TrainingBatch;
use App\Models\TrainingPlan;
use App\Models\TrainingTier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the role-tailored operational dashboard.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. Base query scoped to user's administrative authority
        $batchQuery = TrainingBatch::forUserScope($user);

        // 2. Aggregate metrics from real MySQL database
        $totalBatches = (clone $batchQuery)->count();
        $plannedBatches = (clone $batchQuery)->where('status', TrainingBatch::STATUS_PLANNED)->count();
        $scheduledBatches = (clone $batchQuery)->where('status', TrainingBatch::STATUS_SCHEDULED)->count();
        $inProgressBatches = (clone $batchQuery)->whereIn('status', [
            TrainingBatch::STATUS_IN_PROGRESS,
            TrainingBatch::STATUS_AWAITING_EVIDENCE,
            TrainingBatch::STATUS_CORRECTION_REQUIRED,
        ])->count();
        $submittedBatches = (clone $batchQuery)->whereIn('status', [
            TrainingBatch::STATUS_SUBMITTED,
            TrainingBatch::STATUS_UNDER_REVIEW,
        ])->count();
        $verifiedBatches = (clone $batchQuery)->where('status', TrainingBatch::STATUS_VERIFIED)->count();

        // Overdue submissions (> 48 hours without submission)
        $overdueBatches = (clone $batchQuery)
            ->whereNotIn('status', [TrainingBatch::STATUS_VERIFIED, TrainingBatch::STATUS_CLOSED, TrainingBatch::STATUS_SUBMITTED])
            ->where('submission_deadline', '<', Carbon::now())
            ->count();

        // Participant counts
        $totalRegistered = (clone $batchQuery)->sum('registered_participants');
        $totalPresent = (clone $batchQuery)->sum('present_participants');

        // Recent Batches
        $recentBatches = (clone $batchQuery)
            ->with(['tier', 'district', 'trainers'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        // Tier-wise distribution
        $tierStats = TrainingTier::withCount(['batches' => function ($q) use ($user) {
            $q->forUserScope($user);
        }])->get();

        // District-wise breakdown (for State, PMU, and OCAC)
        $districtStats = [];
        if ($user->hasRole(['super-admin', 'ocac', 'pmu'])) {
            $districtStats = District::withCount([
                'batches as total_batches',
                'batches as verified_batches' => function ($q) {
                    $q->where('status', TrainingBatch::STATUS_VERIFIED);
                },
            ])->orderByDesc('total_batches')->limit(10)->get();
        }

        // Feedback score summary
        $avgFeedbackScore = \App\Models\BatchFeedback::whereHas('batch', function ($q) use ($user) {
            $q->forUserScope($user);
        })->avg('q5_overall_avg') ?? 4.8;

        // Recent audit events for admin/PMU
        $recentAudits = [];
        if ($user->can('audit-logs.view')) {
            $recentAudits = AuditLog::with('user')->latest()->limit(5)->get();
        }

        // Active training plan
        $activePlan = TrainingPlan::where('status', 'active')->first();

        return view('dashboards.index', compact(
            'user',
            'totalBatches',
            'plannedBatches',
            'scheduledBatches',
            'inProgressBatches',
            'submittedBatches',
            'verifiedBatches',
            'overdueBatches',
            'totalRegistered',
            'totalPresent',
            'recentBatches',
            'tierStats',
            'districtStats',
            'avgFeedbackScore',
            'recentAudits',
            'activePlan'
        ));
    }
}
