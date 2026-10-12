@extends('layouts.app', ['title' => 'Audit Trail', 'headerTitle' => 'Security & Audit Logging'])

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Security Audit Trail & Event Logs</h1>
        <p class="text-xs text-slate-500 mt-0.5">Immutable record of logins, batch state transitions, evidence actions, reviews, and exports</p>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Search Keywords</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Event, IP address, Model..."
                    class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Event Type</label>
                <select name="event" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    <option value="">All Events</option>
                    <option value="login_success" {{ request('event') === 'login_success' ? 'selected' : '' }}>Login Success</option>
                    <option value="login_failed" {{ request('event') === 'login_failed' ? 'selected' : '' }}>Login Failed</option>
                    <option value="batch_created" {{ request('event') === 'batch_created' ? 'selected' : '' }}>Batch Created</option>
                    <option value="batch_updated" {{ request('event') === 'batch_updated' ? 'selected' : '' }}>Batch Updated</option>
                    <option value="evidence_uploaded" {{ request('event') === 'evidence_uploaded' ? 'selected' : '' }}>Evidence Uploaded</option>
                    <option value="evidence_downloaded" {{ request('event') === 'evidence_downloaded' ? 'selected' : '' }}>Evidence Downloaded</option>
                    <option value="batch_reviewed" {{ request('event') === 'batch_reviewed' ? 'selected' : '' }}>Batch Reviewed</option>
                    <option value="field_verification_logged" {{ request('event') === 'field_verification_logged' ? 'selected' : '' }}>Field Verification</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition text-center">
                    Filter Logs
                </button>
                <a href="{{ route('audit-logs.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Timestamp</th>
                        <th class="px-6 py-3.5">Actor (User)</th>
                        <th class="px-6 py-3.5">Event</th>
                        <th class="px-6 py-3.5">Target Entity</th>
                        <th class="px-6 py-3.5">IP Address</th>
                        <th class="px-6 py-3.5">Change Metadata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                            {{ $log->user?->name ?? 'System / Anonymous' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-800">
                                {{ strtoupper(str_replace('_', ' ', $log->event)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-mono text-[11px]">
                            {{ class_basename($log->auditable_type ?? 'N/A') }} #{{ $log->auditable_id ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-[11px] text-slate-500">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="px-6 py-4 max-w-xs truncate font-mono text-[11px] text-slate-500">
                            {{ json_encode($log->new_values ?? $log->old_values) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            No audit log events match the query.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
