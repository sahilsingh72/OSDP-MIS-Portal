<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\BatchParticipant;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Services\ParticipantImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantController extends Controller
{
    /**
     * Display a listing of registered participants with search & batch filtering.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Participant::class);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Participant::with(['creator', 'batches' => function ($q) use ($user) {
            $q->forUserScope($user)->with(['district', 'block', 'tier']);
        }]);

        // Status tab filtering (active vs trashed / deleted)
        $status = $request->input('status', 'active');
        if ($user->hasRole('ocac')) {
            $status = 'active';
        }

        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        }

        // Scope participants to those in user's authorized batches or created by them
        if ($user->hasRole(['district-coordinator', 'field-trainer', 'field-monitor'])) {
            $scopeUser = function ($q) use ($user) {
                $q->where(function ($sub) use ($user) {
                    $sub->whereHas('batches', function ($b) use ($user) {
                        $b->forUserScope($user);
                    })->orWhere('created_by', $user->id);
                });
            };

            $query->where($scopeUser);
            $activeCount = Participant::where($scopeUser)->count();
            $trashedCount = Participant::onlyTrashed()->where($scopeUser)->count();
        } else {
            $activeCount = Participant::count();
            $trashedCount = $user->hasRole('ocac') ? 0 : Participant::onlyTrashed()->count();
        }

        // Batches query for batch selection screen and dropdowns
        $batchQuery = TrainingBatch::forUserScope($user)
            ->with(['district', 'block', 'tier', 'venue'])
            ->withCount(['activeBatchParticipants as batch_participants_count']);

        if ($request->filled('batch_search')) {
            $bSearch = $request->input('batch_search');
            $batchQuery->where(function ($q) use ($bSearch) {
                $q->where('batch_code', 'like', "%{$bSearch}%")
                  ->orWhereHas('district', fn($d) => $d->where('name', 'like', "%{$bSearch}%"))
                  ->orWhereHas('block', fn($bl) => $bl->where('name', 'like', "%{$bSearch}%"))
                  ->orWhereHas('venue', fn($v) => $v->where('name', 'like', "%{$bSearch}%"));
            });
        }

        if ($request->filled('tier_id')) {
            $batchQuery->where('training_tier_id', $request->input('tier_id'));
        }

        $batches = (clone $batchQuery)->orderByDesc('scheduled_date')->get();

        // Selected batch detection
        $selectedBatchId = $request->input('batch_id');
        $selectedBatch = null;
        if (!empty($selectedBatchId)) {
            $selectedBatch = TrainingBatch::forUserScope($user)
                ->with(['district', 'block', 'tier', 'venue'])
                ->withCount(['activeBatchParticipants as batch_participants_count'])
                ->find($selectedBatchId);
        }

        // Determine if batch selection should be presented first
        // Shows batches first when:
        // 1. No batch is selected
        // 2. Not explicitly viewing all participants (?all_participants=1)
        // 3. Not searching participants directly (?search=...)
        // 4. Not viewing trashed participants (?status=trashed)
        $isAllView = $request->boolean('all_participants') || ($request->filled('search') && empty($selectedBatchId));
        $showBatchesFirst = empty($selectedBatch) && !$isAllView && $status !== 'trashed';

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $cleanNumeric = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanNumeric) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('office_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_masked', 'like', "%{$search}%");

                if (!empty($cleanNumeric)) {
                    $q->orWhere('phone_masked', 'like', "%{$cleanNumeric}%");
                }
            });
        }

        if ($selectedBatch) {
            $query->whereHas('batches', function ($q) use ($selectedBatch) {
                $q->where('training_batches.id', $selectedBatch->id);
            });
        } elseif ($request->filled('batch_id')) {
            $query->whereHas('batches', function ($q) use ($request) {
                $q->where('training_batches.id', $request->input('batch_id'));
            });
        }

        $participants = $query->orderByRaw('LOWER(name) ASC')->paginate(20)->withQueryString();

        $tiers = \App\Models\TrainingTier::orderBy('id')->get();

        return view('participants.index', compact(
            'participants',
            'batches',
            'selectedBatch',
            'showBatchesFirst',
            'isAllView',
            'tiers',
            'activeCount',
            'trashedCount',
            'status'
        ));
    }

    /**
     * Store a newly created single participant in the directory.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Participant::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'office_name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['required', 'email', 'max:255'],
            'batch_id' => ['required', 'exists:training_batches,id'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'department.required' => 'Department is mandatory.',
            'batch_id.required' => 'Target training batch selection is mandatory.',
            'phone.required' => 'Contact phone number is mandatory.',
            'phone.regex' => 'Contact phone number must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'email.required' => 'Email address is mandatory.',
            'email.email' => 'Please provide a valid email address.',
        ]);

        $batch = TrainingBatch::findOrFail($validated['batch_id']);
        $this->authorize('update', $batch);

        $name = trim($validated['name']);
        $designation = trim($validated['designation']);
        $officeName = trim($validated['office_name']);
        $department = trim($validated['department']);
        $cleanEmail = strtolower(trim($validated['email']));
        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        $phoneExists = Participant::withTrashed()
            ->where('phone_masked', $cleanPhone)
            ->exists();

        $emailExists = Participant::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$cleanEmail])
            ->exists();

        if ($phoneExists || $emailExists) {
            $duplicateErrors = [];
            if ($phoneExists) {
                $duplicateErrors['phone'] = 'Participant already existed with same phone num.';
            }
            if ($emailExists) {
                $duplicateErrors['email'] = 'Participant already existed with same email.';
            }
            return back()->withInput()->withErrors($duplicateErrors);
        }

        $enrollmentStatus = '';

        $participant = DB::transaction(function () use (
            $name,
            $designation,
            $officeName,
            $department,
            $cleanPhone,
            $cleanEmail,
            $isActive,
            $batch,
            &$enrollmentStatus
        ) {
            $participant = Participant::create([
                'name' => $name,
                'designation' => $designation,
                'department' => $department,
                'office_name' => $officeName,
                'phone_masked' => $cleanPhone,
                'email' => $cleanEmail,
                'is_active' => $isActive,
                'created_by' => Auth::id(),
            ]);

            AuditLog::record('participant_created', $participant, null, $participant->toArray());
            $enrollmentStatus = "Participant '{$participant->name}' added successfully";

            if ($batch) {
                $nextSerial = $batch->batchParticipants()->count() + 1;

                $bp = BatchParticipant::create([
                    'training_batch_id' => $batch->id,
                    'participant_id' => $participant->id,
                    'serial_number' => $nextSerial,
                ]);

                AttendanceRecord::create([
                    'training_batch_id' => $batch->id,
                    'batch_participant_id' => $bp->id,
                    'morning_present' => false,
                    'afternoon_present' => false,
                    'remarks' => null,
                    'marked_by' => null,
                ]);

                $batch->recalculateCounts();
                $enrollmentStatus .= " and enrolled into batch {$batch->batch_code}";
            }

            return $participant;
        });

        return back()->with('status', $enrollmentStatus . '.');
    }

    /**
     * Update participant details.
     */
    public function update(Request $request, Participant $participant): RedirectResponse
    {
        $this->authorize('update', $participant);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'office_name' => ['required', 'string', 'max:255'],
            'department' => $request->has('department') ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'batch_id' => ['nullable', 'exists:training_batches,id'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'department.required' => 'Department is mandatory.',
            'phone.regex' => 'Contact phone number must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'email.email' => 'Please provide a valid email address.',
        ]);

        $newBatch = null;
        if (!empty($validated['batch_id'])) {
            $newBatch = TrainingBatch::findOrFail($validated['batch_id']);
            $this->authorize('update', $newBatch);
        }

        $cleanPhone = !empty($validated['phone']) ? preg_replace('/[^0-9]/', '', $validated['phone']) : $participant->phone_masked;
        $cleanEmail = !empty($validated['email']) ? strtolower(trim($validated['email'])) : $participant->email;

        $duplicateErrors = [];
        if (!empty($cleanPhone)) {
            $phoneExists = Participant::withTrashed()
                ->where('id', '!=', $participant->id)
                ->where('phone_masked', $cleanPhone)
                ->exists();
            if ($phoneExists) {
                $duplicateErrors['phone'] = 'Participant already existed with same phone num.';
            }
        }

        if (!empty($cleanEmail)) {
            $emailExists = Participant::withTrashed()
                ->where('id', '!=', $participant->id)
                ->whereRaw('LOWER(email) = ?', [$cleanEmail])
                ->exists();
            if ($emailExists) {
                $duplicateErrors['email'] = 'Participant already existed with same email.';
            }
        }

        if (!empty($duplicateErrors)) {
            return back()->withInput()->withErrors($duplicateErrors);
        }

        $oldData = $participant->toArray();

        DB::transaction(function () use ($participant, $validated, $cleanPhone, $cleanEmail, $request, $newBatch, $oldData) {
            $participant->update([
                'name' => $validated['name'],
                'designation' => $validated['designation'],
                'office_name' => $validated['office_name'],
                'department' => $request->has('department') ? trim($validated['department']) : $participant->department,
                'phone_masked' => $cleanPhone,
                'email' => $cleanEmail,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $participant->is_active,
            ]);

            // Handle Batch Allocation Change ONLY if batch_id field is present in the request
            if ($request->has('batch_id')) {
                $newBatchId = $newBatch?->id;
                $currentBp = BatchParticipant::where('participant_id', $participant->id)->first();
                $currentBatchId = $currentBp?->training_batch_id;

                if ($newBatchId !== $currentBatchId) {
                    // If enrolled in an old batch, remove and recalculate
                    if ($currentBp) {
                        $oldBatch = TrainingBatch::find($currentBatchId);
                        $currentBp->delete();
                        $oldBatch?->recalculateCounts();
                    }

                    // If enrolled in a new batch, create enrollment and initialize attendance
                    if ($newBatch) {
                        $nextSerial = $newBatch->batchParticipants()->count() + 1;
                        $newBp = BatchParticipant::create([
                            'training_batch_id' => $newBatch->id,
                            'participant_id' => $participant->id,
                            'serial_number' => $nextSerial,
                        ]);

                        AttendanceRecord::create([
                            'training_batch_id' => $newBatch->id,
                            'batch_participant_id' => $newBp->id,
                            'morning_present' => false,
                            'afternoon_present' => false,
                            'remarks' => null,
                            'marked_by' => null,
                        ]);

                        $newBatch->recalculateCounts();
                    }
                }
            }

            AuditLog::record('participant_updated', $participant, $oldData, $participant->getChanges());
        });

        return back()->with('status', "Participant '{$participant->name}' updated successfully.");
    }

    /**
     * Soft delete a participant.
     */
    public function destroy(Participant $participant): RedirectResponse
    {
        $this->authorize('delete', $participant);

        $name = $participant->name;
        $participant->delete();

        // Recalculate batch counts for any batches this participant was enrolled in
        $batches = TrainingBatch::whereHas('batchParticipants', function ($q) use ($participant) {
            $q->where('participant_id', $participant->id);
        })->get();

        foreach ($batches as $batch) {
            $batch->recalculateCounts();
        }

        AuditLog::record('participant_deleted', $participant, null, ['soft_deleted' => true]);

        return back()->with('status', "Participant '{$name}' was deleted (soft-deleted). It can be recovered anytime from the Trashed tab.");
    }

    /**
     * Restore a soft-deleted participant.
     */
    public function restore(string|int $id): RedirectResponse
    {
        $participant = Participant::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $participant);

        $participant->restore();

        // Recalculate batch counts for any batches this participant is enrolled in
        $batches = TrainingBatch::whereHas('batchParticipants', function ($q) use ($participant) {
            $q->where('participant_id', $participant->id);
        })->get();

        foreach ($batches as $batch) {
            $batch->recalculateCounts();
        }

        AuditLog::record('participant_restored', $participant, null, ['restored' => true]);

        return back()->with('status', "Participant '{$participant->name}' has been restored successfully.");
    }

    /**
     * Bulk upload participants via Excel/CSV.
     */
    public function import(Request $request, ParticipantImportService $importService): RedirectResponse
    {
        $this->authorize('create', Participant::class);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
            'batch_id' => ['required', 'exists:training_batches,id'],
        ], [
            'batch_id.required' => 'Target training batch selection is mandatory.',
            'file.mimes' => 'The uploaded file must be an Excel spreadsheet (.xlsx, .xls) or CSV file.',
            'file.max' => 'The uploaded file must not exceed 10 megabytes.',
        ]);

        $batch = TrainingBatch::findOrFail($validated['batch_id']);
        $this->authorize('update', $batch);

        try {
            $result = $importService->importFromExcel($request->file('file'), $batch, Auth::user());

            $msg = "Successfully processed {$result['total_rows']} rows: {$result['imported']} participants registered";
            if ($batch) {
                $msg .= " and enrolled into batch {$batch->batch_code}";
            }
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} duplicates/skipped)";
            }
            $msg .= '.';

            $redirect = back()->with('status', $msg);
            if (!empty($result['errors'])) {
                $redirect->with('import_errors', $result['errors']);
            }

            return $redirect;
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Download the official Excel (.xlsx) template.
     */
    public function downloadTemplate(Request $request, ParticipantImportService $importService): StreamedResponse
    {
        $batch = null;
        if ($request->filled('batch_id')) {
            $batch = TrainingBatch::find($request->input('batch_id'));
        }

        return $importService->downloadTemplate($batch);
    }
}
