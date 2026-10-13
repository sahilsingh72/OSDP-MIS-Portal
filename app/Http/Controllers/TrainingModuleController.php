<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\TrainingModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainingModuleController extends Controller
{
    /**
     * Display a listing of training modules.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('master-data.index');
    }

    /**
     * Show the form for creating a new training module.
     */
    public function create(): View
    {
        $this->authorize('create', TrainingModule::class);

        $nextModuleNumber = (TrainingModule::max('module_number') ?? 0) + 1;
        $suggestedCode = 'MOD-' . $nextModuleNumber;

        return view('modules.create', compact('nextModuleNumber', 'suggestedCode'));
    }

    /**
     * Store a newly created training module in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TrainingModule::class);

        $validated = $request->validate([
            'module_number' => ['required', 'integer', 'min:1', 'max:65535', 'unique:training_modules,module_number'],
            'code' => ['required', 'string', 'max:20', 'unique:training_modules,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'topics' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $topicsList = [];
        if (!empty($validated['topics'])) {
            $lines = preg_split('/\r\n|\r|\n/', $validated['topics']);
            $topicsList = array_values(array_filter(array_map('trim', $lines)));
        }

        $module = TrainingModule::create([
            'module_number' => $validated['module_number'],
            'code' => strtoupper(trim($validated['code'])),
            'title' => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'topics' => $topicsList,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record('module_created', $module, null, $module->toArray());

        return redirect()->route('master-data.index')
            ->with('status', "Curriculum Module {$module->module_number} ({$module->code}) created successfully.");
    }

    /**
     * Show the form for editing the specified training module.
     */
    public function edit(TrainingModule $module): View
    {
        $this->authorize('update', $module);

        $batchesCount = $module->batches()->count();

        return view('modules.edit', compact('module', 'batchesCount'));
    }

    /**
     * Update the specified training module in storage.
     */
    public function update(Request $request, TrainingModule $module): RedirectResponse
    {
        $this->authorize('update', $module);

        $validated = $request->validate([
            'module_number' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
                Rule::unique('training_modules', 'module_number')->ignore($module->id),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('training_modules', 'code')->ignore($module->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'topics' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $topicsList = [];
        if (!empty($validated['topics'])) {
            $lines = preg_split('/\r\n|\r|\n/', $validated['topics']);
            $topicsList = array_values(array_filter(array_map('trim', $lines)));
        }

        $oldValues = $module->toArray();

        $module->update([
            'module_number' => $validated['module_number'],
            'code' => strtoupper(trim($validated['code'])),
            'title' => trim($validated['title']),
            'description' => $validated['description'] ?? null,
            'topics' => $topicsList,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::record('module_updated', $module, $oldValues, $module->toArray());

        return redirect()->route('master-data.index')
            ->with('status', "Curriculum Module {$module->module_number} ({$module->code}) updated successfully.");
    }

    /**
     * Remove the specified training module from storage.
     */
    public function destroy(TrainingModule $module): RedirectResponse
    {
        $this->authorize('delete', $module);

        $batchesCount = $module->batches()->count();

        if ($batchesCount > 0) {
            return redirect()->route('master-data.index')->withErrors([
                'module' => "Cannot delete Module {$module->module_number} ({$module->code}) because it is assigned to {$batchesCount} training batch(es). To retire this curriculum module from future scheduling without affecting past training records, edit it and set its status to Inactive.",
            ]);
        }

        $oldValues = $module->toArray();
        $moduleIdentifier = "Module {$module->module_number} ({$module->code}: {$module->title})";

        $module->delete();

        AuditLog::record('module_deleted', null, $oldValues, null);

        return redirect()->route('master-data.index')
            ->with('status', "{$moduleIdentifier} was deleted successfully.");
    }
}
