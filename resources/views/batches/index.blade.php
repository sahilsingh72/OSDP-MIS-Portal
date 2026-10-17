@extends('layouts.app', ['title' => 'Training Batches', 'headerTitle' => 'Batch Management'])

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Training Batches Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage schedules, track evidence completeness, and verify training deliveries</p>
        </div>
        <div class="flex items-center space-x-3">
            @can('batches.create')
            <a href="{{ route('batches.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Schedule New Batch</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter Form Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('batches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Search Batch Code</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="OSDP-BLK-..."
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Training Tier</label>
                <select name="tier_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none">
                    <option value="">All Tiers</option>
                    @foreach($tiers as $tier)
                        <option value="{{ $tier->id }}" {{ request('tier_id') == $tier->id ? 'selected' : '' }}>{{ $tier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">District</label>
                <select name="district_id" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none">
                    <option value="">All Districts</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="awaiting_evidence" {{ request('status') === 'awaiting_evidence' ? 'selected' : '' }}>Awaiting Evidence</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="correction_required" {{ request('status') === 'correction_required' ? 'selected' : '' }}>Correction Required</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 mb-1">Date Range</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            @if(request('overdue_only'))
                <input type="hidden" name="overdue_only" value="1">
            @endif

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition text-center">
                    Filter
                </button>
                <a href="{{ route('batches.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if(request('overdue_only'))
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800">
        <div class="flex items-center space-x-2">
            <span class="px-2 py-0.5 rounded-lg bg-rose-200 text-rose-900 font-bold uppercase text-[10px]">Filter Active</span>
            <span class="font-medium">Showing batches with evidence submissions overdue beyond the 48-hour SLA deadline.</span>
        </div>
        <a href="{{ route('batches.index') }}" class="inline-flex items-center space-x-1 font-semibold text-rose-700 hover:text-rose-900 underline">
            <span>Clear Filter (Show All)</span>
            <span>✕</span>
        </a>
    </div>
    @endif

    <!-- Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5 w-16">S.No</th>
                        <th class="px-6 py-3.5">Batch Code</th>
                        <th class="px-6 py-3.5">Tier</th>
                        <th class="px-6 py-3.5">District / Block</th>
                        <th class="px-6 py-3.5">Scheduled Date</th>
                        <th class="px-6 py-3.5">Trainers</th>
                        <th class="px-6 py-3.5">Participants</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Evidence Status</th>
                        <th class="px-6 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                            {{ $batches->firstItem() ? ($batches->firstItem() + $loop->index) : $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                            <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:underline">
                                {{ $batch->batch_code }}
                            </a>
                            @if($batch->is_overdue)
                                <span class="block text-[10px] text-rose-600 font-semibold uppercase tracking-wide">⚠️ Overdue (>48h)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-slate-800">{{ $batch->tier?->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $batch->district?->name ?? 'State-Level' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $batch->block?->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ $batch->scheduled_date->format('d M Y') }}</div>
                            <div class="text-[11px] text-slate-400">{{ substr($batch->start_time, 0, 5) }} - {{ substr($batch->end_time, 0, 5) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            {{ $batch->trainers->pluck('name')->implode(', ') ?: 'None' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" title="{{ $batch->present_participants }} Present / {{ $batch->registered_participants }} Registered">
                            <span class="font-semibold text-slate-900">{{ $batch->present_participants }}</span>
                            <span class="text-slate-400 text-[11px]">/ {{ $batch->registered_participants }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-status-badge :status="$batch->status" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $ev = $batch->evidence_status; @endphp
                            <div class="flex items-center space-x-1.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $ev['is_complete'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $ev['completed_count'] }}/6 Items
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                            <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:text-blue-900 font-semibold">View</a>
                            <a href="{{ route('batches.attendance.show', $batch) }}" class="text-emerald-600 hover:text-emerald-900 font-semibold">Attendance</a>
                            <a href="{{ route('batches.evidence.index', $batch) }}" class="text-purple-600 hover:text-purple-900 font-semibold">Evidence</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                            No training batches match the selected criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
