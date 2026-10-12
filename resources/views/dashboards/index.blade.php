@extends('layouts.app', ['title' => 'Executive Dashboard', 'headerTitle' => 'Monitoring Dashboard'])

@section('content')
<div class="space-y-6">

    <!-- Top Banner & State Context -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-indigo-900 rounded-2xl p-6 text-white shadow-lg border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center space-x-2 px-2.5 py-1 rounded-md bg-orange-500/20 text-orange-400 text-xs font-semibold uppercase tracking-wider mb-2 border border-orange-500/30">
                <span>OSDP 2.0 Monitoring MIS</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Odisha State Data Policy 2.0 Capacity Building
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-2xl">
                Statewide multi-tier training operations, participant attendance tracking, evidence verification, and compliance monitoring across 30 Districts, 314 Blocks, and Gram Panchayats.
            </p>
        </div>

        <div class="flex items-center space-x-3 self-start md:self-auto">
            @can('batches.create')
            <a href="{{ route('batches.create') }}" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-md shadow-orange-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Schedule Batch</span>
            </a>
            @endcan
            @can('reports.view')
            <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 text-slate-200 border border-slate-700 font-semibold text-xs transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Reports</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Total Batches -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Batches</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($totalBatches) }}</span>
                <span class="text-xs text-slate-500 ml-1">/ {{ $activePlan ? number_format($activePlan->target_batches) : '2,149' }} target</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ $scheduledBatches }} Scheduled</span>
                <span class="text-emerald-600 font-semibold">{{ $verifiedBatches }} Verified</span>
            </div>
        </div>

        <!-- Verified & Accepted -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Verified Batches</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-emerald-600">{{ number_format($verifiedBatches) }}</span>
                <span class="text-xs text-slate-500 ml-1">settlement ready</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ $submittedBatches }} In Review</span>
                <span class="text-indigo-600 font-semibold">{{ $inProgressBatches }} In Progress</span>
            </div>
        </div>

        <!-- Participants Attendance -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Trained Officials</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900">{{ number_format($totalPresent) }}</span>
                <span class="text-xs text-slate-500 ml-1">officials present</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ number_format($totalRegistered) }} Registered</span>
                <span class="text-purple-600 font-semibold">{{ $totalRegistered > 0 ? round(($totalPresent / $totalRegistered) * 100, 1) : 0 }}% Turnout</span>
            </div>
        </div>

        <!-- Overdue SLA & Feedback -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overdue (>48h)</span>
                <span class="p-2 rounded-xl {{ $overdueBatches > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold {{ $overdueBatches > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $overdueBatches }}
                </span>
                <div class="text-right">
                    <span class="text-xs font-semibold text-amber-500">★ {{ number_format($avgFeedbackScore, 2) }}</span>
                    <span class="text-[10px] text-slate-400 block">Avg Quality</span>
                </div>
            </div>
            <div class="mt-2 text-xs text-slate-500">
                @if($overdueBatches > 0)
                    <span class="text-rose-600 font-medium">⚠️ Action needed within 48h SLA</span>
                @else
                    <span class="text-emerald-600 font-medium">✓ All evidence packs compliant</span>
                @endif
            </div>
        </div>

    </div>

    <!-- Analytics Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Status Breakdown Doughnut Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">
                Batch Lifecycle Distribution
            </h3>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-slate-600">
                <div class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2"></span>Verified: {{ $verifiedBatches }}</div>
                <div class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-purple-500 mr-2"></span>Submitted: {{ $submittedBatches }}</div>
                <div class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2"></span>In Progress: {{ $inProgressBatches }}</div>
                <div class="flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-sky-500 mr-2"></span>Scheduled: {{ $scheduledBatches }}</div>
            </div>
        </div>

        <!-- Training Tier Progress Bar Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm lg:col-span-2">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4">
                Training Reach by Administrative Tier
            </h3>
            <div class="relative h-64">
                <canvas id="tierChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Recent Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Recent Training Batches
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Real-time status updates and evidence completeness</p>
            </div>
            <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                View All ({{ $totalBatches }}) →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3">Batch Code</th>
                        <th class="px-6 py-3">Tier</th>
                        <th class="px-6 py-3">District / Block</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Trainer</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Evidence</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($recentBatches as $batch)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">
                            <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:underline">
                                {{ $batch->batch_code }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-800">{{ $batch->tier?->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $batch->district?->name ?? 'State-Level' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $batch->block?->name ?? 'All Blocks' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $batch->scheduled_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $batch->trainers->first()?->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-6 py-4">
                            <x-status-badge :status="$batch->status" />
                        </td>
                        <td class="px-6 py-4">
                            @php $ev = $batch->evidence_status; @endphp
                            <div class="flex items-center space-x-1.5">
                                <div class="w-16 bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $ev['is_complete'] ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                         style="width: {{ ($ev['completed_count'] / 6) * 100 }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-600">{{ $ev['completed_count'] }}/6</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:text-blue-900 font-semibold">View</a>
                            <a href="{{ route('batches.evidence.index', $batch) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Uploads</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                            No training batches recorded yet. Schedule your first batch to begin tracking.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Status Doughnut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Verified', 'Submitted', 'In Progress', 'Scheduled', 'Planned'],
            datasets: [{
                data: [
                    {{ $verifiedBatches }},
                    {{ $submittedBatches }},
                    {{ $inProgressBatches }},
                    {{ $scheduledBatches }},
                    {{ $plannedBatches }}
                ],
                backgroundColor: ['#10b981', '#a855f7', '#f59e0b', '#0ea5e9', '#94a3b8'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '70%'
        }
    });

    // 2. Tier Bar Chart
    const tierCtx = document.getElementById('tierChart').getContext('2d');
    const tierLabels = {!! json_encode($tierStats->pluck('name')) !!};
    const tierCounts = {!! json_encode($tierStats->pluck('batches_count')) !!};

    new Chart(tierCtx, {
        type: 'bar',
        data: {
            labels: tierLabels,
            datasets: [{
                label: 'Conducted Batches',
                data: tierCounts,
                backgroundColor: '#3b82f6',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
});
</script>
@endpush
