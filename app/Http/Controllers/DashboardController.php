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

        // Calendar training events scoped to user authority
        $calendarBatches = (clone $batchQuery)
            ->with(['tier', 'district', 'block', 'venue', 'trainers'])
            ->whereNotNull('scheduled_date')
            ->orderBy('scheduled_date')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'batch_code' => $batch->batch_code,
                    'date' => $batch->scheduled_date->format('Y-m-d'),
                    'formatted_date' => $batch->scheduled_date->format('D, d M Y'),
                    'start_time' => $batch->start_time ? substr($batch->start_time, 0, 5) : null,
                    'end_time' => $batch->end_time ? substr($batch->end_time, 0, 5) : null,
                    'time_range' => ($batch->start_time && $batch->end_time)
                        ? Carbon::parse($batch->start_time)->format('h:i A') . ' - ' . Carbon::parse($batch->end_time)->format('h:i A')
                        : '10:00 AM - 05:30 PM',
                    'status' => $batch->status,
                    'status_label' => match($batch->status) {
                        'planned' => 'Planned',
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'awaiting_evidence' => 'Awaiting Evidence',
                        'submitted' => 'Submitted for Review',
                        'under_review' => 'Under Review',
                        'correction_required' => 'Correction Required',
                        'verified' => 'Verified & Accepted',
                        'rejected' => 'Rejected',
                        'closed' => 'Closed',
                        default => ucfirst(str_replace('_', ' ', $batch->status)),
                    },
                    'tier_name' => $batch->tier?->name ?? 'District Level',
                    'district_name' => $batch->district?->name ?? 'State-Level',
                    'block_name' => $batch->block?->name ?? null,
                    'venue_name' => $batch->venue?->name ?? 'Venue to be notified',
                    'trainer_names' => $batch->trainers->pluck('name')->implode(', ') ?: 'Trainer to be assigned',
                    'planned_participants' => $batch->planned_participants ?? 0,
                    'registered_participants' => $batch->registered_participants ?? 0,
                    'present_participants' => $batch->present_participants ?? 0,
                    'show_url' => route('batches.show', $batch),
                    'attendance_url' => route('batches.attendance.show', $batch),
                    'evidence_url' => route('batches.evidence.index', $batch),
                ];
            });

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
            'activePlan',
            'calendarBatches'
        ));
    }
}
