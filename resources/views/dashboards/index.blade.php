@extends('layouts.app', ['title' => 'Executive Dashboard', 'headerTitle' => 'Monitoring Dashboard'])

@section('content')
<div class="space-y-6">

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Total Batches -->
        <a href="{{ route('batches.index') }}" class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-400/60 hover:-translate-y-0.5 transition duration-200 block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider group-hover:text-blue-600 transition">Total Batches</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900 group-hover:text-blue-600 transition">{{ number_format($totalBatches) }}</span>
                <span class="text-xs text-slate-500 ml-1">/ {{ $activePlan ? number_format($activePlan->target_batches) : '2,149' }} target</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ $scheduledBatches }} Scheduled</span>
                <span class="text-emerald-600 font-semibold">{{ $verifiedBatches }} Verified</span>
            </div>
        </a>

        <!-- Verified & Accepted -->
        <a href="{{ route('batches.index', ['status' => 'verified']) }}" class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-400/60 hover:-translate-y-0.5 transition duration-200 block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider group-hover:text-emerald-600 transition">Verified Batches</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
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
        </a>

        <!-- Participants Attendance -->
        <a href="{{ auth()->user()->can('participants.view') ? route('participants.index') : route('batches.index') }}" class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-purple-400/60 hover:-translate-y-0.5 transition duration-200 block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider group-hover:text-purple-600 transition">Trained Officials</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-extrabold text-slate-900 group-hover:text-purple-600 transition">{{ number_format($totalPresent) }}</span>
                <span class="text-xs text-slate-500 ml-1">officials present</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ number_format($totalRegistered) }} Registered</span>
                <span class="text-purple-600 font-semibold">{{ $totalRegistered > 0 ? round(($totalPresent / $totalRegistered) * 100, 1) : 0 }}% Turnout</span>
            </div>
        </a>

        <!-- Overdue SLA & Feedback -->
        <a href="{{ route('batches.index', ['overdue_only' => 1]) }}" class="group bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-rose-400/60 hover:-translate-y-0.5 transition duration-200 block cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider group-hover:text-rose-600 transition">Overdue (>48h)</span>
                <span class="p-2 rounded-xl {{ $overdueBatches > 0 ? 'bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white' : 'bg-slate-50 text-slate-400 group-hover:bg-slate-600 group-hover:text-white' }} transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold {{ $overdueBatches > 0 ? 'text-rose-600' : 'text-slate-900 group-hover:text-rose-600' }} transition">
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
        </a>

    </div>

    <!-- Analytics Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Status Breakdown Doughnut Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                            Batch Lifecycle Distribution
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Click any slice or badge to filter</p>
                    </div>
                    <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                        View Directory →
                    </a>
                </div>
                <div class="relative h-64 flex items-center justify-center cursor-pointer" title="Click chart segment to filter batches">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Clickable status legend badges -->
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <a href="{{ route('batches.index', ['status' => 'verified']) }}"
                   class="group flex items-center p-2 rounded-xl border border-slate-200/70 hover:border-emerald-300 hover:bg-emerald-50/70 transition"
                   title="Filter Verified Batches">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-2 flex-shrink-0 group-hover:scale-110 transition"></span>
                    <span class="text-slate-600 group-hover:text-emerald-900 font-medium">Verified: <strong class="text-slate-900 group-hover:text-emerald-800">{{ $verifiedBatches }}</strong></span>
                </a>
                <a href="{{ route('batches.index', ['status' => 'submitted']) }}"
                   class="group flex items-center p-2 rounded-xl border border-slate-200/70 hover:border-purple-300 hover:bg-purple-50/70 transition"
                   title="Filter Submitted Batches">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500 mr-2 flex-shrink-0 group-hover:scale-110 transition"></span>
                    <span class="text-slate-600 group-hover:text-purple-900 font-medium">Submitted: <strong class="text-slate-900 group-hover:text-purple-800">{{ $submittedBatches }}</strong></span>
                </a>
                <a href="{{ route('batches.index', ['status' => 'in_progress']) }}"
                   class="group flex items-center p-2 rounded-xl border border-slate-200/70 hover:border-amber-300 hover:bg-amber-50/70 transition"
                   title="Filter In Progress Batches">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 mr-2 flex-shrink-0 group-hover:scale-110 transition"></span>
                    <span class="text-slate-600 group-hover:text-amber-900 font-medium">In Progress: <strong class="text-slate-900 group-hover:text-amber-800">{{ $inProgressBatches }}</strong></span>
                </a>
                <a href="{{ route('batches.index', ['status' => 'scheduled']) }}"
                   class="group flex items-center p-2 rounded-xl border border-slate-200/70 hover:border-sky-300 hover:bg-sky-50/70 transition"
                   title="Filter Scheduled Batches">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500 mr-2 flex-shrink-0 group-hover:scale-110 transition"></span>
                    <span class="text-slate-600 group-hover:text-sky-900 font-medium">Scheduled: <strong class="text-slate-900 group-hover:text-sky-800">{{ $scheduledBatches }}</strong></span>
                </a>
            </div>
        </div>

        <!-- Training Tier Progress Bar Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                            Training Reach by Administrative Tier
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Click any bar to filter batches by tier</p>
                    </div>
                    <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition">
                        View All Batches →
                    </a>
                </div>
                <div class="relative h-64 cursor-pointer" title="Click bar to view batches for tier">
                    <canvas id="tierChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center space-x-1.5 text-xs text-slate-500">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Clicking any tier bar filters the Training Batches Directory for that administrative level.</span>
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
    const statusMap = ['verified', 'submitted', 'in_progress', 'scheduled', 'planned'];
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
                hoverBackgroundColor: ['#059669', '#9333ea', '#d97706', '#0284c7', '#64748b'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: function() {
                            return 'Click to filter batches →';
                        }
                    }
                }
            },
            cutout: '70%',
            onClick: (event, elements) => {
                if (elements && elements.length > 0) {
                    const index = elements[0].index;
                    const status = statusMap[index];
                    if (status) {
                        window.location.href = "{{ route('batches.index') }}?status=" + encodeURIComponent(status);
                    }
                }
            },
            onHover: (event, elements) => {
                event.native.target.style.cursor = elements && elements.length > 0 ? 'pointer' : 'default';
            }
        }
    });

    // 2. Tier Bar Chart
    const tierCtx = document.getElementById('tierChart').getContext('2d');
    const tierLabels = {!! json_encode($tierStats->pluck('name')) !!};
    const tierCounts = {!! json_encode($tierStats->pluck('batches_count')) !!};
    const tierIds = {!! json_encode($tierStats->pluck('id')) !!};

    new Chart(tierCtx, {
        type: 'bar',
        data: {
            labels: tierLabels,
            datasets: [{
                label: 'Conducted Batches',
                data: tierCounts,
                backgroundColor: '#3b82f6',
                hoverBackgroundColor: '#1d4ed8',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: function() {
                            return 'Click to view batches for this tier →';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            },
            onClick: (event, elements) => {
                if (elements && elements.length > 0) {
                    const index = elements[0].index;
                    const tierId = tierIds[index];
                    if (tierId) {
                        window.location.href = "{{ route('batches.index') }}?tier_id=" + encodeURIComponent(tierId);
                    }
                }
            },
            onHover: (event, elements) => {
                event.native.target.style.cursor = elements && elements.length > 0 ? 'pointer' : 'default';
            }
        }
    });
});
</script>
@endpush
