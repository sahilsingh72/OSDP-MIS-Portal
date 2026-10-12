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

        $batch->load([
            'tier',
            'district',
            'block',
            'venue',
            'trainers',
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
        $this->authorize('update', $batch);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'office_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:15'],
        ]);

        DB::transaction(function () use ($validated, $batch) {
            $participant = Participant::create([
                'name' => $validated['name'],
                'designation' => $validated['designation'],
                'department' => $validated['department'] ?? null,
                'office_name' => $validated['office_name'],
                'phone_masked' => !empty($validated['phone']) ? substr($validated['phone'], 0, 2) . '******' . substr($validated['phone'], -2) : null,
            ]);

            $nextSerial = $batch->batchParticipants()->count() + 1;

            $bp = BatchParticipant::create([
                'training_batch_id' => $batch->id,
                'participant_id' => $participant->id,
                'serial_number' => $nextSerial,
            ]);

            AttendanceRecord::create([
                'training_batch_id' => $batch->id,
                'batch_participant_id' => $bp->id,
                'morning_present' => true,
                'afternoon_present' => true,
                'marked_by' => Auth::id(),
            ]);

            // Update registered participant count
            $batch->increment('registered_participants');
            $this->recalculateCounts($batch);
        });

        return back()->with('status', 'Participant added and enrolled into batch successfully.');
    }

    /**
     * Save Form 2 Attendance transcription.
     */
    public function update(Request $request, TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('update', $batch);

        $validated = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*.batch_participant_id' => ['required', 'exists:batch_participants,id'],
            'attendance.*.morning_present' => ['nullable'],
            'attendance.*.afternoon_present' => ['nullable'],
            'attendance.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $batch) {
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

            $this->recalculateCounts($batch);

            // If batch was scheduled, transition to in_progress or awaiting_evidence
            if ($batch->status === TrainingBatch::STATUS_SCHEDULED) {
                $batch->update(['status' => TrainingBatch::STATUS_AWAITING_EVIDENCE]);
            }

            AuditLog::record('attendance_saved', $batch, null, [
                'registered' => $batch->registered_participants,
                'present' => $batch->present_participants,
            ]);
        });

        return back()->with('status', 'Form 2 attendance records saved successfully.');
    }

    /**
     * Recalculate present and registered participant counts.
     */
    private function recalculateCounts(TrainingBatch $batch): void
    {
        $registered = $batch->batchParticipants()->count();
        // Participant is present if either morning or afternoon was marked present
        $present = $batch->attendanceRecords()
            ->where(function ($q) {
                $q->where('morning_present', true)->orWhere('afternoon_present', true);
            })
            ->count();

        $batch->update([
            'registered_participants' => $registered,
            'present_participants' => $present,
        ]);
    }
}
