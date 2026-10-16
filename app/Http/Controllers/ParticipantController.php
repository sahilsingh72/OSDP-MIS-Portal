<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Participant;
use App\Models\TrainingBatch;
use App\Services\ParticipantImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $trashedCount = Participant::onlyTrashed()->count();
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

        $participants = $query->orderBy('name')->paginate(20)->withQueryString();

        $batches = TrainingBatch::forUserScope($user)
            ->orderByDesc('scheduled_date')
            ->limit(100)
            ->get();

        return view('participants.index', compact('participants', 'batches', 'activeCount', 'trashedCount', 'status'));
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
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $phone = $validated['phone'] ?? null;
        $phoneMasked = $participant->phone_masked;
        if (!empty($phone) && !str_contains($phone, '*')) {
            $digits = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($digits) >= 10) {
                $phoneMasked = substr($digits, 0, 2) . '******' . substr($digits, -2);
            } else {
                $phoneMasked = $phone;
            }
        } elseif (empty($phone)) {
            $phoneMasked = null;
        }

        $oldData = $participant->toArray();

        $participant->update([
            'name' => $validated['name'],
            'designation' => $validated['designation'],
            'office_name' => $validated['office_name'],
            'department' => $validated['department'] ?? null,
            'phone_masked' => $phoneMasked,
            'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $participant->is_active,
        ]);

        AuditLog::record('participant_updated', $participant, $oldData, $participant->getChanges());

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
