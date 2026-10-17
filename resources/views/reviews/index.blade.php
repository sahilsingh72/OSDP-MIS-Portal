@extends('layouts.app', ['title' => 'Review Queue', 'headerTitle' => 'Evidence Review & Verification'])

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Review & Verification Queue</h1>
            <p class="text-xs text-slate-500 mt-0.5">District Coordinator pack checks, PMU compliance verification, and resubmissions</p>
        </div>
    </div>

    <!-- Review Queue Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5 w-16">S.No</th>
                        <th class="px-6 py-3.5">Batch Code</th>
                        <th class="px-6 py-3.5">Tier</th>
                        <th class="px-6 py-3.5">District</th>
                        <th class="px-6 py-3.5">Session Date</th>
                        <th class="px-6 py-3.5">Submitted On</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Evidence Status</th>
                        <th class="px-6 py-3.5 text-right">Review Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($pendingBatches as $batch)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                            {{ $pendingBatches->firstItem() ? ($pendingBatches->firstItem() + $loop->index) : $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                            <a href="{{ route('batches.show', $batch) }}" class="text-blue-600 hover:underline">
                                {{ $batch->batch_code }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            {{ $batch->tier?->name }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $batch->district?->name ?? 'State-Level' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $batch->scheduled_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                            {{ $batch->submitted_at ? $batch->submitted_at->diffForHumans() : 'Awaiting Submission' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-status-badge :status="$batch->status" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $ev = $batch->evidence_status; @endphp
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $ev['is_complete'] ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $ev['completed_count'] }}/6 Items
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('batches.show', $batch) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition">
                                Inspect & Decide →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                            No batches currently pending in the review queue. All submitted evidence packs are processed!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendingBatches->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $pendingBatches->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
