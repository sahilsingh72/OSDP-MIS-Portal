<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BatchParticipant;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantAttendanceSinglePresentTest extends TestCase
{
    /**
     * Test attendance sheet UI shows single Present column instead of Morning and Afternoon signs.
     */
    public function test_attendance_sheet_displays_single_present_column_without_morning_afternoon_signs(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $batch = TrainingBatch::first();

        $response = $this->actingAs($admin)->get("/batches/{$batch->id}/attendance");
        $response->assertOk();

        // Must display single Present header
        $response->assertSee('Present');

        // Must NOT show separate Morning Sign and Afternoon Sign headers
        $response->assertDontSee('Morning Sign');
        $response->assertDontSee('Afternoon Sign');
    }

    /**
     * Test recording attendance via single is_present marks participant present and updates batch count.
     */
    public function test_recording_attendance_with_single_is_present_marks_participant_present(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $batch = TrainingBatch::first();

        // Create a new participant enrolled in this batch
        $participant = Participant::create([
            'name' => 'Single Present Trainee',
            'designation' => 'Panchayat Executive Officer',
            'office_name' => 'Khurda GP Office',
            'phone' => '9876500001',
            'email' => 'single.present@test.gov.in',
            'is_active' => true,
        ]);

        $bp = BatchParticipant::create([
            'training_batch_id' => $batch->id,
            'participant_id' => $participant->id,
            'serial_number' => 999,
        ]);

        $att = AttendanceRecord::create([
            'training_batch_id' => $batch->id,
            'batch_participant_id' => $bp->id,
            'is_present' => false,
            'morning_present' => false,
            'afternoon_present' => false,
        ]);

        $batch->recalculateCounts();
        $initialPresent = $batch->fresh()->present_participants;

        // 1. Mark present using the single is_present field
        $postResponse = $this->actingAs($admin)->post("/batches/{$batch->id}/attendance", [
            'attendance' => [
                [
                    'batch_participant_id' => $bp->id,
                    'is_present' => '1',
                    'remarks' => 'Single present mark test',
                ],
            ],
        ]);
        $postResponse->assertRedirect();

        $att->refresh();
        $this->assertTrue((bool) $att->is_present);
        $this->assertTrue((bool) $att->morning_present);
        $this->assertTrue((bool) $att->afternoon_present);
        $this->assertEquals('Single present mark test', $att->remarks);
        $this->assertEquals($initialPresent + 1, $batch->fresh()->present_participants);

        // 2. Unmark present (absent)
        $unmarkResponse = $this->actingAs($admin)->post("/batches/{$batch->id}/attendance", [
            'attendance' => [
                [
                    'batch_participant_id' => $bp->id,
                    // is_present not set = absent
                    'remarks' => 'Absent on training day',
                ],
            ],
        ]);
        $unmarkResponse->assertRedirect();

        $att->refresh();
        $this->assertFalse((bool) $att->is_present);
        $this->assertFalse((bool) $att->morning_present);
        $this->assertFalse((bool) $att->afternoon_present);
        $this->assertEquals($initialPresent, $batch->fresh()->present_participants);
    }
}
