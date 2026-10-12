@extends('layouts.app', ['title' => 'Monitoring Reports', 'headerTitle' => 'Reports & Analytics'])

@section('content')
<div class="space-y-6">

    <!-- Header & Export Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Programme Monitoring & Regulatory Reports</h1>
            <p class="text-xs text-slate-500 mt-0.5">Aggregated performance, attendance rosters, evidence compliance, and district scorecards</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('reports.export.csv', request()->query()) }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV Spreadsheet</span>
            </a>
            <button onclick="window.print()" class="px-3.5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs transition">
                🖨️ Print
            </button>
        </div>
    </div>

    <!-- Report Type Tabs -->
    <div class="flex border-b border-slate-200 space-x-6 text-xs font-semibold">
        <a href="{{ route('reports.index', ['type' => 'statewide_progress']) }}"
            class="pb-3 border-b-2 transition {{ $reportType === 'statewide_progress' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Statewide Batch Register
        </a>
        <a href="{{ route('reports.index', ['type' => 'district_wise']) }}"
            class="pb-3 border-b-2 transition {{ $reportType === 'district_wise' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            District-wise Performance
        </a>
        <a href="{{ route('reports.index', ['type' => 'tier_summary']) }}"
            class="pb-3 border-b-2 transition {{ $reportType === 'tier_summary' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Training Tier Summary
        </a>
        <a href="{{ route('reports.index', ['type' => 'overdue_batches']) }}"
            class="pb-3 border-b-2 transition {{ $reportType === 'overdue_batches' ? 'border-rose-600 text-rose-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
            Overdue / Defaulter Batches
        </a>
    </div>

    <!-- Active Report Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if($reportType === 'district_wise')
            <!-- District Wise Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">District</th>
                            <th class="px-6 py-3.5">Total Batches Scheduled</th>
                            <th class="px-6 py-3.5">Verified / Completed</th>
                            <th class="px-6 py-3.5">Under Review / Pending</th>
                            <th class="px-6 py-3.5">Total Trained Officials</th>
                            <th class="px-6 py-3.5 text-right">Completion %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @foreach($reportData as $row)
                        @php $pct = $row->total_batches > 0 ? round(($row->completed_batches / $row->total_batches) * 100, 1) : 0; @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $row->name }} ({{ $row->code }})</td>
                            <td class="px-6 py-4">{{ $row->total_batches }}</td>
                            <td class="px-6 py-4 font-semibold text-emerald-600">{{ $row->completed_batches }}</td>
                            <td class="px-6 py-4 font-semibold text-indigo-600">{{ $row->pending_batches }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ number_format($row->total_participants ?? 0) }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $pct >= 80 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $pct }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($reportType === 'tier_summary')
            <!-- Tier Summary Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Training Tier</th>
                            <th class="px-6 py-3.5">Target Duration</th>
                            <th class="px-6 py-3.5">Target Size</th>
                            <th class="px-6 py-3.5">Total Batches Conducted</th>
                            <th class="px-6 py-3.5">Verified Batches</th>
                            <th class="px-6 py-3.5">Total Attendees</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @foreach($reportData as $row)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $row->name }}</td>
                            <td class="px-6 py-4">{{ $row->target_duration_hours }} Hours</td>
                            <td class="px-6 py-4">~{{ $row->target_batch_size }} participants</td>
                            <td class="px-6 py-4 font-bold">{{ $row->total_batches }}</td>
                            <td class="px-6 py-4 font-semibold text-emerald-600">{{ $row->verified_batches }}</td>
                            <td class="px-6 py-4 font-extrabold text-blue-600">{{ number_format($row->total_present ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($reportType === 'overdue_batches')
            <!-- Overdue Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-rose-50 text-rose-800 uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Batch Code</th>
                            <th class="px-6 py-3.5">Tier</th>
                            <th class="px-6 py-3.5">District / Block</th>
                            <th class="px-6 py-3.5">Scheduled Date</th>
                            <th class="px-6 py-3.5">Deadline Elapsed</th>
                            <th class="px-6 py-3.5">Missing Evidence</th>
                            <th class="px-6 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @forelse($reportData as $batch)
                        <tr class="hover:bg-rose-50/30 transition">
                            <td class="px-6 py-4 font-bold text-rose-700 font-mono">
                                <a href="{{ route('batches.show', $batch) }}" class="hover:underline">{{ $batch->batch_code }}</a>
                            </td>
                            <td class="px-6 py-4">{{ $batch->tier?->name }}</td>
                            <td class="px-6 py-4">{{ $batch->district?->name }} / {{ $batch->block?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ $batch->scheduled_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-semibold text-rose-600">
                                {{ $batch->submission_deadline ? $batch->submission_deadline->diffForHumans() : 'Expired' }}
                            </td>
                            <td class="px-6 py-4">
                                @php $ev = $batch->evidence_status; @endphp
                                <span class="font-bold text-rose-600">{{ 6 - $ev['completed_count'] }} items missing</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('batches.evidence.index', $batch) }}" class="text-blue-600 font-semibold hover:underline">
                                    Upload Now →
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-emerald-600 font-semibold">
                                ✓ No batches currently breaching the 48-hour submission SLA!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        @else
            <!-- Standard Statewide Batch Register -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3.5">Batch Code</th>
                            <th class="px-6 py-3.5">Tier</th>
                            <th class="px-6 py-3.5">District</th>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5">Registered</th>
                            <th class="px-6 py-3.5">Present</th>
                            <th class="px-6 py-3.5">Evidence Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700">
                        @foreach($reportData as $batch)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-bold text-blue-600 font-mono">
                                <a href="{{ route('batches.show', $batch) }}" class="hover:underline">{{ $batch->batch_code }}</a>
                            </td>
                            <td class="px-6 py-4">{{ $batch->tier?->name }}</td>
                            <td class="px-6 py-4">{{ $batch->district?->name ?? 'State Level' }}</td>
                            <td class="px-6 py-4">{{ $batch->scheduled_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$batch->status" /></td>
                            <td class="px-6 py-4">{{ $batch->registered_participants }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $batch->present_participants }}</td>
                            <td class="px-6 py-4">
                                @php $ev = $batch->evidence_status; @endphp
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $ev['is_complete'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $ev['completed_count'] }}/6 Items
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($reportData, 'hasPages') && $reportData->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $reportData->links() }}
            </div>
            @endif
        @endif
    </div>

</div>
@endsection
