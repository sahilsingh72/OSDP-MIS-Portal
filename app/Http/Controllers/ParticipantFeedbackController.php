<?php

namespace App\Http\Controllers;

use App\Models\BatchParticipant;
use App\Models\ParticipantFeedback;
use App\Models\TrainingBatch;
use App\Models\TrainingTier;
use App\Services\ParticipantFeedbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ParticipantFeedbackController extends Controller
{
    /**
     * Display a listing of batches with Form 1 feedback completion metrics.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->can('feedback.view'), 403, 'Unauthorized access to Participant Feedback.');

        if ($request->filled('batch_id')) {
            return redirect()->route('feedback.batch', $request->input('batch_id'));
        }

        $query = TrainingBatch::forUserScope($user)
            ->with(['district', 'block', 'tier', 'venue', 'feedback'])
            ->withCount(['activeBatchParticipants as batch_participants_count'])
            ->withCount(['participantFeedback as feedback_count']);

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

        return view('feedback.index', compact('batches', 'tiers'));
    }

    /**
     * Show enrolled participants and Form 1 feedback transcription sheet for a specific batch.
     */
    public function showBatch(TrainingBatch $batch): View
    {
        $user = Auth::user();
        abort_unless($user->can('feedback.view'), 403, 'Unauthorized access to Participant Feedback.');

        $this->authorize('view', $batch);

        $batch->load(['district', 'block', 'tier', 'venue', 'trainers', 'feedback']);

        // Load active participants enrolled in this batch
        $batchParticipants = $batch->batchParticipants()
            ->whereHas('participant', fn($q) => $q->whereNull('deleted_at'))
            ->with(['participant', 'feedback.enteredBy'])
            ->get()
            ->sortBy(fn($bp) => strtolower(trim($bp->participant?->name ?? '')), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $enrolledCount = $batchParticipants->count();
        $enteredCount = $batchParticipants->filter(fn($bp) => $bp->feedback !== null)->count();

        // Calculate detailed rating breakdown
        $feedbacks = $batch->participantFeedback;
        $stats = [
            'enrolled_count' => $enrolledCount,
            'entered_count' => $enteredCount,
            'pending_count' => max(0, $enrolledCount - $enteredCount),
            'completion_rate' => $enrolledCount > 0 ? round(($enteredCount / $enrolledCount) * 100) : 0,
            'q1_avg' => $feedbacks->isNotEmpty() ? round((float) $feedbacks->avg('q1_clarity'), 2) : 0.00,
            'q2_avg' => $feedbacks->isNotEmpty() ? round((float) $feedbacks->avg('q2_knowledge'), 2) : 0.00,
            'q3_avg' => $feedbacks->isNotEmpty() ? round((float) $feedbacks->avg('q3_relevance'), 2) : 0.00,
            'q4_avg' => $feedbacks->isNotEmpty() ? round((float) $feedbacks->avg('q4_handbook'), 2) : 0.00,
            'q5_avg' => $feedbacks->isNotEmpty() ? round((float) $feedbacks->avg('q5_overall'), 2) : 0.00,
            'overall_avg' => $feedbacks->isNotEmpty() ? round((float) $feedbacks->avg('average_rating'), 2) : 0.00,
        ];

        return view('feedback.batch', compact('batch', 'batchParticipants', 'stats'));
    }

    /**
     * Store or update Form 1 participant feedback from physical form.
     */
    public function store(
        Request $request,
        TrainingBatch $batch,
        BatchParticipant $batchParticipant,
        ParticipantFeedbackService $feedbackService
    ): RedirectResponse|JsonResponse {
        $user = Auth::user();
        abort_unless($user->can('feedback.manage'), 403, 'Unauthorized to transcribe participant feedback.');

        $this->authorize('update', $batch);

        if ($batchParticipant->training_batch_id !== $batch->id) {
            abort(404, 'Participant is not enrolled in this batch.');
        }

        $validated = $request->validate([
            'q1_clarity' => ['required', 'integer', 'between:1,5'],
            'q2_knowledge' => ['required', 'integer', 'between:1,5'],
            'q3_relevance' => ['required', 'integer', 'between:1,5'],
            'q4_handbook' => ['required', 'integer', 'between:1,5'],
            'q5_overall' => ['required', 'integer', 'between:1,5'],
            'q6_most_useful' => ['required', 'string', 'max:2000'],
            'q7_improvements' => ['required', 'string', 'max:2000'],
            'q8_unanswered' => ['required', 'string', 'max:2000'],
            'participant_name_snapshot' => ['nullable', 'string', 'max:255'],
            'designation_office_snapshot' => ['nullable', 'string', 'max:255'],
            'session_date' => ['nullable', 'date'],
            'training_tier_snapshot' => ['nullable', 'string', 'max:50'],
            'venue_snapshot' => ['nullable', 'string', 'max:255'],
        ], [
            'q1_clarity.required' => 'Question 1 (Clarity of content) rating is mandatory (1-5).',
            'q2_knowledge.required' => 'Question 2 (Trainer knowledge) rating is mandatory (1-5).',
            'q3_relevance.required' => 'Question 3 (Relevance to daily work) rating is mandatory (1-5).',
            'q4_handbook.required' => 'Question 4 (Participant Handbook usefulness) rating is mandatory (1-5).',
            'q5_overall.required' => 'Question 5 (Overall satisfaction) rating is mandatory (1-5).',
            'q6_most_useful.required' => 'Question 6 (What was the most useful part) is mandatory.',
            'q7_improvements.required' => 'Question 7 (What could be improved) is mandatory.',
            'q8_unanswered.required' => 'Question 8 (Any question not answered) is mandatory.',
        ]);

        $participant = $batchParticipant->participant;

        $feedback = ParticipantFeedback::updateOrCreate(
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
                'q1_clarity' => $validated['q1_clarity'],
                'q2_knowledge' => $validated['q2_knowledge'],
                'q3_relevance' => $validated['q3_relevance'],
                'q4_handbook' => $validated['q4_handbook'],
                'q5_overall' => $validated['q5_overall'],
                'q6_most_useful' => $validated['q6_most_useful'] ?? null,
                'q7_improvements' => $validated['q7_improvements'] ?? null,
                'q8_unanswered' => $validated['q8_unanswered'] ?? null,
                'entered_by' => $user->id,
            ]
        );

        // Recalculate batch-level aggregates
        $feedbackService->syncBatchAggregates($batch);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Form 1 Feedback for {$participant->name} saved successfully.",
                'feedback' => $feedback->fresh(),
            ]);
        }

        return back()->with('status', "Form 1 Participant Feedback for {$participant->name} saved successfully.");
    }

    /**
     * Delete an entered participant feedback record.
     */
    public function destroy(
        TrainingBatch $batch,
        ParticipantFeedback $feedback,
        ParticipantFeedbackService $feedbackService
    ): RedirectResponse|JsonResponse {
        $user = Auth::user();
        abort_unless($user->can('feedback.manage'), 403, 'Unauthorized to delete feedback.');

        $this->authorize('update', $batch);

        if ($feedback->training_batch_id !== $batch->id) {
            abort(404, 'Feedback record does not belong to this batch.');
        }

        $feedback->delete();
        $feedbackService->syncBatchAggregates($batch);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Participant feedback removed successfully.',
            ]);
        }

        return back()->with('status', 'Participant feedback removed successfully.');
    }
}
