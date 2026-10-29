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

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('office_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('batch_id')) {
            $query->whereHas('batches', function ($q) use ($request) {
                $q->where('training_batches.id', $request->input('batch_id'));
            });
        }

        $participants = $query->orderByRaw('LOWER(name) ASC')->paginate(20)->withQueryString();

        $batches = TrainingBatch::forUserScope($user)
            ->orderByDesc('scheduled_date')
            ->limit(100)
            ->get();

        return view('participants.index', compact('participants', 'batches', 'activeCount', 'trashedCount', 'status'));
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
            'department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'batch_id' => ['nullable', 'exists:training_batches,id'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'phone.regex' => 'Contact phone number must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
        ]);

        $batch = null;
        if (!empty($validated['batch_id'])) {
            $batch = TrainingBatch::findOrFail($validated['batch_id']);
            $this->authorize('update', $batch);
        }

        $name = trim($validated['name']);
        $designation = trim($validated['designation']);
        $officeName = trim($validated['office_name']);
        $department = !empty($validated['department']) ? trim($validated['department']) : null;
        $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : null;
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : true;

        $phone = !empty($validated['phone']) ? trim($validated['phone']) : null;
        $cleanPhone = null;
        if (!empty($phone)) {
            $digits = preg_replace('/[^0-9+]/', '', $phone);
            $cleanPhone = !empty($digits) ? $digits : $phone;
        }

        $enrollmentStatus = '';

        $participant = DB::transaction(function () use (
            $name,
            $designation,
            $officeName,
            $department,
            $cleanPhone,
            $email,
            $isActive,
            $batch,
            &$enrollmentStatus
        ) {
            $participant = Participant::withTrashed()
                ->where('name', $name)
                ->where('office_name', $officeName)
                ->first();

            if (!$participant) {
                $participant = Participant::create([
                    'name' => $name,
                    'designation' => $designation,
                    'department' => $department,
                    'office_name' => $officeName,
                    'phone_masked' => $cleanPhone,
                    'email' => $email,
                    'is_active' => $isActive,
                    'created_by' => Auth::id(),
                ]);

                AuditLog::record('participant_created', $participant, null, $participant->toArray());
                $enrollmentStatus = "Participant '{$participant->name}' added successfully";
            } else {
                if ($participant->trashed()) {
                    $participant->restore();
                }

                $oldData = $participant->toArray();
                $participant->update([
                    'designation' => $designation ?: $participant->designation,
                    'department' => $department ?: $participant->department,
                    'phone_masked' => $cleanPhone ?: $participant->phone_masked,
                    'email' => $email ?: $participant->email,
                    'is_active' => $isActive,
                ]);

                AuditLog::record('participant_updated', $participant, $oldData, $participant->getChanges());
                $enrollmentStatus = "Participant '{$participant->name}' record updated";
            }

            if ($batch) {
                $alreadyEnrolled = BatchParticipant::where('training_batch_id', $batch->id)
                    ->where('participant_id', $participant->id)
                    ->first();

                if (!$alreadyEnrolled) {
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
                } else {
                    $enrollmentStatus .= " (already enrolled in batch {$batch->batch_code})";
                }
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
            'department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'batch_id' => ['nullable', 'exists:training_batches,id'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'phone.regex' => 'Contact phone number must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
        ]);

        $newBatch = null;
        if (!empty($validated['batch_id'])) {
            $newBatch = TrainingBatch::findOrFail($validated['batch_id']);
            $this->authorize('update', $newBatch);
        }

        $phone = !empty($validated['phone']) ? trim($validated['phone']) : null;
        $cleanPhone = $participant->phone_masked;
        if (!empty($phone) && !str_contains($phone, '*')) {
            $digits = preg_replace('/[^0-9+]/', '', $phone);
            $cleanPhone = !empty($digits) ? $digits : $phone;
        } elseif (empty($phone)) {
            $cleanPhone = null;
        }

        $oldData = $participant->toArray();

        DB::transaction(function () use ($participant, $validated, $cleanPhone, $request, $newBatch, $oldData) {
            $participant->update([
                'name' => $validated['name'],
                'designation' => $validated['designation'],
                'office_name' => $validated['office_name'],
                'department' => $validated['department'] ?? null,
                'phone_masked' => $cleanPhone,
                'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
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
            'batch_id' => ['nullable', 'exists:training_batches,id'],
        ], [
            'file.mimes' => 'The uploaded file must be an Excel spreadsheet (.xlsx, .xls) or CSV file.',
            'file.max' => 'The uploaded file must not exceed 10 megabytes.',
        ]);

        $batch = null;
        if (!empty($validated['batch_id'])) {
            $batch = TrainingBatch::findOrFail($validated['batch_id']);
            $this->authorize('update', $batch);
        }

        try {
            $result = $importService->importFromExcel($request->file('file'), $batch, Auth::user());

            $msg = "Successfully processed {$result['total_rows']} rows: {$result['imported']} participants registered";
            if ($batch) {
                $msg .= " and enrolled into batch {$batch->batch_code}";
            }
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} duplicates/empty skipped)";
            }
            $msg .= '.';

            return back()->with('status', $msg);
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
