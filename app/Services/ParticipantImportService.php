<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\BatchParticipant;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantImportService
{
    /**
     * Import participants from an uploaded Excel or CSV file.
     *
     * @param UploadedFile $file
     * @param TrainingBatch|null $batch
     * @param User|null $user
     * @return array{total_rows: int, imported: int, skipped: int, errors: array}
     */
    public function importFromExcel(UploadedFile $file, ?TrainingBatch $batch = null, ?User $user = null): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        // Find header row (check first 10 rows)
        $headerRow = 1;
        $headerMap = [];

        for ($row = 1; $row <= min(10, $highestRow); $row++) {
            $rowValues = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $val = trim((string) $worksheet->getCell([$col, $row])->getValue());
                // Lowercase first, then strip non-alphanumeric characters
                $rowValues[$col] = preg_replace('/[^a-z0-9]/', '', strtolower($val));
            }

            // Check if this row looks like a header (contains 'name' or 'participantname' or 'fullname')
            if (
                in_array('name', $rowValues, true) ||
                in_array('participantname', $rowValues, true) ||
                in_array('fullname', $rowValues, true) ||
                in_array('participant', $rowValues, true)
            ) {
                $headerRow = $row;
                $headerMap = $this->mapHeaders($rowValues);
                break;
            }
        }

        if (empty($headerMap)) {
            // Default sequential mapping if no explicit header recognized (Columns A to F)
            $headerMap = [
                1 => 'name',
                2 => 'designation',
                3 => 'office_name',
                4 => 'department',
                5 => 'phone',
                6 => 'email',
            ];
            $headerRow = 1;
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $totalRows = 0;

        DB::transaction(function () use (
            $worksheet,
            $highestRow,
            $highestColumnIndex,
            $headerRow,
            $headerMap,
            $batch,
            $user,
            &$imported,
            &$skipped,
            &$errors,
            &$totalRows
        ) {
            $currentSerial = $batch ? $batch->batchParticipants()->count() : 0;

            for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                $rowData = [];
                $hasContent = false;

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $field = $headerMap[$col] ?? null;
                    if ($field) {
                        $val = trim((string) $worksheet->getCell([$col, $row])->getValue());
                        if ($val !== '') {
                            $hasContent = true;
                        }
                        $rowData[$field] = $val;
                    }
                }

                // Skip entirely blank rows
                if (!$hasContent) {
                    continue;
                }

                $name = trim($rowData['name'] ?? '');
                $lowerName = strtolower($name);

                // Guard against accidental header/title rows or instructions being imported as participants
                if (
                    empty($name) ||
                    str_starts_with($lowerName, 'batch:') ||
                    str_starts_with($lowerName, 'instructions:') ||
                    str_starts_with($lowerName, 'odisha state') ||
                    str_contains($lowerName, 'data policy') ||
                    str_contains($lowerName, 'participant name') ||
                    $lowerName === 'name' ||
                    $lowerName === 'sl no' ||
                    $lowerName === 'serial' ||
                    $lowerName === 'sr no'
                ) {
                    continue;
                }

                $totalRows++;

                // Validate minimum mandatory fields
                $designation = trim($rowData['designation'] ?? '');
                $officeName = trim($rowData['office_name'] ?? '');

                if (empty($designation)) {
                    $designation = 'Official / Trainee';
                }

                if (empty($officeName)) {
                    $officeName = $batch ? ($batch->block?->name ?? $batch->district?->name ?? 'District Office') : 'General';
                }

                $phone = $rowData['phone'] ?? null;
                $phoneMasked = null;
                if (!empty($phone)) {
                    // Extract digits
                    $digits = preg_replace('/[^0-9]/', '', $phone);
                    if (strlen($digits) >= 10) {
                        $phoneMasked = substr($digits, 0, 2) . '******' . substr($digits, -2);
                    } else {
                        $phoneMasked = $phone;
                    }
                }

                $email = !empty($rowData['email']) ? strtolower(trim($rowData['email'])) : null;
                $department = !empty($rowData['department']) ? trim($rowData['department']) : null;

                // Create or find master Participant
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
                        'phone_masked' => $phoneMasked,
                        'email' => $email,
                        'is_active' => true,
                        'created_by' => $user?->id ?? Auth::id(),
                    ]);
                } else {
                    if ($participant->trashed()) {
                        $participant->restore();
                    }
                    // Update any missing details
                    $participant->update([
                        'designation' => $designation ?: $participant->designation,
                        'department' => $department ?: $participant->department,
                        'phone_masked' => $phoneMasked ?: $participant->phone_masked,
                        'email' => $email ?: $participant->email,
                    ]);
                }

                // If batch context, enroll participant into batch
                if ($batch) {
                    $alreadyEnrolled = BatchParticipant::where('training_batch_id', $batch->id)
                        ->where('participant_id', $participant->id)
                        ->first();

                    if (!$alreadyEnrolled) {
                        $currentSerial++;
                        $bp = BatchParticipant::create([
                            'training_batch_id' => $batch->id,
                            'participant_id' => $participant->id,
                            'serial_number' => $currentSerial,
                        ]);

                        // Enforce: Bulk upload only adds participant details, NOT attendance.
                        // Attendance must be recorded manually from paper Form 2 sheets.
                        AttendanceRecord::create([
                            'training_batch_id' => $batch->id,
                            'batch_participant_id' => $bp->id,
                            'morning_present' => false,
                            'afternoon_present' => false,
                            'remarks' => null,
                            'marked_by' => null,
                        ]);

                        $imported++;
                    } else {
                        // Already in batch, skip duplicate enrollment
                        $skipped++;
                    }
                } else {
                    $imported++;
                }
            }

            if ($batch) {
                // Recalculate registered and present counts
                $registered = $batch->batchParticipants()->count();
                $present = $batch->attendanceRecords()
                    ->where(function ($q) {
                        $q->where('morning_present', true)->orWhere('afternoon_present', true);
                    })
                    ->count();

                $batch->update([
                    'registered_participants' => $registered,
                    'present_participants' => $present,
                ]);

                AuditLog::record('participants_bulk_imported', $batch, null, [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'total_registered' => $registered,
                ]);
            }
        });

        return [
            'total_rows' => $totalRows,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Map cleaned row headers to model fields.
     */
    protected function mapHeaders(array $rowValues): array
    {
        $map = [];
        foreach ($rowValues as $col => $header) {
            if (in_array($header, ['name', 'participantname', 'fullname', 'traineename', 'candidate', 'participant'])) {
                $map[$col] = 'name';
            } elseif (in_array($header, ['designation', 'role', 'post', 'rank', 'designationtitle'])) {
                $map[$col] = 'designation';
            } elseif (in_array($header, ['officename', 'office', 'grampanchayat', 'gp', 'organization', 'placeofposting', 'officegrampanchayat'])) {
                $map[$col] = 'office_name';
            } elseif (in_array($header, ['department', 'dept', 'division', 'directorate'])) {
                $map[$col] = 'department';
            } elseif (in_array($header, ['phone', 'phonenumber', 'mobile', 'mobileno', 'contact', 'contactno'])) {
                $map[$col] = 'phone';
            } elseif (in_array($header, ['email', 'emailid', 'mail'])) {
                $map[$col] = 'email';
            }
        }
        return $map;
    }

    /**
     * Generate and download a formatted official Excel (.xlsx) template.
     */
    public function downloadTemplate(?TrainingBatch $batch = null): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Participant Register');

        // Page setup
        $sheet->setShowGridLines(true);

        // Table Column Headers (Row 1) - Clean standard spreadsheet format
        $headers = [
            'A1' => 'Participant Name *',
            'B1' => 'Designation *',
            'C1' => 'Office / Gram Panchayat *',
            'D1' => 'Department',
            'E1' => 'Mobile No',
            'F1' => 'Email ID',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'], // Dark slate
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '334155'],
                ],
            ],
        ];

        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample Data Rows (Row 2 onwards)
        $sampleData = [
            [
                'Ramesh Chandra Mohapatra',
                'Panchayat Executive Officer (PEO)',
                'Mendhasala GP',
                'Panchayati Raj & Drinking Water',
                '9861012345',
                'ramesh.peo@gmail.com',
            ],
            [
                'Pratima Jena',
                'Gram Rozgar Sevak (GRS)',
                'Kantabada GP',
                'Panchayati Raj & Drinking Water',
                '9437012345',
                'pratima.grs@gmail.com',
            ],
            [
                'Subash Chandra Sahoo',
                'Data Entry Operator (DEO)',
                'Jatni Block Office',
                'Electronics & Information Technology',
                '9938012345',
                'subash.deo@gmail.com',
            ],
            [
                'Ananya Priyadarshini',
                'Additional Block Development Officer',
                'Bhubaneswar Block',
                'Panchayati Raj & Drinking Water',
                '9439012345',
                'ananya.abdo@odisha.gov.in',
            ],
            [
                'Deepak Kumar Nayak',
                'Master Trainer / Coordinator',
                'District Project Office',
                'Odisha Computer Application Centre',
                '9337012345',
                'deepak.nayak@okcl.org',
            ],
        ];

        $startRow = 2;
        foreach ($sampleData as $idx => $row) {
            $r = $startRow + $idx;
            $sheet->fromArray($row, null, "A{$r}");

            $dataStyle = [
                'font' => ['size' => 10],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $sheet->getStyle("A{$r}:F{$r}")->applyFromArray($dataStyle);
            $sheet->getRowDimension($r)->setRowHeight(22);
        }

        // Auto-fit column widths
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = $batch 
            ? "Participant_Upload_Template_{$batch->batch_code}.xlsx"
            : 'OSDP_Participant_Upload_Template.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
