<?php

namespace App\Http\Controllers;

use App\Models\BatchParticipant;
use App\Models\ParticipantAssessment;
use App\Models\TrainingBatch;
use App\Models\TrainingTier;
use App\Models\TrainingUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ParticipantAssessmentController extends Controller
{
    /**
     * Display a listing of batches with Pre & Post Assessment completion metrics.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->can('assessments.view') || $user->can('feedback.view'), 403, 'Unauthorized access to Assessment Entry.');

        if ($request->filled('batch_id')) {
            return redirect()->route('assessments.batch', $request->input('batch_id'));
        }

        $query = TrainingBatch::forUserScope($user)
            ->with(['district', 'block', 'tier', 'venue'])
            ->withCount(['activeBatchParticipants as batch_participants_count'])
            ->withCount([
                'participantAssessments as pre_completed_count' => fn($q) => $q->where('pre_completed', true),
                'participantAssessments as post_completed_count' => fn($q) => $q->where('post_completed', true),
            ]);

        if ($request->filled('batch_search')) {
            $search = $request->input('batch_search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                  ->orWhereHas('district', fn($d) => $d->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('block', fn($bl) => $bl->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('venue', fn($v) => $v->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('tier_id')) {
            $query->where('training_tier_id', $request->input('tier_id'));
        }

        $batches = $query->orderByDesc('scheduled_date')->paginate(12)->withQueryString();
        $tiers = TrainingTier::orderBy('id')->get();

        return view('assessments.index', compact('batches', 'tiers'));
    }

    /**
     * Show enrolled participants and assessment entry sheet for a specific batch.
     */
    public function showBatch(TrainingBatch $batch): View
    {
        $user = Auth::user();
        abort_unless($user->can('assessments.view') || $user->can('feedback.view'), 403, 'Unauthorized access to Assessment Entry.');

        $this->authorize('view', $batch);

        $batch->load(['district', 'block', 'tier', 'venue', 'trainers', 'uploads']);

        // Load active enrolled participants with their assessment records
        $batchParticipants = $batch->batchParticipants()
            ->whereHas('participant', fn($q) => $q->whereNull('deleted_at'))
            ->with(['participant', 'assessment.enteredBy'])
            ->get()
            ->sortBy(fn($bp) => strtolower(trim($bp->participant?->name ?? '')), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // Get uploaded assessment PDFs if available in evidence pack
        $preUpload = $batch->uploads->where('is_valid', true)->firstWhere('evidence_type', TrainingUpload::TYPE_PRE_ASSESSMENT);
        $postUpload = $batch->uploads->where('is_valid', true)->firstWhere('evidence_type', TrainingUpload::TYPE_POST_ASSESSMENT);

        $enrolledCount = $batchParticipants->count();
        $assessments = $batch->participantAssessments;

        $preEnteredCount = $assessments->where('pre_completed', true)->count();
        $postEnteredCount = $assessments->where('post_completed', true)->count();

        $preAvgScore = $preEnteredCount > 0 ? round((float) $assessments->where('pre_completed', true)->avg('pre_score'), 2) : 0.00;
        $postAvgScore = $postEnteredCount > 0 ? round((float) $assessments->where('post_completed', true)->avg('post_score'), 2) : 0.00;

        $stats = [
            'enrolled_count' => $enrolledCount,
            'pre_entered_count' => $preEnteredCount,
            'pre_pending_count' => max(0, $enrolledCount - $preEnteredCount),
            'pre_completion_rate' => $enrolledCount > 0 ? round(($preEnteredCount / $enrolledCount) * 100) : 0,
            'pre_avg_score' => $preAvgScore,
            'post_entered_count' => $postEnteredCount,
            'post_pending_count' => max(0, $enrolledCount - $postEnteredCount),
            'post_completion_rate' => $enrolledCount > 0 ? round(($postEnteredCount / $enrolledCount) * 100) : 0,
            'post_avg_score' => $postAvgScore,
        ];

        $preQuestions = ParticipantAssessment::PRE_QUESTIONS;
        $postQuestions = ParticipantAssessment::POST_QUESTIONS;

        return view('assessments.batch', compact(
            'batch',
            'batchParticipants',
            'preUpload',
            'postUpload',
            'stats',
            'preQuestions',
            'postQuestions'
        ));
    }

    /**
     * Store or update participant pre and/or post assessment answers.
     */
    public function store(
        Request $request,
        TrainingBatch $batch,
        BatchParticipant $batchParticipant
    ): RedirectResponse|JsonResponse {
        $user = Auth::user();
        abort_unless($user->can('assessments.manage') || $user->can('feedback.manage'), 403, 'Unauthorized to enter assessments.');

        $this->authorize('manageAssessments', $batch);

        if ($batchParticipant->training_batch_id !== $batch->id) {
            abort(404, 'Participant is not enrolled in this batch.');
        }

        $validated = $request->validate([
            // Header snapshots
            'training_tier_snapshot' => ['nullable', 'string', 'max:50'],
            'venue_snapshot' => ['nullable', 'string', 'max:255'],
            'session_date' => ['nullable', 'date'],
            'designation_office_snapshot' => ['nullable', 'string', 'max:255'],
            'participant_name_snapshot' => ['nullable', 'string', 'max:255'],

            // PART A: Pre-Assessment (Yes = 1, No = 0)
            'pre_q1' => ['nullable'],
            'pre_q2' => ['nullable'],
            'pre_q3' => ['nullable'],
            'pre_q4' => ['nullable'],
            'pre_q5' => ['nullable'],
            'pre_q6' => ['nullable'],

            // PART B: Post-Assessment (A, B, C, D)
            'post_q1' => ['nullable', 'string', 'in:A,B,C,D,a,b,c,d'],
            'post_q2' => ['nullable', 'string', 'in:A,B,C,D,a,b,c,d'],
            'post_q3' => ['nullable', 'string', 'in:A,B,C,D,a,b,c,d'],
            'post_q4' => ['nullable', 'string', 'in:A,B,C,D,a,b,c,d'],
            'post_q5' => ['nullable', 'string', 'in:A,B,C,D,a,b,c,d'],
            'post_q6' => ['nullable', 'string', 'in:A,B,C,D,a,b,c,d'],
        ]);

        $existing = ParticipantAssessment::where('training_batch_id', $batch->id)
            ->where('batch_participant_id', $batchParticipant->id)
            ->first();

        $participant = $batchParticipant->participant;

        // Extract Pre-Assessment fields
        $preValues = [];
        $hasAnyPreInput = false;
        for ($i = 1; $i <= 6; $i++) {
            $key = "pre_q{$i}";
            if ($request->has($key) && $request->input($key) !== null && $request->input($key) !== '') {
                $raw = $request->input($key);
                $val = in_array(strtolower((string)$raw), ['1', 'true', 'yes', 'on'], true);
                $preValues[$key] = $val;
                $hasAnyPreInput = true;
            } elseif ($existing && $existing->$key !== null) {
                $preValues[$key] = (bool) $existing->$key;
            } else {
                $preValues[$key] = null;
            }
        }

        $preAnsweredCount = count(array_filter($preValues, fn($v) => $v !== null));
        $preCompleted = ($preAnsweredCount === 6);
        $preScore = $hasAnyPreInput || ($existing && $existing->pre_completed)
            ? ParticipantAssessment::computePreScore($preValues)
            : ($existing?->pre_score ?? null);

        // Extract Post-Assessment fields
        $postValues = [];
        $hasAnyPostInput = false;
        for ($i = 1; $i <= 6; $i++) {
            $key = "post_q{$i}";
            if ($request->filled($key)) {
                $postValues[$key] = strtoupper(trim($request->input($key)));
                $hasAnyPostInput = true;
            } elseif ($existing && $existing->$key !== null) {
                $postValues[$key] = $existing->$key;
            } else {
                $postValues[$key] = null;
            }
        }

        $postAnsweredCount = count(array_filter($postValues, fn($v) => !empty($v)));
        $postCompleted = ($postAnsweredCount === 6);
        $postScore = $hasAnyPostInput || ($existing && $existing->post_completed)
            ? ParticipantAssessment::computePostScore($postValues)
            : ($existing?->post_score ?? null);

        $assessment = ParticipantAssessment::updateOrCreate(
            [
                'training_batch_id' => $batch->id,
                'batch_participant_id' => $batchParticipant->id,
            ],
            [
                'participant_id' => $participant->id,
                'training_tier_snapshot' => $validated['training_tier_snapshot'] ?? $batch->tier?->name,
                'venue_snapshot' => $validated['venue_snapshot'] ?? $batch->venue?->name,
                'session_date' => $validated['session_date'] ?? $batch->scheduled_date,
                'designation_office_snapshot' => $validated['designation_office_snapshot'] ?? trim(($participant->designation ?? '') . ' / ' . ($participant->office_name ?? '')),
                'participant_name_snapshot' => $validated['participant_name_snapshot'] ?? $participant->name,

                'pre_q1' => $preValues['pre_q1'],
                'pre_q2' => $preValues['pre_q2'],
                'pre_q3' => $preValues['pre_q3'],
                'pre_q4' => $preValues['pre_q4'],
                'pre_q5' => $preValues['pre_q5'],
                'pre_q6' => $preValues['pre_q6'],
                'pre_score' => $preScore,
                'pre_completed' => $preCompleted,

                'post_q1' => $postValues['post_q1'],
                'post_q2' => $postValues['post_q2'],
                'post_q3' => $postValues['post_q3'],
                'post_q4' => $postValues['post_q4'],
                'post_q5' => $postValues['post_q5'],
                'post_q6' => $postValues['post_q6'],
                'post_score' => $postScore,
                'post_completed' => $postCompleted,

                'entered_by' => $user->id,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Assessment for {$participant->name} saved successfully.",
                'assessment' => $assessment->fresh(),
            ]);
        }

        return back()->with('status', "Assessment for {$participant->name} saved successfully.");
    }

    /**
     * Delete an entered participant assessment record.
     */
    public function destroy(
        TrainingBatch $batch,
        ParticipantAssessment $assessment
    ): RedirectResponse|JsonResponse {
        $user = Auth::user();
        abort_unless($user->can('assessments.manage') || $user->can('feedback.manage'), 403, 'Unauthorized to delete assessment.');

        $this->authorize('manageAssessments', $batch);

        if ($assessment->training_batch_id !== $batch->id) {
            abort(404, 'Assessment record does not belong to this batch.');
        }

        $assessment->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Participant assessment removed successfully.',
            ]);
        }

        return back()->with('status', 'Participant assessment removed successfully.');
    }
}
