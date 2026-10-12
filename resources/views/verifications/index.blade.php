@extends('layouts.app', ['title' => 'Field Verifications', 'headerTitle' => 'Independent Field Audits'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Independent Field Monitoring Reports</h1>
            <p class="text-xs text-slate-500 mt-0.5">Third-party on-site verification of training sessions, participant attendance, banners, and kits</p>
        </div>

        @can('field-verifications.create')
        <a href="{{ route('verifications.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Log Field Visit Assessment</span>
        </a>
        @endcan
    </div>

    <!-- Verifications Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Visit Date</th>
                        <th class="px-6 py-3.5">Batch Code</th>
                        <th class="px-6 py-3.5">District / Block</th>
                        <th class="px-6 py-3.5">Monitor</th>
                        <th class="px-6 py-3.5">Verified Headcount</th>
                        <th class="px-6 py-3.5">Checks (Banner/Kits/Tea)</th>
                        <th class="px-6 py-3.5">Quality Rating</th>
                        <th class="px-6 py-3.5">Observations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($verifications as $v)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900">
                            {{ $v->visit_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 font-mono font-bold text-blue-600">
                            <a href="{{ route('batches.show', $v->batch) }}" class="hover:underline">
                                {{ $v->batch?->batch_code }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-slate-800">{{ $v->batch?->district?->name }}</span>
                            <span class="text-slate-400 text-[11px] block">{{ $v->batch?->block?->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $v->monitor?->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900">
                            {{ $v->verified_headcount }} present
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-1 text-[11px]">
                                <span class="px-1.5 py-0.5 rounded {{ $v->banner_displayed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">Banner</span>
                                <span class="px-1.5 py-0.5 rounded {{ $v->kits_distributed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">Kits</span>
                                <span class="px-1.5 py-0.5 rounded {{ $v->tea_lunch_provided ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">Refreshment</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $v->training_quality_rating === 'excellent' ? 'bg-emerald-100 text-emerald-800' : ($v->training_quality_rating === 'good' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                {{ $v->training_quality_rating }}
                            </span>
                        </td>
                        <td class="px-6 py-4 max-w-xs truncate text-slate-600">
                            {{ $v->observations }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            No independent field verification assessments logged yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($verifications->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $verifications->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
