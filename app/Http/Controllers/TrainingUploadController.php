<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BatchFeedback;
use App\Models\BatchSummary;
use App\Models\SystemSetting;
use App\Models\TrainingBatch;
use App\Models\TrainingUpload;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class TrainingUploadController extends Controller
{
    /**
     * Show the Evidence Upload Center for a batch.
     */
    public function index(TrainingBatch $batch): View
    {
        $this->authorize('view', $batch);

        $batch->load([
            'tier',
            'district',
            'block',
            'trainers',
            'uploads.uploader',
            'summary',
            'feedback',
            'reviews.reviewer',
        ]);

        $evidenceStatus = $batch->evidence_status;

        // Current operational upload limits
        $maxPhotoMb = (int)SystemSetting::get('max_photo_size_mb', 10);
        $maxVideoMb = (int)SystemSetting::get('max_video_size_mb', 100);
        $maxPdfMb = (int)SystemSetting::get('max_pdf_size_mb', 25);

        return view('evidence.index', compact('batch', 'evidenceStatus', 'maxPhotoMb', 'maxVideoMb', 'maxPdfMb'));
    }

    /**
     * Handle evidence file upload.
     */
    public function store(Request $request, TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('uploadEvidence', $batch);

        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'evidence_type' => [
                'required',
                'in:attendance_sheet,photo_1,photo_2,video,feedback_forms,batch_completion_report',
            ],
            'file' => ['required', 'file'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');
        $evidenceType = $validated['evidence_type'];
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // 1. Strict MIME type & size verification by type
        $maxPhotoBytes = (int)SystemSetting::get('max_photo_size_mb', 10) * 1024 * 1024;
        $maxVideoBytes = (int)SystemSetting::get('max_video_size_mb', 100) * 1024 * 1024;
        $maxPdfBytes = (int)SystemSetting::get('max_pdf_size_mb', 25) * 1024 * 1024;

        if (in_array($evidenceType, ['photo_1', 'photo_2'])) {
            if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
                return back()->withErrors(['file' => 'Photographs must be valid JPEG or PNG images.'])->withInput();
            }
            if ($fileSize > $maxPhotoBytes) {
                return back()->withErrors(['file' => "Photo size exceeds the maximum limit of " . SystemSetting::get('max_photo_size_mb', 10) . " MB."])->withInput();
            }
        } elseif ($evidenceType === 'video') {
            if (!in_array($mimeType, ['video/mp4', 'video/quicktime', 'video/x-msvideo'])) {
                return back()->withErrors(['file' => 'Video recording must be a valid MP4 video clip.'])->withInput();
            }
            if ($fileSize > $maxVideoBytes) {
                return back()->withErrors(['file' => "Video size exceeds the maximum limit of " . SystemSetting::get('max_video_size_mb', 100) . " MB."])->withInput();
            }
        } else {
            // PDF documents (attendance, feedback, completion report)
            if (!in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png'])) {
                return back()->withErrors(['file' => 'Document must be a valid PDF file (or scanned image for attendance sheet).'])->withInput();
            }
            if ($fileSize > $maxPdfBytes) {
                return back()->withErrors(['file' => "Document size exceeds the maximum limit of " . SystemSetting::get('max_pdf_size_mb', 25) . " MB."])->withInput();
            }
        }

        // 2. Compute SHA-256 integrity hash
        $fileHash = hash_file('sha256', $file->getRealPath());

        // 3. Generate randomized storage path
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $storageDir = 'batches/' . $batch->batch_code;
        $filename = Str::uuid()->toString() . '.' . $extension;
        $storagePath = $storageDir . '/' . $filename;

        // Store file onto private disk
        Storage::disk('evidence')->putFileAs($storageDir, $file, $filename);

        DB::transaction(function () use ($batch, $evidenceType, $originalName, $storagePath, $mimeType, $fileSize, $fileHash, $user, $validated) {
            // Check existing upload of same type to calculate versioning
            $latest = TrainingUpload::where('training_batch_id', $batch->id)
                ->where('evidence_type', $evidenceType)
                ->latest('version')
                ->first();

            $nextVersion = $latest ? ($latest->version + 1) : 1;

            // Invalidate prior versions if any
            if ($latest) {
                $latest->update(['is_valid' => false]);
            }

            $upload = TrainingUpload::create([
                'training_batch_id' => $batch->id,
                'evidence_type' => $evidenceType,
                'original_filename' => $originalName,
                'file_path' => $storagePath,
                'mime_type' => $mimeType,
                'file_size_bytes' => $fileSize,
                'file_hash' => $fileHash,
                'uploaded_by' => $user->id,
                'version' => $nextVersion,
                'is_valid' => true,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            AuditLog::record('evidence_uploaded', $upload, null, [
                'batch_id' => $batch->id,
                'evidence_type' => $evidenceType,
                'file_name' => $originalName,
                'version' => $nextVersion,
            ]);
        });

        return back()->with('status', 'Evidence file uploaded successfully!');
    }

    /**
     * Update an existing evidence upload (replace file, update evidence type, or edit remarks).
     */
    public function update(Request $request, TrainingUpload $upload): RedirectResponse
    {
        $this->authorize('update', $upload);

        $batch = $upload->batch;

        $validated = $request->validate([
            'evidence_type' => [
                'required',
                'in:attendance_sheet,photo_1,photo_2,video,feedback_forms,batch_completion_report',
            ],
            'file' => ['nullable', 'file'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $newType = $validated['evidence_type'];
        $oldAttributes = $upload->only(['evidence_type', 'original_filename', 'file_size_bytes', 'remarks', 'version']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();

            // Strict MIME type & size verification by type
            $maxPhotoBytes = (int)SystemSetting::get('max_photo_size_mb', 10) * 1024 * 1024;
            $maxVideoBytes = (int)SystemSetting::get('max_video_size_mb', 100) * 1024 * 1024;
            $maxPdfBytes = (int)SystemSetting::get('max_pdf_size_mb', 25) * 1024 * 1024;

            if (in_array($newType, ['photo_1', 'photo_2'])) {
                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg'])) {
                    return back()->withErrors(['file' => 'Photographs must be valid JPEG or PNG images.'])->withInput();
                }
                if ($fileSize > $maxPhotoBytes) {
                    return back()->withErrors(['file' => "Photo size exceeds the maximum limit of " . SystemSetting::get('max_photo_size_mb', 10) . " MB."])->withInput();
                }
            } elseif ($newType === 'video') {
                if (!in_array($mimeType, ['video/mp4', 'video/quicktime', 'video/x-msvideo'])) {
                    return back()->withErrors(['file' => 'Video recording must be a valid MP4 video clip.'])->withInput();
                }
                if ($fileSize > $maxVideoBytes) {
                    return back()->withErrors(['file' => "Video size exceeds the maximum limit of " . SystemSetting::get('max_video_size_mb', 100) . " MB."])->withInput();
                }
            } else {
                if (!in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png'])) {
                    return back()->withErrors(['file' => 'Document must be a valid PDF file (or scanned image for attendance sheet).'])->withInput();
                }
                if ($fileSize > $maxPdfBytes) {
                    return back()->withErrors(['file' => "Document size exceeds the maximum limit of " . SystemSetting::get('max_pdf_size_mb', 25) . " MB."])->withInput();
                }
            }

            // Compute SHA-256 integrity hash
            $fileHash = hash_file('sha256', $file->getRealPath());

            // Generate randomized storage path
            $extension = $file->getClientOriginalExtension() ?: 'bin';
            $storageDir = 'batches/' . $batch->batch_code;
            $filename = Str::uuid()->toString() . '.' . $extension;
            $storagePath = $storageDir . '/' . $filename;

            // Store new file onto private disk
            Storage::disk('evidence')->putFileAs($storageDir, $file, $filename);

            // Clean up old file if exists
            if ($upload->file_path && Storage::disk('evidence')->exists($upload->file_path)) {
                Storage::disk('evidence')->delete($upload->file_path);
            }

            // If switching evidence type to another type, deactivate any existing upload of that target type
            if ($newType !== $upload->evidence_type) {
                TrainingUpload::where('training_batch_id', $batch->id)
                    ->where('evidence_type', $newType)
                    ->where('id', '!=', $upload->id)
                    ->update(['is_valid' => false]);
            }

            $upload->update([
                'evidence_type' => $newType,
                'original_filename' => $originalName,
                'file_path' => $storagePath,
                'mime_type' => $mimeType,
                'file_size_bytes' => $fileSize,
                'file_hash' => $fileHash,
                'version' => $upload->version + 1,
                'remarks' => $validated['remarks'] ?? null,
                'is_valid' => true,
            ]);
        } else {
            // If switching evidence type without new file, deactivate any conflicting upload of that target type
            if ($newType !== $upload->evidence_type) {
                TrainingUpload::where('training_batch_id', $batch->id)
                    ->where('evidence_type', $newType)
                    ->where('id', '!=', $upload->id)
                    ->update(['is_valid' => false]);
            }

            $upload->update([
                'evidence_type' => $newType,
                'remarks' => $validated['remarks'] ?? null,
            ]);
        }

        AuditLog::record('evidence_updated', $upload, $oldAttributes, [
            'batch_id' => $batch->id,
            'evidence_type' => $upload->evidence_type,
            'file_name' => $upload->original_filename,
            'version' => $upload->version,
            'file_replaced' => $request->hasFile('file'),
        ]);

        return back()->with('status', 'Evidence item updated successfully!');
    }

    /**
     * Securely download or stream private evidence file.
     */
    public function download(TrainingUpload $upload): StreamedResponse|RedirectResponse
    {
        $this->authorize('download', $upload);

        $disk = Storage::disk('evidence');
        if (!$disk->exists($upload->file_path)) {
            return back()->withErrors(['file' => 'Requested evidence file was not found on private storage.']);
        }

        AuditLog::record('evidence_downloaded', $upload, null, [
            'file_name' => $upload->original_filename,
            'evidence_type' => $upload->evidence_type,
        ]);

        return $disk->response($upload->file_path, $upload->original_filename);
    }

    /**
     * Delete/Invalidate evidence upload.
     */
    public function destroy(TrainingUpload $upload): RedirectResponse
    {
        $this->authorize('delete', $upload);

        $batch = $upload->batch;
        $upload->update(['is_valid' => false]);
        $upload->delete();

        AuditLog::record('evidence_deleted', $upload, ['is_valid' => true], ['is_valid' => false]);

        return back()->with('status', 'Evidence file removed successfully.');
    }

    /**
     * Save Form 3 Part C Training Summary.
     */
    public function saveSummary(Request $request, TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('manageSummary', $batch);

        $validated = $request->validate([
            'registered_count' => ['required', 'integer', 'min:0'],
            'present_count' => ['required', 'integer', 'min:0'],
            'feedback_forms_received' => ['required', 'integer', 'min:0'],
            'actual_start_time' => ['nullable'],
            'actual_end_time' => ['nullable'],
            'pre_post_assessment_done' => ['nullable'],
            'what_went_well' => ['nullable', 'string', 'max:1000'],
            'problems_faced' => ['nullable', 'string', 'max:1000'],
            'follow_up_actions' => ['nullable', 'string', 'max:1000'],
            'submitted_by_name' => ['nullable', 'string', 'max:255'],
        ]);

        BatchSummary::updateOrCreate(
            ['training_batch_id' => $batch->id],
            [
                'registered_count' => $validated['registered_count'],
                'present_count' => $validated['present_count'],
                'feedback_forms_received' => $validated['feedback_forms_received'],
                'actual_start_time' => $validated['actual_start_time'] ?? null,
                'actual_end_time' => $validated['actual_end_time'] ?? null,
                'pre_post_assessment_done' => isset($validated['pre_post_assessment_done']),
                'what_went_well' => $validated['what_went_well'] ?? null,
                'problems_faced' => $validated['problems_faced'] ?? null,
                'follow_up_actions' => $validated['follow_up_actions'] ?? null,
                'submitted_by_name' => $validated['submitted_by_name'] ?? Auth::user()->name,
            ]
        );

        // Sync batch present counts
        $batch->update([
            'actual_start_time' => $validated['actual_start_time'] ?? $batch->actual_start_time,
            'actual_end_time' => $validated['actual_end_time'] ?? $batch->actual_end_time,
            'registered_participants' => $validated['registered_count'],
            'present_participants' => $validated['present_count'],
        ]);

        return back()->with('status', 'Form 3 Training Summary saved successfully.');
    }

    /**
     * Save Form 1 Feedback Metrics.
     */
    public function saveFeedback(Request $request, TrainingBatch $batch): RedirectResponse
    {
        $this->authorize('manageSummary', $batch);

        $validated = $request->validate([
            'q1_clarity_avg' => ['required', 'numeric', 'min:1', 'max:5'],
            'q2_knowledge_avg' => ['required', 'numeric', 'min:1', 'max:5'],
            'q3_relevance_avg' => ['required', 'numeric', 'min:1', 'max:5'],
            'q4_handbook_avg' => ['required', 'numeric', 'min:1', 'max:5'],
            'q5_overall_avg' => ['required', 'numeric', 'min:1', 'max:5'],
            'total_forms_analyzed' => ['required', 'integer', 'min:1'],
            'qualitative_highlights' => ['nullable', 'string', 'max:1000'],
            'improvement_suggestions' => ['nullable', 'string', 'max:1000'],
        ]);

        BatchFeedback::updateOrCreate(
            ['training_batch_id' => $batch->id],
            $validated
        );

        return back()->with('status', 'Form 1 Participant Feedback summary saved successfully.');
    }
}
