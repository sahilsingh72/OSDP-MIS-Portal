<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    /**
     * Display system settings form.
     */
    public function index(): View
    {
        $this->authorize('viewAny', SystemSetting::class);

        $settings = SystemSetting::all()->groupBy('group');

        return view('settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', SystemSetting::class);

        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        AuditLog::record('settings_updated', null, null, $inputs);

        return back()->with('status', 'System operational settings updated successfully.');
    }
}
