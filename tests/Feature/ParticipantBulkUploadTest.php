<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ParticipantBulkUploadTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Create a realistic test Excel file with participant details only.
     */
    protected function createTestExcelFile(array $rows, ?array $customHeader = null): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $header = $customHeader ?? [
            'Participant Name',
            'Designation',
            'Office / Gram Panchayat',
            'Department',
            'Mobile No',
            'Email ID',
        ];

        $sheet->fromArray([$header], null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_excel_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile(
            $tempPath,
            'participants.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * Test downloading official Excel template.
     */
    public function test_user_can_download_excel_template(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $batch = TrainingBatch::first();

        // Download general template
        $response = $this->actingAs($admin)->get('/participants/template');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Download batch specific template
        $batchResponse = $this->actingAs($admin)->get("/batches/{$batch->id}/participants/template");
        $batchResponse->assertStatus(200);
        $batchResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Test bulk uploading participants only adds participant details and does NOT mark attendance.
     * Attendance must be recorded manually.
     */
    public function test_bulk_upload_only_adds_participant_details_not_attendance(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $batch = TrainingBatch::first();
        $batch->recalculateCounts();
        $initialRegistered = $batch->registered_participants;
        $initialPresent = $batch->present_participants;

        $uid = uniqid();
        $name1 = "Participant One {$uid}";
        $name2 = "Participant Two {$uid}";

        // Even if an Excel file has extra attendance columns with "Yes", bulk upload must ignore them
        $customHeader = [
            'Participant Name',
            'Designation',
            'Office / Gram Panchayat',
            'Department',
            'Mobile No',
            'Email ID',
            'Morning Present',
            'Afternoon Present',
            'Remarks',
        ];

        $rows = [
            [
                $name1,
                'Panchayat Executive Officer',
                'Janla GP',
                'PR & DW Department',
                '9861055555',
                "p1_{$uid}@gmail.com",
                'Yes', // Should be ignored
                'Yes', // Should be ignored
                'Attempted Remark', // Should be ignored
            ],
            [
                $name2,
                'Gram Rozgar Sevak',
                'Tamando GP',
                'PR & DW Department',
                '9437055555',
                "p2_{$uid}@gmail.com",
                'Present', // Should be ignored
                'Present', // Should be ignored
                'Attempted Remark 2', // Should be ignored
            ],
        ];

        $file = $this->createTestExcelFile($rows, $customHeader);

        $response = $this->actingAs($admin)->post("/batches/{$batch->id}/participants/bulk-upload", [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // Verify participant master records were created
        $p1 = Participant::where('name', $name1)->first();
        $this->assertNotNull($p1);
        $this->assertEquals('Panchayat Executive Officer', $p1->designation);
        $this->assertEquals('Janla GP', $p1->office_name);
        $this->assertEquals('98******55', $p1->phone_masked);

        $p2 = Participant::where('name', $name2)->first();
        $this->assertNotNull($p2);

        // Verify enrolled into batch
        $batch->refresh();
        $this->assertEquals($initialRegistered + 2, $batch->registered_participants);

        // Verify present count did NOT increase (attendance must NOT be marked by bulk upload)
        $this->assertEquals($initialPresent, $batch->present_participants);

        // Verify attendance records for both participants are strictly unmarked (false/null)
        $bp1 = $batch->batchParticipants()->where('participant_id', $p1->id)->first();
        $this->assertNotNull($bp1);
        $att1 = $bp1->attendanceRecord;
        $this->assertNotNull($att1);
        $this->assertFalse((bool) $att1->morning_present, 'Morning attendance must be false after bulk upload');
        $this->assertFalse((bool) $att1->afternoon_present, 'Afternoon attendance must be false after bulk upload');
        $this->assertNull($att1->remarks, 'Remarks must be null after bulk upload');
        $this->assertNull($att1->marked_by, 'Marked by must be null after bulk upload');

        $bp2 = $batch->batchParticipants()->where('participant_id', $p2->id)->first();
        $this->assertNotNull($bp2);
        $att2 = $bp2->attendanceRecord;
        $this->assertNotNull($att2);
        $this->assertFalse((bool) $att2->morning_present, 'Morning attendance must be false after bulk upload');
        $this->assertFalse((bool) $att2->afternoon_present, 'Afternoon attendance must be false after bulk upload');

        // Now verify attendance can be recorded manually via Form 2 update
        $attendanceData = [
            'attendance' => [
                [
                    'batch_participant_id' => $bp1->id,
                    'morning_present' => '1',
                    'afternoon_present' => '1',
                    'remarks' => 'Verified on physical Form 2',
                ],
            ],
        ];

        $manualResponse = $this->actingAs($admin)->post("/batches/{$batch->id}/attendance", $attendanceData);
        $manualResponse->assertRedirect();

        $att1->refresh();
        $this->assertTrue((bool) $att1->morning_present);
        $this->assertTrue((bool) $att1->afternoon_present);
        $this->assertEquals('Verified on physical Form 2', $att1->remarks);
        $this->assertEquals($admin->id, $att1->marked_by);
    }

    /**
     * Test bulk uploading participants from general Participants page with optional batch.
     */
    public function test_import_participants_from_general_participants_page(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $uid = uniqid();
        $name = "Sarat Chandra Das {$uid}";

        $rows = [
            [
                $name,
                'Block Project Coordinator',
                'Bhubaneswar Block Office',
                'P&C Department',
                '9938066666',
                "sarat_{$uid}@gov.in",
            ],
        ];

        $file = $this->createTestExcelFile($rows);

        $response = $this->actingAs($admin)->post('/participants/import', [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $p = Participant::where('name', $name)->first();
        $this->assertNotNull($p);
        $this->assertEquals('Bhubaneswar Block Office', $p->office_name);
    }

    /**
     * Test uploading non-spreadsheet file is rejected with validation error.
     */
    public function test_invalid_file_type_is_rejected(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $batch = TrainingBatch::first();

        $invalidFile = UploadedFile::fake()->create('malicious.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->post("/batches/{$batch->id}/participants/bulk-upload", [
            'file' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    /**
     * Test District Coordinator cannot bulk upload into another district's batch.
     */
    public function test_district_coordinator_cannot_upload_into_unassigned_district(): void
    {
        $balasoreUser = User::where('email', 'dlcbalasore@okcl.org')->first(); // Baleshwar ID 1103
        $this->assertNotNull($balasoreUser);

        // Cuttack batch (district_id 1107)
        $cuttackBatch = TrainingBatch::where('district_id', 1107)->first();
        $this->assertNotNull($cuttackBatch);

        $rows = [
            ['Test Trainee ' . uniqid(), 'PEO', 'GP Office', 'Dept', '9999999999', 'test@example.com'],
        ];
        $file = $this->createTestExcelFile($rows);

        $response = $this->actingAs($balasoreUser)->post("/batches/{$cuttackBatch->id}/participants/bulk-upload", [
            'file' => $file,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot access participant upload or directory.
     */
    public function test_guest_cannot_access_participants(): void
    {
        $response = $this->get('/participants');
        $response->assertRedirect('/login');

        $batch = TrainingBatch::first();
        $response = $this->post("/batches/{$batch->id}/participants/bulk-upload");
        $response->assertRedirect('/login');
    }
}
