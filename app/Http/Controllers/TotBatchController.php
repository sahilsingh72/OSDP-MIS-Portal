<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\District;
use App\Models\FieldTrainer;
use App\Models\TotBatch;
use App\Models\TotBatchTrainer;
use App\Models\TotVenue;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TotBatchController extends Controller
{
    /**
     * Display a listing of 1-day TOT (Training of Trainers) batches.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $this->authorizeView($user);

        $query = $this->scopeBatchesForUser(TotBatch::with(['district', 'creator', 'totVenue']), $user)
            ->withCount([
                'enrollments as enrolled_count',
                'enrollments as present_count' => fn($q) => $q->where('is_present', true),
            ]);

        if (!$user->hasRole('district-coordinator') && $request->filled('district_id')) {
            $query->where('district_id', $request->input('district_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%")
                  ->orWhere('venue_address', 'like', "%{$search}%");
            });
        }

        // Summary KPI counts scoped to authorized view
        $scopedBatchIds = $this->scopeBatchesForUser(TotBatch::query(), $user)->pluck('id');
        $totalBatches = $scopedBatchIds->count();

        $enrollmentQuery = TotBatchTrainer::whereIn('tot_batch_id', $scopedBatchIds);
        if ($user->hasRole('district-coordinator')) {
            $assignedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            $enrollmentQuery->whereHas('fieldTrainer', fn($t) => $t->whereIn('district_id', $assignedDistrictIds));
        }
        $totalEnrolled = (clone $enrollmentQuery)->count();
        $totalPresent = (clone $enrollmentQuery)->where('is_present', true)->count();

        $batches = $query->orderByDesc('training_date')->orderByDesc('id')->paginate(12)->withQueryString();

        $districts = $user->hasRole('district-coordinator')
            ? $user->assignedDistricts
            : District::where('is_active', true)->orderBy('name')->get();

        $canManage = $this->canManageTot($user);
        $suggestedCode = $this->generateNextBatchCode();
        $totVenues = $canManage
            ? TotVenue::where('is_active', true)->with('district')->orderBy('name')->get()
            : collect();

        return view('trainers.tot.index', compact(
            'batches',
            'districts',
            'totVenues',
            'canManage',
            'totalBatches',
            'totalEnrolled',
            'totalPresent',
            'suggestedCode'
        ));
    }

    /**
     * Store a new 1-day TOT batch (Super Admin / PMU only).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeManage($user);

        $validated = $this->validateBatchPayload($request);

        $totBatch = DB::transaction(function () use ($validated, $user) {
            $venueData = $this->resolveTotVenue($validated, $user);
            $attributes = array_merge(
                $this->normalizeBatchAttributes($validated, TotBatch::STATUS_SCHEDULED),
                $venueData,
                ['created_by' => $user->id]
            );

            $batch = TotBatch::create($attributes);
            AuditLog::record('tot_batch_created', $batch, null, $batch->toArray());

            return $batch;
        });

        return redirect()->route('trainers.tot.show', $totBatch)
            ->with('status', "TOT Batch '{$totBatch->batch_code}' created. Add field trainers below to manage attendance.");
    }

    /**
     * Display selected TOT batch, venue details, enrolled trainers list, and 1-day attendance sheet.
     */
    public function show(TotBatch $totBatch): View
    {
        $user = Auth::user();
        $this->authorizeView($user);
        $this->authorizeViewBatch($user, $totBatch);

        $totBatch->load(['district', 'creator', 'totVenue']);

        $enrollmentQuery = $totBatch->enrollments()
            ->whereHas('fieldTrainer')
            ->with(['fieldTrainer.district', 'fieldTrainer.block', 'markedBy']);

        if ($user->hasRole('district-coordinator')) {
            $assignedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            $enrollmentQuery->whereHas('fieldTrainer', fn($q) => $q->whereIn('district_id', $assignedDistrictIds));
        }

        $enrollments = $enrollmentQuery->get()
            ->sortBy(fn($e) => strtolower(trim($e->fieldTrainer?->name ?? '')), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $enrolledTrainerIds = $totBatch->enrollments()->pluck('field_trainer_id')->toArray();
        $enrolledCount = $enrollments->count();
        $presentCount = $enrollments->where('is_present', true)->count();

        $canManage = $this->canManageTot($user);

        $availableTrainers = $canManage
            ? FieldTrainer::where('is_active', true)->with(['district', 'block'])->orderBy('name')->get()
            : collect();

        $districts = District::where('is_active', true)->orderBy('name')->get();
        $totVenues = $canManage
            ? TotVenue::where('is_active', true)->with('district')->orderBy('name')->get()
            : collect();

        return view('trainers.tot.show', compact(
            'totBatch',
            'enrollments',
            'enrolledTrainerIds',
            'enrolledCount',
            'presentCount',
            'canManage',
            'availableTrainers',
            'districts',
            'totVenues'
        ));
    }

    /**
     * Update an existing TOT batch's date, venue, or status (Super Admin / PMU only).
     */
    public function update(Request $request, TotBatch $totBatch): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeManage($user);

        $validated = $this->validateBatchPayload($request, $totBatch->id);
        $oldValues = $totBatch->toArray();

        DB::transaction(function () use ($totBatch, $validated, $oldValues, $user) {
            $venueData = $this->resolveTotVenue($validated, $user);
            $attributes = array_merge(
                $this->normalizeBatchAttributes($validated, $totBatch->status),
                $venueData
            );

            $totBatch->update($attributes);
            AuditLog::record('tot_batch_updated', $totBatch, $oldValues, $totBatch->toArray());
        });

        return back()->with('status', "TOT Batch '{$totBatch->batch_code}' updated successfully.");
    }

    /**
     * Delete a TOT batch (Super Admin / PMU only).
     */
    public function destroy(TotBatch $totBatch): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeManage($user);

        $code = $totBatch->batch_code;
        AuditLog::record('tot_batch_deleted', $totBatch, $totBatch->toArray(), null);
        $totBatch->delete();

        return redirect()->route('trainers.tot.index')
            ->with('status', "TOT Batch '{$code}' has been deleted.");
    }

    /**
     * Enroll one or more Field Trainers into the selected TOT batch (Super Admin / PMU only).
     */
    public function enrollTrainers(Request $request, TotBatch $totBatch): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeManage($user);

        $validated = $request->validate([
            'trainer_ids' => ['required', 'array', 'min:1'],
            'trainer_ids.*' => ['integer', 'exists:field_trainers,id'],
        ], [
            'trainer_ids.required' => 'Please select at least one field trainer to enroll into this TOT batch.',
            'trainer_ids.min' => 'Please select at least one field trainer to enroll into this TOT batch.',
        ]);

        $addedIds = [];

        DB::transaction(function () use ($validated, $totBatch, &$addedIds) {
            $existingIds = TotBatchTrainer::where('tot_batch_id', $totBatch->id)
                ->pluck('field_trainer_id')
                ->toArray();

            foreach (array_unique($validated['trainer_ids']) as $trainerId) {
                $trainerId = (int) $trainerId;
                if (!in_array($trainerId, $existingIds, true)) {
                    TotBatchTrainer::create([
                        'tot_batch_id' => $totBatch->id,
                        'field_trainer_id' => $trainerId,
                        'is_present' => false,
                    ]);
                    $addedIds[] = $trainerId;
                }
            }
        });

        $addedCount = count($addedIds);

        if ($addedCount === 0) {
            return back()->with('status', 'All selected trainers are already enrolled in this TOT batch.');
        }

        AuditLog::record('tot_trainers_enrolled', $totBatch, null, [
            'added_count' => $addedCount,
            'trainer_ids' => $addedIds,
        ]);

        return back()->with('status', "{$addedCount} field trainer(s) added to TOT Batch '{$totBatch->batch_code}'.");
    }

    /**
     * Remove a Field Trainer from the selected TOT batch (Super Admin / PMU only).
     */
    public function removeTrainer(TotBatch $totBatch, FieldTrainer $fieldTrainer): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeManage($user);

        $enrollment = TotBatchTrainer::where('tot_batch_id', $totBatch->id)
            ->where('field_trainer_id', $fieldTrainer->id)
            ->first();

        if (!$enrollment) {
            abort(404, 'Trainer is not enrolled in this TOT batch.');
        }

        AuditLog::record('tot_trainer_removed', $totBatch, $enrollment->toArray(), null);
        $enrollment->delete();

        return back()->with('status', "Removed '{$fieldTrainer->name}' from TOT Batch '{$totBatch->batch_code}'.");
    }

    /**
     * Save 1-day digital attendance for all enrolled trainers in the TOT batch (Super Admin / PMU only).
     */
    public function saveAttendance(Request $request, TotBatch $totBatch): RedirectResponse
    {
        $user = Auth::user();
        $this->authorizeManage($user);

        $validated = $request->validate([
            'attendance' => ['nullable', 'array'],
            'attendance.*.is_present' => ['nullable', 'boolean'],
            'attendance.*.remarks' => ['nullable', 'string', 'max:500'],
            'mark_completed' => ['nullable', 'boolean'],
        ]);

        $attendanceInput = $validated['attendance'] ?? [];
        $presentCount = 0;
        $totalCount = 0;

        DB::transaction(function () use ($totBatch, $attendanceInput, $validated, $user, &$presentCount, &$totalCount) {
            $enrollments = $totBatch->enrollments()->get();
            $totalCount = $enrollments->count();
            $now = now();

            foreach ($enrollments as $enrollment) {
                $row = $attendanceInput[$enrollment->id] ?? [];
                $isPresent = !empty($row['is_present']);
                $remarks = isset($row['remarks']) && trim((string) $row['remarks']) !== ''
                    ? trim((string) $row['remarks'])
                    : null;

                $enrollment->update([
                    'is_present' => $isPresent,
                    'remarks' => $remarks,
                    'marked_by' => $user->id,
                    'marked_at' => $now,
                ]);

                if ($isPresent) {
                    $presentCount++;
                }
            }

            if (!empty($validated['mark_completed']) && $totBatch->status !== TotBatch::STATUS_COMPLETED) {
                $totBatch->update(['status' => TotBatch::STATUS_COMPLETED]);
            }
        });

        AuditLog::record('tot_attendance_saved', $totBatch, null, [
            'present_count' => $presentCount,
            'total_enrolled' => $totalCount,
        ]);

        return back()->with('status', "1-Day TOT attendance saved ({$presentCount} Present out of {$totalCount} Enrolled).");
    }

    /**
     * Scope TOT batch queries for district coordinators vs statewide roles.
     */
    protected function scopeBatchesForUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('district-coordinator')) {
            $assignedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            $query->where(function ($q) use ($assignedDistrictIds) {
                $q->whereIn('district_id', $assignedDistrictIds)
                  ->orWhereHas('trainers', fn($t) => $t->whereIn('field_trainers.district_id', $assignedDistrictIds));
            });
        }

        return $query;
    }

    /**
     * Normalize validated TOT batch attributes for create and update operations.
     */
    protected function normalizeBatchAttributes(array $validated, string $defaultStatus): array
    {
        return [
            'batch_code' => strtoupper(trim($validated['batch_code'])),
            'title' => !empty($validated['title']) ? trim($validated['title']) : null,
            'training_date' => $validated['training_date'],
            'district_id' => !empty($validated['district_id']) ? $validated['district_id'] : null,
            'status' => $validated['status'] ?? $defaultStatus,
            'remarks' => !empty($validated['remarks']) ? trim($validated['remarks']) : null,
        ];
    }

    /**
     * Resolve an existing TOT venue or register a newly created TOT venue inline.
     *
     * @return array{tot_venue_id: int, venue_name: string, venue_address: ?string}
     */
    protected function resolveTotVenue(array $validated, User $user): array
    {
        $useExisting = ($validated['venue_mode'] ?? null) === 'existing'
            || (($validated['venue_mode'] ?? null) === null && !empty($validated['tot_venue_id']) && empty($validated['venue_name']));

        if ($useExisting) {
            $venue = TotVenue::findOrFail($validated['tot_venue_id']);

            return [
                'tot_venue_id' => $venue->id,
                'venue_name' => $venue->name,
                'venue_address' => $venue->address,
            ];
        }

        $name = trim((string) ($validated['venue_name'] ?? ''));
        $address = !empty($validated['venue_address']) ? trim((string) $validated['venue_address']) : null;
        $districtId = !empty($validated['district_id']) ? (int) $validated['district_id'] : null;

        // Reuse matching TOT venue (case-insensitive) to avoid duplicate dropdown clutter
        $existingVenue = TotVenue::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->whereRaw("LOWER(COALESCE(address, '')) = ?", [strtolower((string) ($address ?? ''))])
            ->first();

        if ($existingVenue) {
            $venue = $existingVenue;
        } else {
            $venue = TotVenue::create([
                'name' => $name,
                'address' => $address,
                'district_id' => $districtId,
                'is_active' => true,
                'created_by' => $user->id,
            ]);
        }

        return [
            'tot_venue_id' => $venue->id,
            'venue_name' => $venue->name,
            'venue_address' => $venue->address,
        ];
    }

    /**
     * Validate TOT batch creation and update payloads.
     */
    protected function validateBatchPayload(Request $request, ?int $ignoreBatchId = null): array
    {
        $venueMode = $request->input('venue_mode');
        $useExisting = $venueMode === 'existing'
            || ($venueMode === null && $request->filled('tot_venue_id') && !$request->filled('venue_name'));

        return $request->validate([
            'batch_code' => [
                'required',
                'string',
                'max:60',
                Rule::unique('tot_batches', 'batch_code')->ignore($ignoreBatchId),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'training_date' => ['required', 'date'],
            'venue_mode' => ['nullable', Rule::in(['existing', 'new'])],
            'tot_venue_id' => [Rule::requiredIf($useExisting), 'nullable', 'exists:tot_venues,id'],
            'venue_name' => [Rule::requiredIf(!$useExisting), 'nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'status' => ['nullable', Rule::in([TotBatch::STATUS_SCHEDULED, TotBatch::STATUS_COMPLETED])],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ], [
            'batch_code.required' => 'TOT Batch Code is mandatory.',
            'batch_code.unique' => 'A TOT batch with this code already exists.',
            'training_date.required' => '1-Day Training Date is mandatory.',
            'tot_venue_id.required' => 'Please select an existing TOT venue.',
            'venue_name.required' => 'Venue of TOT is mandatory.',
        ]);
    }

    /**
     * Generate a clean sequential batch code suggestion (e.g., TOT-2026-01).
     */
    protected function generateNextBatchCode(): string
    {
        $year = now()->format('Y');
        $prefix = "TOT-{$year}-";
        $count = TotBatch::where('batch_code', 'like', "{$prefix}%")->count() + 1;

        $candidate = $prefix . str_pad((string) $count, 2, '0', STR_PAD_LEFT);
        while (TotBatch::where('batch_code', $candidate)->exists()) {
            $count++;
            $candidate = $prefix . str_pad((string) $count, 2, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    protected function canManageTot(User $user): bool
    {
        return $user->hasRole(['super-admin', 'pmu']);
    }

    protected function authorizeView(User $user): void
    {
        if (!$user->can('trainers.view') && !$user->hasRole(['super-admin', 'pmu', 'district-coordinator', 'ocac'])) {
            abort(403, 'Unauthorized. You do not have permission to access TOT records.');
        }
    }

    protected function authorizeViewBatch(User $user, TotBatch $totBatch): void
    {
        if ($user->hasRole(['super-admin', 'pmu', 'ocac'])) {
            return;
        }

        if ($user->hasRole('district-coordinator')) {
            $assignedDistrictIds = $user->assignedDistricts->pluck('id')->toArray();
            $matchesDistrict = $totBatch->district_id && in_array((int) $totBatch->district_id, $assignedDistrictIds, true);
            $hasDistrictTrainers = $totBatch->trainers()->whereIn('field_trainers.district_id', $assignedDistrictIds)->exists();

            if ($matchesDistrict || $hasDistrictTrainers) {
                return;
            }
        }

        abort(403, 'Unauthorized to view this TOT batch.');
    }

    protected function authorizeManage(User $user): void
    {
        if (!$this->canManageTot($user)) {
            abort(403, 'Unauthorized. Only Super Admin and PMU can create or manage TOT batches.');
        }
    }
}
