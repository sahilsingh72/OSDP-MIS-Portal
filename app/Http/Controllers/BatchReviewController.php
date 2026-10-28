<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BatchReview;
use App\Models\TrainingBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatchReviewController extends Controller
{
    /**
     * Display the review queue.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authorize('viewAny', BatchReview::class);

        $query = TrainingBatch::forUserScope($user)
            ->with(['tier', 'district', 'block', 'trainers', 'uploads', 'summary', 'reviews'])
            ->whereIn('status', [
                TrainingBatch::STATUS_SUBMITTED,
                TrainingBatch::STATUS_UNDER_REVIEW,
                TrainingBatch::STATUS_AWAITING_EVIDENCE,
                TrainingBatch::STATUS_CORRECTION_REQUIRED,
            ]);

        $pendingBatches = $query->latest('submitted_at')->paginate(15);

        return view('reviews.index', compact('pendingBatches'));
    }

    /**
     * Submit a review decision (DC or PMU).
     */
    public function store(Request $request, TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('review', $batch);

        /** @var User $user */
        $user = Auth::user();

        $allowedDecisions = ['verified', 'correction_requested', 'rejected', 'pack_checked'];
        if ($user->hasRole('district-coordinator') && !$user->hasRole(['super-admin', 'pmu'])) {
            $allowedDecisions = ['pack_checked'];
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:' . implode(',', $allowedDecisions)],
            'remarks' => ['required', 'string', 'max:1000'],
            'checklist' => ['nullable', 'array'],
        ]);

        $roleAtReview = $user->hasRole('district-coordinator') ? 'district_coordinator' : ($user->hasRole('pmu') ? 'pmu' : 'super_admin');

        DB::transaction(function () use ($batch, $user, $roleAtReview, $validated) {
            BatchReview::create([
                'training_batch_id' => $batch->id,
                'reviewer_id' => $user->id,
                'role_at_review' => $roleAtReview,
                'decision' => $validated['decision'],
                'remarks' => $validated['remarks'],
                'checklist_snapshot' => $validated['checklist'] ?? null,
            ]);

            $oldStatus = $batch->status;

            // Apply status changes
            if ($validated['decision'] === BatchReview::DECISION_VERIFIED) {
                $batch->update([
                    'status' => TrainingBatch::STATUS_VERIFIED,
                    'verified_at' => Carbon::now(),
                ]);
            } elseif ($validated['decision'] === BatchReview::DECISION_CORRECTION) {
                $batch->update([
                    'status' => TrainingBatch::STATUS_CORRECTION_REQUIRED,
                ]);
            } elseif ($validated['decision'] === BatchReview::DECISION_REJECTED) {
                $batch->update([
                    'status' => TrainingBatch::STATUS_REJECTED,
                ]);
            } elseif ($validated['decision'] === BatchReview::DECISION_PACK_CHECKED) {
                $batch->update([
                    'status' => TrainingBatch::STATUS_UNDER_REVIEW,
                ]);
            }

            AuditLog::record('batch_reviewed', $batch, ['status' => $oldStatus], [
                'status' => $batch->status,
                'decision' => $validated['decision'],
                'reviewer' => $user->name,
            ]);
        });

        return redirect()->route('batches.show', $batch)->with('status', "Review decision '{$validated['decision']}' recorded successfully.");
    }
}
