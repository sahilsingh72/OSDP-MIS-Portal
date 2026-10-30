@extends('layouts.app', ['title' => 'Participant Feedback - OSDP MIS Portal'])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-16">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Training Operations</span>
                <span>/</span>
                <span class="text-blue-600 font-semibold">Feedback</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center space-x-3">
                <span>Participant Feedback Register</span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                    Feedback Form (Physical)
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-3xl">
                Select a training batch to transcribe and review physical Feedback evaluation sheets for enrolled trainees.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('batches.index') }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Batches Directory</span>
            </a>
            <a href="{{ route('participants.index') }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Participants Roster</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs">
        <form method="GET" action="{{ route('feedback.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-6 relative">
                <input type="text" name="batch_search" value="{{ request('batch_search') }}"
                    placeholder="Search by batch code, district, block, or venue..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-9 pr-3 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <div class="sm:col-span-4">
                <select name="tier_id" onchange="this.form.submit()"
                    class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 px-3 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
                    <option value="">All Training Tiers</option>
                    @foreach($tiers as $tier)
                        <option value="{{ $tier->id }}" {{ request('tier_id') == $tier->id ? 'selected' : '' }}>
                            {{ $tier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center space-x-2">
                <button type="submit"
                    class="w-full py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition text-center">
                    Filter
                </button>
                @if(request()->hasAny(['batch_search', 'tier_id']))
                <a href="{{ route('feedback.index') }}"
                    class="px-2.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs transition"
                    title="Clear filter">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Batches Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($batches as $b)
        @php
            $enrolled = $b->batch_participants_count ?? 0;
            $entered = $b->feedback_count ?? 0;
            $percent = $enrolled > 0 ? min(100, round(($entered / $enrolled) * 100)) : 0;
            $avgScore = $b->feedback?->q5_overall_avg ?? 0;
            $isComplete = $enrolled > 0 && $entered >= $enrolled;
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-md hover:border-blue-300 transition flex flex-col justify-between group">
            <div>
                <!-- Top Badges -->
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $b->tier?->name ?? 'Tier' }}
                    </span>

                    @if($isComplete)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 space-x-1">
                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>Fully Logged</span>
                        </span>
                    @elseif($entered > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            {{ $entered }}/{{ $enrolled }} Entered
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                            Pending Entry
                        </span>
                    @endif
                </div>

                <!-- Batch Title & Code -->
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition flex items-center justify-between">
                    <span>{{ $b->batch_code }}</span>
                    @if($avgScore > 0)
                    <span class="text-xs font-extrabold text-amber-500 flex items-center space-x-1">
                        <span>★</span>
                        <span>{{ number_format($avgScore, 1) }}</span>
                    </span>
                    @endif
                </h3>

                <!-- Location & Venue details -->
                <div class="mt-2 space-y-1 text-xs text-slate-600">
                    <div class="flex items-center space-x-1.5 text-slate-500">
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="truncate">{{ $b->district?->name }}{{ $b->block ? ' &bull; ' . $b->block->name : '' }}</span>
                    </div>
                    <div class="flex items-center space-x-1.5 text-slate-500">
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $b->scheduled_date ? $b->scheduled_date->format('d M Y') : 'Date TBD' }}</span>
                    </div>
                    <div class="flex items-center space-x-1.5 text-slate-500">
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span class="truncate">{{ $b->venue?->name ?? 'Venue unassigned' }}</span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between text-[11px] mb-1 font-medium">
                        <span class="text-slate-500">Feedback Entry</span>
                        <span class="font-bold {{ $isComplete ? 'text-emerald-600' : ($entered > 0 ? 'text-amber-600' : 'text-slate-500') }}">
                            {{ $entered }} / {{ $enrolled }} Enrolled ({{ $percent }}%)
                        </span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $isComplete ? 'bg-emerald-500' : ($entered > 0 ? 'bg-amber-500' : 'bg-slate-300') }}"
                            style="width: {{ $percent }}%;"></div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('feedback.batch', $b) }}"
                    class="w-full py-2 px-3 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold text-xs border border-slate-200 hover:border-blue-300 transition text-center flex items-center justify-center space-x-1.5 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Transcribe & View Feedback</span>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-2xs">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">No training batches found</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                No batches match your search criteria or are assigned within your operational district scope.
            </p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($batches->hasPages())
    <div class="pt-2">
        {{ $batches->links() }}
    </div>
    @endif

</div>
@endsection
