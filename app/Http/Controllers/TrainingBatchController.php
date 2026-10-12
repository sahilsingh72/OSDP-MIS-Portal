<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Block;
use App\Models\District;
use App\Models\GramPanchayat;
use App\Models\SystemSetting;
use App\Models\TrainingBatch;
use App\Models\TrainingModule;
use App\Models\TrainingPlan;
use App\Models\TrainingTier;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TrainingBatchController extends Controller
{
    /**
     * Display a listing of training batches with filters.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $this->authorize('viewAny', TrainingBatch::class);

        $query = TrainingBatch::forUserScope($user)
            ->with(['tier', 'district', 'block', 'venue', 'trainers']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tier_id')) {
            $query->where('training_tier_id', $request->input('tier_id'));
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }

        if ($request->filled('block_id')) {
            $query->where('block_id', $request->input('block_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->input('date_to'));
        }

        if ($request->boolean('overdue_only')) {
            $query->whereNotIn('status', [TrainingBatch::STATUS_VERIFIED, TrainingBatch::STATUS_CLOSED, TrainingBatch::STATUS_SUBMITTED])
                  ->where('submission_deadline', '<', Carbon::now());
        }

        $batches = $query->orderByDesc('scheduled_date')->paginate(15)->withQueryString();

        // Reference master data for filters
        $tiers = TrainingTier::where('is_active', true)->get();
        $districts = District::where('is_active', true)->get();
        if ($user->hasRole('district-coordinator')) {
            $assignedIds = $user->assignedDistricts()->pluck('districts.id')->toArray();
            $districts = $districts->whereIn('id', $assignedIds);
        }

        return view('batches.index', compact('batches', 'tiers', 'districts'));
    }

    /**
     * Show form for scheduling a new batch.
     */
    public function create(): View
    {
        $this->authorize('create', TrainingBatch::class);

        /** @var User $user */
        $user = Auth::user();

        $activePlan = TrainingPlan::where('status', 'active')->firstOrFail();
        $tiers = TrainingTier::where('is_active', true)->get();
        $districts = District::where('is_active', true)->get();

        if ($user->hasRole('district-coordinator')) {
            $assignedIds = $user->assignedDistricts()->pluck('districts.id')->toArray();
            $districts = $districts->whereIn('id', $assignedIds);
        }

        $trainers = User::role('field-trainer')->where('is_active', true)->get();
        $venues = Venue::where('is_active', true)->get();
        $modules = TrainingModule::where('is_active', true)->orderBy('module_number')->get();

        return view('batches.create', compact('activePlan', 'tiers', 'districts', 'trainers', 'venues', 'modules'));
    }

    /**
     * Store a newly created batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TrainingBatch::class);

        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'training_tier_id' => ['required', 'exists:training_tiers,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'block_id' => ['nullable', 'exists:blocks,id'],
            'venue_id' => ['nullable', 'exists:venues,id'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'planned_participants' => ['required', 'integer', 'min:5', 'max:100'],
            'primary_trainer_id' => ['required', 'exists:users,id'],
            'co_trainer_id' => ['nullable', 'exists:users,id', 'different:primary_trainer_id'],
            'cluster_gps' => ['nullable', 'array'],
            'cluster_gps.*' => ['exists:gram_panchayats,id'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['exists:training_modules,id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // Enforce district coordinator scope
        if ($user->hasRole('district-coordinator')) {
            if (!$validated['district_id'] || !$user->isAssignedToDistrict((int)$validated['district_id'])) {
                return back()->withErrors(['district_id' => 'You are only authorized to schedule batches within your assigned district.'])->withInput();
            }
        }

        // Validate administrative hierarchy: Block must belong to District
        if (!empty($validated['block_id']) && !empty($validated['district_id'])) {
            $blockBelongs = Block::where('id', $validated['block_id'])
                ->where('district_id', $validated['district_id'])
                ->exists();
            if (!$blockBelongs) {
                return back()->withErrors(['block_id' => 'The selected Block does not belong to the chosen District.'])->withInput();
            }
        }

        // Generate deterministic unique Batch Code
        $tier = TrainingTier::findOrFail($validated['training_tier_id']);
        $tierCode = match ($tier->code) {
            'directorate' => 'DIR',
            'district' => 'DST',
            'block' => 'BLK',
            'gram_panchayat' => 'GP',
            default => 'GEN',
        };

        $distCode = 'STA';
        if (!empty($validated['district_id'])) {
            $distCode = District::where('id', $validated['district_id'])->value('code') ?? 'DST';
        }

        $seq = TrainingBatch::count() + 1;
        $batchCode = sprintf('OSDP-%s-%s-%04d', $tierCode, $distCode, $seq);

        // Calculate 48-hour submission deadline from end of scheduled session
        $deadlineHours = (int)SystemSetting::get('submission_deadline_hours', 48);
        $scheduledEnd = Carbon::parse($validated['scheduled_date'] . ' ' . $validated['end_time']);
        $submissionDeadline = $scheduledEnd->copy()->addHours($deadlineHours);

        $activePlan = TrainingPlan::where('status', 'active')->firstOrFail();

        DB::transaction(function () use ($validated, $batchCode, $submissionDeadline, $activePlan, $user) {
            $batch = TrainingBatch::create([
                'batch_code' => $batchCode,
                'training_plan_id' => $activePlan->id,
                'training_tier_id' => $validated['training_tier_id'],
                'district_id' => $validated['district_id'] ?? null,
                'block_id' => $validated['block_id'] ?? null,
                'venue_id' => $validated['venue_id'] ?? null,
                'scheduled_date' => $validated['scheduled_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'status' => TrainingBatch::STATUS_SCHEDULED,
                'planned_participants' => $validated['planned_participants'],
                'submission_deadline' => $submissionDeadline,
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => $user->id,
            ]);

            // Assign primary trainer
            $batch->trainers()->attach($validated['primary_trainer_id'], ['role' => 'primary']);

            // Assign co-trainer if provided
            if (!empty($validated['co_trainer_id'])) {
                $batch->trainers()->attach($validated['co_trainer_id'], ['role' => 'co-trainer']);
            }

            // Sync cluster GPs if any
            if (!empty($validated['cluster_gps'])) {
                $batch->clusterGps()->sync($validated['cluster_gps']);
            }

            // Sync covered modules
            $batch->modules()->sync($validated['modules']);

            AuditLog::record('batch_created', $batch, null, $batch->toArray());
        });

        return redirect()->route('batches.index')->with('status', "Training Batch {$batchCode} scheduled successfully!");
    }

    /**
     * Display batch details, attendance summary, evidence status, and review logs.
     */
    public function show(TrainingBatch $batch): View
    {
        $this->authorize('view', $batch);

        $batch->load([
            'plan',
            'tier',
            'district',
            'block',
            'venue',
            'trainers',
            'clusterGps',
            'modules',
            'uploads.uploader',
            'summary',
            'feedback',
            'reviews.reviewer',
            'fieldVerifications.monitor',
            'batchParticipants.participant',
            'batchParticipants.attendance',
        ]);

        $evidenceStatus = $batch->evidence_status;

        return view('batches.show', compact('batch', 'evidenceStatus'));
    }

    /**
     * Show form for editing batch parameters.
     */
    public function edit(TrainingBatch $batch): View
    {
        $this->authorize('update', $batch);

        /** @var User $user */
        $user = Auth::user();

        $tiers = TrainingTier::where('is_active', true)->get();
        $districts = District::where('is_active', true)->get();

        if ($user->hasRole('district-coordinator')) {
            $assignedIds = $user->assignedDistricts()->pluck('districts.id')->toArray();
            $districts = $districts->whereIn('id', $assignedIds);
        }

        $trainers = User::role('field-trainer')->where('is_active', true)->get();
        $venues = Venue::where('is_active', true)->get();
        $modules = TrainingModule::where('is_active', true)->orderBy('module_number')->get();

        return view('batches.edit', compact('batch', 'tiers', 'districts', 'trainers', 'venues', 'modules'));
    }

    /**
     * Update batch parameters and handle status transitions.
     */
    public function update(Request $request, TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('update', $batch);

        $validated = $request->validate([
            'scheduled_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'actual_start_time' => ['nullable'],
            'actual_end_time' => ['nullable'],
            'status' => ['required', 'string'],
            'planned_participants' => ['required', 'integer', 'min:5'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $old = $batch->toArray();
        $batch->update($validated);

        AuditLog::record('batch_updated', $batch, $old, $batch->toArray());

        return redirect()->route('batches.show', $batch)->with('status', 'Batch details updated successfully.');
    }

    /**
     * Submit batch evidence pack for review.
     */
    public function submitForReview(TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('submit', $batch);

        $evidence = $batch->evidence_status;
        if (!$evidence['is_complete']) {
            return back()->withErrors(['evidence' => 'All six mandatory evidence items must be uploaded before submitting for verification.']);
        }

        $oldStatus = $batch->status;
        $batch->update([
            'status' => TrainingBatch::STATUS_SUBMITTED,
            'submitted_at' => Carbon::now(),
        ]);

        AuditLog::record('batch_submitted_for_review', $batch, ['status' => $oldStatus], ['status' => TrainingBatch::STATUS_SUBMITTED]);

        return back()->with('status', 'Batch evidence pack submitted successfully! It is now pending review.');
    }
}
