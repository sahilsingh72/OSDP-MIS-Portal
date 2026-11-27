@extends('layouts.app', ['title' => 'Assessment Entry - OSDP MIS Portal'])

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
                <span class="text-blue-600 font-semibold">Assessment Entry</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center space-x-3">
                <span>Pre & Post Assessment Entry</span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                    Physical Forms (Part A & B)
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-3xl">
                Transcribe trainee answers from physical Pre-Assessment (Part A) and Post-Assessment (Part B) forms against scanned batch PDFs.
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
            <a href="{{ route('feedback.index') }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span>Participant Feedback</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-2xs">
        <form method="GET" action="{{ route('assessments.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
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
                <a href="{{ route('assessments.index') }}"
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
            $preCount = $b->pre_completed_count ?? 0;
            $postCount = $b->post_completed_count ?? 0;
            $prePercent = $enrolled > 0 ? min(100, round(($preCount / $enrolled) * 100)) : 0;
            $postPercent = $enrolled > 0 ? min(100, round(($postCount / $enrolled) * 100)) : 0;
            $isBothComplete = $enrolled > 0 && $preCount >= $enrolled && $postCount >= $enrolled;
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-md hover:border-blue-300 transition flex flex-col justify-between group">
            <div>
                <!-- Top Badges -->
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $b->tier?->name ?? 'Tier' }}
                    </span>

                    @if($isBothComplete)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 space-x-1">
                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>Pre & Post Logged</span>
                        </span>
                    @elseif($preCount > 0 || $postCount > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            In Progress
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
                    <span class="text-xs font-normal text-slate-400 font-mono">{{ $b->scheduled_date ? $b->scheduled_date->format('d M Y') : 'Date TBD' }}</span>
                </h3>

                <!-- Location / District Info -->
                <div class="mt-2 text-xs text-slate-600 space-y-1">
                    <div class="flex items-center space-x-1.5 truncate">
                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate font-medium text-slate-800">{{ $b->district?->name ?? 'State Level' }}</span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="truncate text-slate-500">{{ $b->block?->name ?? 'District HQ' }}</span>
                    </div>

                    <div class="flex items-center space-x-1.5 truncate text-[11px] text-slate-500">
                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="truncate">{{ $b->venue?->name ?? 'Venue TBD' }}</span>
                    </div>
                </div>

                <!-- Progress Meters for Pre and Post -->
                <div class="mt-4 pt-3 border-t border-slate-100 space-y-2.5">
                    <!-- Pre-Assessment Bar -->
                    <div>
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="font-semibold text-slate-700 flex items-center space-x-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                <span>Part A (Pre-Assessment)</span>
                            </span>
                            <span class="font-bold {{ $preCount >= $enrolled && $enrolled > 0 ? 'text-emerald-600' : 'text-blue-600' }}">
                                {{ $preCount }} / {{ $enrolled }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $prePercent }}%"></div>
                        </div>
                    </div>

                    <!-- Post-Assessment Bar -->
                    <div>
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="font-semibold text-slate-700 flex items-center space-x-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                <span>Part B (Post-Assessment)</span>
                            </span>
                            <span class="font-bold {{ $postCount >= $enrolled && $enrolled > 0 ? 'text-emerald-600' : 'text-indigo-600' }}">
                                {{ $postCount }} / {{ $enrolled }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $postPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action Button -->
            <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">
                    {{ $enrolled }} Enrolled
                </span>
                <a href="{{ route('assessments.batch', $b) }}"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-2xs transition">
                    <span>Enter Assessments</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-slate-200">
            <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <h3 class="mt-3 text-sm font-bold text-slate-700">No training batches found</h3>
            <p class="mt-1 text-xs text-slate-400">No batches match the applied filters within your authorized scope.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($batches->hasPages())
    <div class="pt-4">
        {{ $batches->links() }}
    </div>
    @endif

</div>
@endsection
