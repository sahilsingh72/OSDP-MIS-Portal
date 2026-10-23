<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\District;
use App\Models\TrainingBatch;
use App\Models\TrainingPlan;
use App\Models\TrainingTier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Reports index and interactive hub.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authorize('viewAny', TrainingBatch::class);

        $reportType = $request->query('type', 'statewide_progress');

        $tiers = TrainingTier::all();
        $districts = District::all();
        if ($user->hasRole('district-coordinator')) {
            $districts = $districts->whereIn('id', $user->assignedDistricts()->pluck('districts.id'));
        }

        // Base scoped query
        $query = TrainingBatch::forUserScope($user)->with(['tier', 'district', 'block', 'trainers', 'uploads', 'summary', 'feedback']);

        if ($request->filled('tier_id')) {
            $query->where('training_tier_id', $request->input('tier_id'));
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'in_progress') {
                $query->whereIn('status', [
                    TrainingBatch::STATUS_IN_PROGRESS,
                    TrainingBatch::STATUS_AWAITING_EVIDENCE,
                    TrainingBatch::STATUS_CORRECTION_REQUIRED,
                ]);
            } elseif ($status === 'submitted') {
                $query->whereIn('status', [
                    TrainingBatch::STATUS_SUBMITTED,
                    TrainingBatch::STATUS_UNDER_REVIEW,
                ]);
            } else {
                $query->where('status', $status);
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->input('date_to'));
        }

        $reportData = match ($reportType) {
            'district_wise' => District::withCount([
                'batches as total_batches',
                'batches as completed_batches' => fn($q) => $q->where('status', TrainingBatch::STATUS_VERIFIED),
                'batches as pending_batches' => fn($q) => $q->whereIn('status', [TrainingBatch::STATUS_SUBMITTED, TrainingBatch::STATUS_UNDER_REVIEW, TrainingBatch::STATUS_AWAITING_EVIDENCE]),
            ])->withSum('batches as total_participants', 'present_participants')->get(),

            'tier_summary' => TrainingTier::withCount([
                'batches as total_batches',
                'batches as verified_batches' => fn($q) => $q->where('status', TrainingBatch::STATUS_VERIFIED),
            ])->withSum('batches as total_present', 'present_participants')->get(),

            'overdue_batches' => (clone $query)->whereNotIn('status', [TrainingBatch::STATUS_VERIFIED, TrainingBatch::STATUS_CLOSED, TrainingBatch::STATUS_SUBMITTED])
                ->where('submission_deadline', '<', Carbon::now())
                ->get(),

            default => $query->latest('scheduled_date')->paginate(20)->withQueryString(),
        };

        return view('reports.index', compact('reportType', 'reportData', 'tiers', 'districts'));
    }

    /**
     * Export report data as CSV spreadsheet.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authorize('viewAny', TrainingBatch::class);

        $reportType = $request->query('type', 'batches_export');

        AuditLog::record('report_exported_csv', null, null, ['report_type' => $reportType]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"osdp_report_{$reportType}_" . date('Ymd_His') . ".csv\"",
        ];

        return response()->stream(function () use ($user, $request) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Batch Code',
                'Tier',
                'District',
                'Block',
                'Date',
                'Status',
                'Planned',
                'Registered',
                'Present',
                'Evidence Complete?',
                'Overdue?',
                'Primary Trainer',
            ]);

            $batches = TrainingBatch::forUserScope($user)
                ->with(['tier', 'district', 'block', 'trainers', 'uploads'])
                ->latest('scheduled_date')
                ->chunk(100, function ($batchChunk) use ($handle) {
                    foreach ($batchChunk as $batch) {
                        $evidence = $batch->evidence_status;
                        fputcsv($handle, [
                            $batch->batch_code,
                            $batch->tier?->name ?? 'N/A',
                            $batch->district?->name ?? 'State-Level',
                            $batch->block?->name ?? 'N/A',
                            $batch->scheduled_date->format('Y-m-d'),
                            strtoupper(str_replace('_', ' ', $batch->status)),
                            $batch->planned_participants,
                            $batch->registered_participants,
                            $batch->present_participants,
                            $evidence['is_complete'] ? 'Yes' : 'No (' . $evidence['completed_count'] . '/6)',
                            $batch->is_overdue ? 'YES' : 'No',
                            $batch->trainers->first()?->name ?? 'Unassigned',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
