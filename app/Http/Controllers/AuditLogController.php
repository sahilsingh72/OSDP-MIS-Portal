<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display searchable audit trail.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', \App\Models\AuditLog::class);

        $query = AuditLog::with('user');

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('event', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('auditable_type', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('created_at')->paginate(25)->withQueryString();

        return view('audit-logs.index', compact('logs'));
    }
}
