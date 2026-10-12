<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FieldVerification;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FieldVerificationController extends Controller
{
    /**
     * Display listing of independent field verification reports.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authorize('viewAny', FieldVerification::class);

        $query = FieldVerification::with(['batch.tier', 'batch.district', 'batch.block', 'monitor']);

        if ($user->hasRole('field-monitor')) {
            $query->where('monitor_id', $user->id);
        } elseif ($user->hasRole('district-coordinator')) {
            $assignedIds = $user->assignedDistricts()->pluck('districts.id')->toArray();
            $query->whereHas('batch', function ($q) use ($assignedIds) {
                $q->whereIn('district_id', $assignedIds);
            });
        }

        $verifications = $query->latest('visit_date')->paginate(15);

        // Batches available for verification
        $availableBatches = TrainingBatch::forUserScope($user)
            ->whereIn('status', [TrainingBatch::STATUS_IN_PROGRESS, TrainingBatch::STATUS_AWAITING_EVIDENCE, TrainingBatch::STATUS_SUBMITTED, TrainingBatch::STATUS_VERIFIED])
            ->latest()
            ->limit(20)
            ->get();

        return view('verifications.index', compact('verifications', 'availableBatches'));
    }

    /**
     * Show form to submit field verification report.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', FieldVerification::class);

        /** @var User $user */
        $user = Auth::user();

        $selectedBatchId = $request->query('batch_id');
        $batch = null;
        if ($selectedBatchId) {
            $batch = TrainingBatch::forUserScope($user)->findOrFail($selectedBatchId);
        }

        $batches = TrainingBatch::forUserScope($user)
            ->with('district', 'block', 'tier')
            ->orderByDesc('scheduled_date')
            ->get();

        return view('verifications.create', compact('batches', 'batch'));
    }

    /**
     * Store field verification assessment.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FieldVerification::class);

        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'training_batch_id' => ['required', 'exists:training_batches,id'],
            'visit_date' => ['required', 'date'],
            'session_in_progress' => ['nullable'],
            'verified_headcount' => ['required', 'integer', 'min:0'],
            'banner_displayed' => ['nullable'],
            'kits_distributed' => ['nullable'],
            'tea_lunch_provided' => ['nullable'],
            'training_quality_rating' => ['required', 'in:excellent,good,average,poor'],
            'observations' => ['required', 'string', 'max:1000'],
            'corrective_actions_needed' => ['nullable', 'string', 'max:1000'],
        ]);

        $batch = TrainingBatch::findOrFail($validated['training_batch_id']);

        $verification = FieldVerification::create([
            'training_batch_id' => $batch->id,
            'monitor_id' => $user->id,
            'visit_date' => $validated['visit_date'],
            'session_in_progress' => isset($validated['session_in_progress']),
            'verified_headcount' => $validated['verified_headcount'],
            'banner_displayed' => isset($validated['banner_displayed']),
            'kits_distributed' => isset($validated['kits_distributed']),
            'tea_lunch_provided' => isset($validated['tea_lunch_provided']),
            'training_quality_rating' => $validated['training_quality_rating'],
            'observations' => $validated['observations'],
            'corrective_actions_needed' => $validated['corrective_actions_needed'] ?? null,
        ]);

        AuditLog::record('field_verification_logged', $verification, null, [
            'batch_code' => $batch->batch_code,
            'headcount' => $validated['verified_headcount'],
            'rating' => $validated['training_quality_rating'],
        ]);

        return redirect()->route('verifications.index')->with('status', "Field verification report for {$batch->batch_code} submitted successfully.");
    }
}
