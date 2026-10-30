<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\BatchParticipant;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Show Form 2 Attendance transcription sheet for a batch.
     */
    public function show(TrainingBatch $batch): View
    {
        $this->authorize('view', $batch);

        $batch->recalculateCounts();

        $batch->load([
            'tier',
            'district',
            'block',
            'venue',
            'trainers',
            'batchParticipants' => function ($q) {
                $q->join('participants', 'batch_participants.participant_id', '=', 'participants.id')
                    ->whereNull('participants.deleted_at')
                    ->orderByRaw('LOWER(participants.name) ASC')
                    ->select('batch_participants.*');
            },
            'batchParticipants.participant',
            'batchParticipants.attendance',
        ]);

        return view('attendance.show', compact('batch'));
    }

    /**
     * Add new participant and enroll into batch.
     */
    public function addParticipant(Request $request, TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('manageParticipants', $batch);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'office_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'email' => ['required', 'email', 'max:255'],
        ], [
            'department.required' => 'Department is mandatory.',
            'phone.required' => 'Contact phone number is mandatory.',
            'phone.regex' => 'Contact phone number must be a valid 10-digit mobile number starting with 6, 7, 8, or 9.',
            'email.required' => 'Email address is mandatory.',
            'email.email' => 'Please provide a valid email address.',
        ]);

        $name = trim($validated['name']);
        $officeName = trim($validated['office_name']);
        $designation = trim($validated['designation']);
        $department = trim($validated['department']);
        $cleanEmail = strtolower(trim($validated['email']));
        $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);

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

        DB::transaction(function () use ($name, $designation, $department, $officeName, $cleanPhone, $cleanEmail, $batch) {
            $participant = Participant::create([
                'name' => $name,
                'designation' => $designation,
                'department' => $department,
                'office_name' => $officeName,
                'phone_masked' => $cleanPhone,
                'email' => $cleanEmail,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            // Check if already enrolled in this batch
            $bp = BatchParticipant::where('training_batch_id', $batch->id)
                ->where('participant_id', $participant->id)
                ->first();

            if (!$bp) {
                $nextSerial = $batch->batchParticipants()->count() + 1;

                $bp = BatchParticipant::create([
                    'training_batch_id' => $batch->id,
                    'participant_id' => $participant->id,
                    'serial_number' => $nextSerial,
                ]);

                // Attendance is NOT marked present automatically; it must be recorded manually on Form 2
                AttendanceRecord::create([
                    'training_batch_id' => $batch->id,
                    'batch_participant_id' => $bp->id,
                    'morning_present' => false,
                    'afternoon_present' => false,
                    'marked_by' => null,
                ]);

                // Update registered participant count
                $this->recalculateCounts($batch);
            }
        });

        return back()->with('status', 'Participant details added and enrolled into batch successfully. Please mark attendance manually on Form 2 below.');
    }

    /**
     * Bulk upload participants into this batch from Excel.
     */
    public function bulkUpload(Request $request, TrainingBatch $batch, \App\Services\ParticipantImportService $importService): RedirectResponse
    {
        $this->authorize('manageParticipants', $batch);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ], [
            'file.mimes' => 'The uploaded file must be an Excel spreadsheet (.xlsx, .xls) or CSV file.',
            'file.max' => 'The uploaded file must not exceed 10 megabytes.',
        ]);

        try {
            $result = $importService->importFromExcel($request->file('file'), $batch, Auth::user());

            $msg = "Successfully imported {$result['imported']} participants into batch {$batch->batch_code}";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} duplicates/skipped)";
            }
            $msg .= ". Total registered: {$batch->fresh()->registered_participants}. Participant details saved; please mark attendance manually below.";

            $redirect = back()->with('status', $msg);
            if (!empty($result['errors'])) {
                $redirect->with('import_errors', $result['errors']);
            }

            return $redirect;
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Excel upload failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Download Excel template for this batch.
     */
    public function downloadTemplate(TrainingBatch $batch, \App\Services\ParticipantImportService $importService): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('view', $batch);

        return $importService->downloadTemplate($batch);
    }

    /**
     * Save Form 2 Attendance transcription.
     */
    public function update(Request $request, TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('manageAttendance', $batch);

        $validated = $request->validate([
            'attendance' => ['nullable', 'array'],
            'attendance.*.batch_participant_id' => ['required', 'exists:batch_participants,id'],
            'attendance.*.morning_present' => ['nullable'],
            'attendance.*.afternoon_present' => ['nullable'],
            'attendance.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $batch) {
            if (!empty($validated['attendance'])) {
                foreach ($validated['attendance'] as $entry) {
                    AttendanceRecord::updateOrCreate(
                        [
                            'training_batch_id' => $batch->id,
                            'batch_participant_id' => $entry['batch_participant_id'],
                        ],
                        [
                            'morning_present' => isset($entry['morning_present']),
                            'afternoon_present' => isset($entry['afternoon_present']),
                            'remarks' => $entry['remarks'] ?? null,
                            'marked_by' => Auth::id(),
                        ]
                    );
                }
            }

            $batch->recalculateCounts();

            // If batch was scheduled, transition to in_progress or awaiting_evidence
            if ($batch->status === TrainingBatch::STATUS_SCHEDULED) {
                $batch->update(['status' => TrainingBatch::STATUS_AWAITING_EVIDENCE]);
            }

            AuditLog::record('attendance_saved', $batch, null, [
                'registered' => $batch->registered_participants,
                'present' => $batch->present_participants,
            ]);
        });

        return back()->with('status', 'Attendance records saved successfully.');
    }

    /**
     * Recalculate present and registered participant counts.
     */
    public function recalculateCounts(TrainingBatch $batch): void
    {
        $batch->recalculateCounts();
    }
}
