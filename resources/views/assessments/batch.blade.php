@extends('layouts.app', ['title' => 'Assessment Entry - ' . $batch->batch_code])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-24"
    x-data="assessmentManager({
        batchId: {{ $batch->id }},
        participants: {{ json_encode($batchParticipants) }},
        tierName: '{{ addslashes($batch->tier?->name ?? 'Tier') }}',
        venueName: '{{ addslashes($batch->venue?->name ?? '') }}',
        batchDate: '{{ $batch->scheduled_date ? $batch->scheduled_date->format('Y-m-d') : date('Y-m-d') }}',
        prePdfUrl: '{{ $preUpload ? route('evidence.download', $preUpload) : '' }}',
        postPdfUrl: '{{ $postUpload ? route('evidence.download', $postUpload) : '' }}'
    })">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('assessments.index') }}" class="hover:text-blue-600 transition">Assessment Entry</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $batch->batch_code }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center space-x-3">
                <span>{{ $batch->batch_code }}</span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $batch->tier?->name ?? 'Tier' }}
                </span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Pre & Post Assessments
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Transcribe and audit physical paper pre and post assessment answer sheets against participant PDFs.
            </p>
        </div>

        <div class="flex items-center space-x-2.5 flex-wrap">
            <a href="{{ route('assessments.index') }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                &larr; All Batches
            </a>
            <a href="{{ route('feedback.batch', $batch) }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span>Feedback Register</span>
            </a>
            <a href="{{ route('batches.evidence.index', $batch) }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Evidence Vault</span>
            </a>
            <a href="{{ route('batches.show', $batch) }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                Batch Details
            </a>
        </div>
    </div>

    <!-- Batch Summary Metadata Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block font-medium">District & Block</span>
                <span class="text-slate-900 font-bold text-sm block truncate">{{ $batch->district?->name }}</span>
                <span class="text-slate-500 text-[11px] block truncate">{{ $batch->block?->name ?? 'District HQ' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Certified Venue</span>
                <span class="text-slate-900 font-bold text-sm block truncate">{{ $batch->venue?->name ?? 'Venue TBD' }}</span>
                <span class="text-slate-500 text-[11px] block truncate">{{ $batch->venue?->address ?? '' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Scheduled Date</span>
                <span class="text-slate-900 font-bold text-sm block">
                    {{ $batch->scheduled_date ? $batch->scheduled_date->format('d/m/Y') : 'TBD' }}
                </span>
                <span class="text-slate-500 text-[11px] block">{{ $batch->training_mode ?? 'Offline' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Part A (Pre-Assessment)</span>
                <span class="font-bold text-sm block text-blue-600">
                    <span x-text="preEnteredCount">{{ $stats['pre_entered_count'] }}</span> / <span x-text="totalParticipants">{{ $stats['enrolled_count'] }}</span> Logged
                </span>
                <span class="text-slate-500 text-[11px] block">
                    Avg Awareness: <strong class="text-slate-800" x-text="preAvgScore">{{ number_format($stats['pre_avg_score'], 1) }}</strong> / 6.0
                </span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Part B (Post-Assessment)</span>
                <span class="font-bold text-sm block text-indigo-600">
                    <span x-text="postEnteredCount">{{ $stats['post_entered_count'] }}</span> / <span x-text="totalParticipants">{{ $stats['enrolled_count'] }}</span> Logged
                </span>
                <span class="text-slate-500 text-[11px] block">
                    Avg Knowledge: <strong class="text-slate-800" x-text="postAvgScore">{{ number_format($stats['post_avg_score'], 1) }}</strong> / 6.0
                </span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Average Learning Gain</span>
                <div class="flex items-center space-x-1.5 mt-0.5">
                    <span class="text-emerald-500 text-base font-bold">▲</span>
                    <span class="text-slate-900 font-extrabold text-base" x-text="learningGain">{{ number_format($stats['learning_gain'], 1) }}</span>
                    <span class="text-slate-400 text-xs font-normal">pts gain</span>
                </div>
            </div>
        </div>

        <!-- PDF Reference Quick Toolbar -->
        <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-slate-50/70 p-3 rounded-xl border border-slate-200/60">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-slate-700 flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    <span>Uploaded Participant Assessment PDFs:</span>
                </span>
            </div>

            <div class="flex items-center space-x-2 flex-wrap">
                @if($preUpload)
                    <a href="{{ route('evidence.download', $preUpload) }}" target="_blank"
                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg bg-white hover:bg-blue-50 text-blue-700 border border-blue-200 font-semibold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Pre-Assessment PDF (Part A)</span>
                    </a>
                @endif

                @if($postUpload)
                    <a href="{{ route('evidence.download', $postUpload) }}" target="_blank"
                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 font-semibold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Post-Assessment PDF (Part B)</span>
                    </a>
                @endif

                @if(!$preUpload && !$postUpload)
                    <span class="text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200 text-[11px] font-medium">
                        ⚠️ Assessment PDFs not uploaded yet in Evidence Vault.
                    </span>
                    <a href="{{ route('batches.evidence.index', $batch) }}" class="underline text-blue-600 hover:text-blue-800 font-semibold ml-1">
                        Upload PDFs
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Layout (Table + Optional Side-by-side PDF) -->
    <div :class="sidePdfOpen ? 'grid grid-cols-1 lg:grid-cols-12 gap-6' : ''">

        <!-- Enrolled Trainees Assessment Register Table -->
        <div :class="sidePdfOpen ? 'lg:col-span-7 space-y-6' : 'space-y-6'">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
                <!-- Toolbar & Filter -->
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Enrolled Participants Assessment Roster</h3>
                        <p class="text-xs text-slate-500">Record Part A (Pre) and Part B (Post) assessment responses per participant</p>
                    </div>

                    <div class="flex items-center space-x-3">
                        <!-- Search input -->
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Search trainee name / office..."
                                class="rounded-xl border border-slate-200 bg-slate-50/60 py-1.5 pl-8 pr-3 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none transition w-56">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <!-- Status filter pills -->
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/80 text-xs font-semibold">
                            <button type="button" @click="statusFilter = 'all'"
                                :class="statusFilter === 'all' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                                All (<span x-text="totalParticipants">{{ count($batchParticipants) }}</span>)
                            </button>
                            <button type="button" @click="statusFilter = 'pending_pre'"
                                :class="statusFilter === 'pending_pre' ? 'bg-white text-blue-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                                Pending Pre
                            </button>
                            <button type="button" @click="statusFilter = 'pending_post'"
                                :class="statusFilter === 'pending_post' ? 'bg-white text-indigo-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                                Pending Post
                            </button>
                            <button type="button" @click="statusFilter = 'completed'"
                                :class="statusFilter === 'completed' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                                Completed Both
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table with Blade initial render + Alpine live updates -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                            <tr>
                                <th class="px-5 py-3 w-12 text-center">S.No</th>
                                <th class="px-5 py-3">Participant Name</th>
                                <th class="px-5 py-3">Designation & Office</th>
                                <th class="px-5 py-3 text-center">Part A (Pre)</th>
                                <th class="px-5 py-3 text-center">Part B (Post)</th>
                                <th class="px-5 py-3 text-center">Learning Gain</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-700">
                            @forelse($batchParticipants as $idx => $bp)
                            @php
                                $ass = $bp->assessment;
                                $p = $bp->participant;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition" x-show="isParticipantVisible({{ $bp->id }})">
                                <td class="px-5 py-3.5 text-center font-bold text-slate-400">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-900">{{ $p?->name ?? 'Trainee' }}</div>
                                    @if($p?->phone)
                                        <div class="text-[11px] font-mono text-slate-500">{{ $p->phone }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">
                                    <div>{{ $p?->designation ?? 'N/A' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $p?->office_name ?? '' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span x-show="hasPreCompleted({{ $bp->id }})"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 space-x-1"
                                        style="{{ $ass && $ass->pre_completed ? '' : 'display: none;' }}">
                                        <span>Part A:</span>
                                        <span x-text="getPreScore({{ $bp->id }})">{{ $ass ? $ass->pre_score : 0 }}</span>
                                        <span>/ 6 Yes</span>
                                    </span>
                                    <span x-show="!hasPreCompleted({{ $bp->id }})"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                                        style="{{ $ass && $ass->pre_completed ? 'display: none;' : '' }}">
                                        Pending Pre
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span x-show="hasPostCompleted({{ $bp->id }})"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 space-x-1"
                                        style="{{ $ass && $ass->post_completed ? '' : 'display: none;' }}">
                                        <span>Part B:</span>
                                        <span x-text="getPostScore({{ $bp->id }})">{{ $ass ? $ass->post_score : 0 }}</span>
                                        <span>/ 6 Correct</span>
                                    </span>
                                    <span x-show="!hasPostCompleted({{ $bp->id }})"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                                        style="{{ $ass && $ass->post_completed ? 'display: none;' : '' }}">
                                        Pending Post
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span x-show="hasBothCompleted({{ $bp->id }})"
                                        class="inline-flex items-center space-x-1 font-bold text-xs"
                                        :class="getGain({{ $bp->id }}) >= 0 ? 'text-emerald-600' : 'text-slate-500'"
                                        style="{{ $ass && $ass->pre_completed && $ass->post_completed ? '' : 'display: none;' }}">
                                        <span x-text="getGain({{ $bp->id }}) >= 0 ? '▲ +' + getGain({{ $bp->id }}) : getGain({{ $bp->id }})">
                                            {{ $ass && $ass->pre_completed && $ass->post_completed ? (($ass->post_score - $ass->pre_score >= 0 ? '▲ +' : '') . ($ass->post_score - $ass->pre_score)) : '-' }}
                                        </span>
                                    </span>
                                    <span x-show="!hasBothCompleted({{ $bp->id }})"
                                        class="text-slate-400 text-xs italic"
                                        style="{{ $ass && $ass->pre_completed && $ass->post_completed ? 'display: none;' : '' }}">
                                        —
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    @can('assessments.manage')
                                        <button type="button"
                                            @click="openModalForParticipantId({{ $bp->id }})"
                                            class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-2xs transition inline-flex items-center space-x-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            <span x-text="hasAssessment({{ $bp->id }}) ? 'Edit Form' : 'Enter Answers'">
                                                {{ $ass ? 'Edit Form' : 'Enter Answers' }}
                                            </span>
                                        </button>
                                        <button type="button"
                                            x-show="hasAssessment({{ $bp->id }})"
                                            style="{{ $ass ? '' : 'display: none;' }}"
                                            @click="deleteAssessmentById({{ $bp->id }})"
                                            class="p-1.5 ml-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                            title="Delete assessment">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">View Only</span>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    No enrolled participants found in this training batch. Please enroll participants first.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Optional Side-by-Side PDF Preview Pane -->
        <div x-show="sidePdfOpen" class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col space-y-3 sticky top-6 h-[85vh]">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-sm text-slate-900">PDF Reference Viewer</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    @if($preUpload)
                    <button type="button" @click="activePdfTab = 'pre'"
                        :class="activePdfTab === 'pre' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                        class="px-2.5 py-1 text-xs rounded-lg transition cursor-pointer">
                        Part A (Pre)
                    </button>
                    @endif
                    @if($postUpload)
                    <button type="button" @click="activePdfTab = 'post'"
                        :class="activePdfTab === 'post' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700'"
                        class="px-2.5 py-1 text-xs rounded-lg transition cursor-pointer">
                        Part B (Post)
                    </button>
                    @endif
                    <button type="button" @click="sidePdfOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- PDF Embed Frame -->
            <div class="flex-1 w-full bg-slate-100 rounded-xl overflow-hidden relative border border-slate-200">
                <template x-if="activePdfTab === 'pre' && prePdfUrl">
                    <iframe :src="prePdfUrl" class="w-full h-full border-0"></iframe>
                </template>
                <template x-if="activePdfTab === 'post' && postPdfUrl">
                    <iframe :src="postPdfUrl" class="w-full h-full border-0"></iframe>
                </template>
                <template x-if="(!prePdfUrl && activePdfTab === 'pre') || (!postPdfUrl && activePdfTab === 'post')">
                    <div class="flex items-center justify-center h-full p-6 text-center text-slate-400 text-xs">
                        PDF file is not available for this assessment type.
                    </div>
                </template>
            </div>
        </div>

    </div>

    <!-- Assessment Entry Modal (Pre & Post Questions from PDF) -->
    <div x-show="modalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
        style="display: none;"
        @keydown.escape.window="modalOpen = false">

        <div class="bg-white rounded-2xl w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] transition-all"
             :class="modalSplitPdf ? 'max-w-6xl' : 'max-w-3xl'"
             @click.away="modalOpen = false">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                        <span x-text="activeParticipant ? activeParticipant.name.charAt(0).toUpperCase() : 'T'">T</span>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="font-bold text-slate-900 text-base" x-text="activeParticipant ? activeParticipant.name : ''">Participant</h3>
                            <span class="text-xs px-2 py-0.5 rounded-md font-mono bg-slate-200 text-slate-700 font-semibold"
                                  x-text="activeParticipant && activeParticipant.phone ? activeParticipant.phone : 'No Phone'"></span>
                        </div>
                        <p class="text-xs text-slate-500" x-text="activeParticipant ? ((activeParticipant.designation || 'Trainee') + ' • ' + (activeParticipant.office_name || '')) : ''"></p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    @if($preUpload || $postUpload)
                    <button type="button" @click="modalSplitPdf = !modalSplitPdf"
                        :class="modalSplitPdf ? 'bg-slate-800 text-white' : 'bg-white text-slate-700 hover:bg-slate-100'"
                        class="px-2.5 py-1 text-xs rounded-lg border border-slate-300 font-semibold transition cursor-pointer flex items-center space-x-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span x-text="modalSplitPdf ? 'Hide PDF' : 'View PDF Alongside'">View PDF Alongside</span>
                    </button>
                    @endif
                    <button type="button" @click="modalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Tab Navigation (Part A Pre-Assessment / Part B Post-Assessment) -->
            <div class="px-6 py-2.5 bg-white border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/80 text-xs font-semibold">
                    <button type="button" @click="activeModalTab = 'pre'"
                        :class="activeModalTab === 'pre' ? 'bg-blue-600 text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-1.5 rounded-lg transition cursor-pointer flex items-center space-x-1.5">
                        <span>Part A: Pre-Assessment</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                              :class="activeModalTab === 'pre' ? 'bg-blue-800 text-white' : 'bg-slate-200 text-slate-700'"
                              x-text="computedPreScore + '/6'"></span>
                    </button>
                    <button type="button" @click="activeModalTab = 'post'"
                        :class="activeModalTab === 'post' ? 'bg-indigo-600 text-white shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-1.5 rounded-lg transition cursor-pointer flex items-center space-x-1.5">
                        <span>Part B: Post-Assessment</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                              :class="activeModalTab === 'post' ? 'bg-indigo-800 text-white' : 'bg-slate-200 text-slate-700'"
                              x-text="computedPostScore + '/6'"></span>
                    </button>
                </div>

                <div class="flex items-center space-x-3 text-xs">
                    <span class="text-slate-500">Live Score:</span>
                    <span class="font-bold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200">
                        Pre: <span x-text="computedPreScore">0</span> / 6 Yes
                    </span>
                    <span class="font-bold px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Post: <span x-text="computedPostScore">0</span> / 6 Correct
                    </span>
                </div>
            </div>

            <!-- Modal Body (Questions + Optional Split PDF) -->
            <div class="overflow-y-auto flex-1 p-6" :class="modalSplitPdf ? 'grid grid-cols-1 md:grid-cols-2 gap-6' : ''">

                <!-- Left Column: PDF Frame when split mode active -->
                <div x-show="modalSplitPdf" class="bg-slate-100 rounded-xl overflow-hidden border border-slate-200 h-[65vh] flex flex-col">
                    <div class="bg-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-700 flex items-center justify-between">
                        <span x-text="activeModalTab === 'pre' ? 'Part A Pre-Assessment PDF' : 'Part B Post-Assessment PDF'"></span>
                        <span class="text-slate-500 font-normal">Scroll PDF to view answers</span>
                    </div>
                    <div class="flex-1 w-full relative">
                        <template x-if="activeModalTab === 'pre' && prePdfUrl">
                            <iframe :src="prePdfUrl" class="w-full h-full border-0"></iframe>
                        </template>
                        <template x-if="activeModalTab === 'post' && postPdfUrl">
                            <iframe :src="postPdfUrl" class="w-full h-full border-0"></iframe>
                        </template>
                        <template x-if="(!prePdfUrl && activeModalTab === 'pre') || (!postPdfUrl && activeModalTab === 'post')">
                            <div class="flex items-center justify-center h-full p-6 text-center text-slate-400 text-xs">
                                PDF file is not available for this assessment type.
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Right Column (or Full Width): Questions Form -->
                <div class="space-y-6">

                    <!-- PART A: PRE-ASSESSMENT QUESTIONS -->
                    <div x-show="activeModalTab === 'pre'" class="space-y-4">
                        <div class="bg-blue-50/70 p-3 rounded-xl border border-blue-100 text-xs text-blue-900">
                            <span class="font-bold block">PART A: PRE-ASSESSMENT (Before we begin: what do you know today?)</span>
                            <span class="text-blue-700 text-[11px]">Tick (✓) Yes or No for each question. This is not a test. Handed to trainer at start of session.</span>
                        </div>

                        @foreach($preQuestions as $qNum => $qText)
                        <div class="p-3.5 rounded-xl border border-slate-200/80 bg-white shadow-2xs hover:border-blue-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="text-xs text-slate-800 pr-2">
                                <span class="font-bold text-slate-900 mr-1.5">{{ $qNum }}.</span>
                                <span>{{ $qText }}</span>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0">
                                <button type="button"
                                    @click="form.pre_q{{ $qNum }} = true"
                                    :class="form.pre_q{{ $qNum }} === true ? 'bg-emerald-600 text-white font-bold ring-2 ring-emerald-600/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                    ✓ Yes
                                </button>
                                <button type="button"
                                    @click="form.pre_q{{ $qNum }} = false"
                                    :class="form.pre_q{{ $qNum }} === false ? 'bg-slate-700 text-white font-bold ring-2 ring-slate-700/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-3.5 py-1.5 rounded-lg text-xs transition cursor-pointer">
                                    ✗ No
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- PART B: POST-ASSESSMENT QUESTIONS -->
                    <div x-show="activeModalTab === 'post'" class="space-y-4">
                        <div class="bg-indigo-50/70 p-3 rounded-xl border border-indigo-100 text-xs text-indigo-900">
                            <span class="font-bold block">PART B: POST-ASSESSMENT (After the training: check what you learned)</span>
                            <span class="text-indigo-700 text-[11px]">Tick (✓) one answer for each question. Handed to trainer at end of session.</span>
                        </div>

                        @foreach($postQuestions as $qNum => $qMeta)
                        <div class="p-4 rounded-xl border border-slate-200/80 bg-white shadow-2xs hover:border-indigo-300 transition space-y-2.5">
                            <div class="text-xs font-bold text-slate-900">
                                <span>{{ $qNum }}. {{ $qMeta['question'] }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                @foreach($qMeta['options'] as $optKey => $optVal)
                                <button type="button"
                                    @click="form.post_q{{ $qNum }} = '{{ $optKey }}'"
                                    :class="form.post_q{{ $qNum }} === '{{ $optKey }}' ? 'bg-indigo-600 text-white font-semibold shadow-xs ring-2 ring-indigo-600/30' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200'"
                                    class="p-2.5 rounded-xl text-left transition flex items-start space-x-2 cursor-pointer">
                                    <span class="w-5 h-5 rounded-md flex items-center justify-center font-bold text-[11px] flex-shrink-0"
                                          :class="form.post_q{{ $qNum }} === '{{ $optKey }}' ? 'bg-indigo-800 text-white' : 'bg-slate-200 text-slate-800'">
                                        {{ $optKey }}
                                    </span>
                                    <span class="leading-tight">{{ $optVal }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
                <button type="button" @click="modalOpen = false"
                    class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 transition cursor-pointer">
                    Cancel
                </button>

                <div class="flex items-center space-x-2">
                    <button type="button"
                        @click="saveAssessment(false)"
                        :disabled="isSubmitting"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition cursor-pointer flex items-center space-x-1.5 disabled:opacity-50">
                        <span x-show="!isSubmitting">Save Assessment</span>
                        <span x-show="isSubmitting">Saving...</span>
                    </button>

                    <button type="button"
                        @click="saveAssessment(true)"
                        :disabled="isSubmitting"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition cursor-pointer flex items-center space-x-1.5 disabled:opacity-50">
                        <span>Save & Next Trainee &rarr;</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Alpine Manager Script for Live Updates & Fast Transcription -->
<script>
function assessmentManager(config) {
    return {
        batchId: config.batchId,
        participants: config.participants,
        prePdfUrl: config.prePdfUrl,
        postPdfUrl: config.postPdfUrl,
        sidePdfOpen: false,
        activePdfTab: 'pre',
        modalOpen: false,
        modalSplitPdf: false,
        activeModalTab: 'pre',
        activeParticipantId: null,
        activeBatchParticipantId: null,
        searchQuery: '',
        statusFilter: 'all',
        isSubmitting: false,

        form: {
            pre_q1: null,
            pre_q2: null,
            pre_q3: null,
            pre_q4: null,
            pre_q5: null,
            pre_q6: null,
            post_q1: null,
            post_q2: null,
            post_q3: null,
            post_q4: null,
            post_q5: null,
            post_q6: null,
        },

        get totalParticipants() {
            return this.participants.length;
        },

        get preEnteredCount() {
            return this.participants.filter(bp => bp.assessment && bp.assessment.pre_completed).length;
        },

        get postEnteredCount() {
            return this.participants.filter(bp => bp.assessment && bp.assessment.post_completed).length;
        },

        get preAvgScore() {
            const list = this.participants.filter(bp => bp.assessment && bp.assessment.pre_completed);
            if (list.length === 0) return '0.0';
            const sum = list.reduce((acc, bp) => acc + (bp.assessment.pre_score || 0), 0);
            return (sum / list.length).toFixed(1);
        },

        get postAvgScore() {
            const list = this.participants.filter(bp => bp.assessment && bp.assessment.post_completed);
            if (list.length === 0) return '0.0';
            const sum = list.reduce((acc, bp) => acc + (bp.assessment.post_score || 0), 0);
            return (sum / list.length).toFixed(1);
        },

        get learningGain() {
            const pre = parseFloat(this.preAvgScore);
            const post = parseFloat(this.postAvgScore);
            if (post === 0) return '0.0';
            return (post - pre).toFixed(1);
        },

        get activeParticipant() {
            if (!this.activeBatchParticipantId) return null;
            const bp = this.participants.find(p => p.id === this.activeBatchParticipantId);
            return bp ? bp.participant : null;
        },

        get computedPreScore() {
            let score = 0;
            for (let i = 1; i <= 6; i++) {
                if (this.form['pre_q' + i] === true) score++;
            }
            return score;
        },

        get computedPostScore() {
            let score = 0;
            const correctAnswers = { 1: 'B', 2: 'C', 3: 'B', 4: 'C', 5: 'A', 6: 'C' };
            for (let i = 1; i <= 6; i++) {
                const val = this.form['post_q' + i];
                if (val && val.toUpperCase() === correctAnswers[i]) score++;
            }
            return score;
        },

        isParticipantVisible(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            if (!bp) return false;

            // Search filter
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase();
                const name = (bp.participant && bp.participant.name ? bp.participant.name : '').toLowerCase();
                const office = (bp.participant && bp.participant.office_name ? bp.participant.office_name : '').toLowerCase();
                const desig = (bp.participant && bp.participant.designation ? bp.participant.designation : '').toLowerCase();
                if (!name.includes(q) && !office.includes(q) && !desig.includes(q)) return false;
            }

            // Status filter
            const hasPre = bp.assessment && bp.assessment.pre_completed;
            const hasPost = bp.assessment && bp.assessment.post_completed;

            if (this.statusFilter === 'pending_pre') return !hasPre;
            if (this.statusFilter === 'pending_post') return !hasPost;
            if (this.statusFilter === 'completed') return hasPre && hasPost;

            return true;
        },

        hasAssessment(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            return !!(bp && bp.assessment);
        },

        hasPreCompleted(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            return !!(bp && bp.assessment && bp.assessment.pre_completed);
        },

        hasPostCompleted(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            return !!(bp && bp.assessment && bp.assessment.post_completed);
        },

        hasBothCompleted(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            return !!(bp && bp.assessment && bp.assessment.pre_completed && bp.assessment.post_completed);
        },

        getPreScore(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            return bp && bp.assessment ? bp.assessment.pre_score : 0;
        },

        getPostScore(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            return bp && bp.assessment ? bp.assessment.post_score : 0;
        },

        getGain(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            if (!bp || !bp.assessment) return 0;
            return (bp.assessment.post_score || 0) - (bp.assessment.pre_score || 0);
        },

        openModalForParticipantId(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            if (!bp) return;

            this.activeBatchParticipantId = bpId;
            const ass = bp.assessment;

            // Populate form
            this.form.pre_q1 = ass && ass.pre_q1 !== null ? !!ass.pre_q1 : null;
            this.form.pre_q2 = ass && ass.pre_q2 !== null ? !!ass.pre_q2 : null;
            this.form.pre_q3 = ass && ass.pre_q3 !== null ? !!ass.pre_q3 : null;
            this.form.pre_q4 = ass && ass.pre_q4 !== null ? !!ass.pre_q4 : null;
            this.form.pre_q5 = ass && ass.pre_q5 !== null ? !!ass.pre_q5 : null;
            this.form.pre_q6 = ass && ass.pre_q6 !== null ? !!ass.pre_q6 : null;

            this.form.post_q1 = ass ? (ass.post_q1 || null) : null;
            this.form.post_q2 = ass ? (ass.post_q2 || null) : null;
            this.form.post_q3 = ass ? (ass.post_q3 || null) : null;
            this.form.post_q4 = ass ? (ass.post_q4 || null) : null;
            this.form.post_q5 = ass ? (ass.post_q5 || null) : null;
            this.form.post_q6 = ass ? (ass.post_q6 || null) : null;

            // Auto select tab: if pre is pending, show pre; else if post is pending, show post
            if (ass && ass.pre_completed && !ass.post_completed) {
                this.activeModalTab = 'post';
            } else {
                this.activeModalTab = 'pre';
            }

            this.modalOpen = true;
        },

        async saveAssessment(openNext = false) {
            if (!this.activeBatchParticipantId) return;
            this.isSubmitting = true;

            const url = `/batches/${this.batchId}/participants/${this.activeBatchParticipantId}/assessments`;
            const payload = {
                _token: '{{ csrf_token() }}',
                pre_q1: this.form.pre_q1,
                pre_q2: this.form.pre_q2,
                pre_q3: this.form.pre_q3,
                pre_q4: this.form.pre_q4,
                pre_q5: this.form.pre_q5,
                pre_q6: this.form.pre_q6,
                post_q1: this.form.post_q1,
                post_q2: this.form.post_q2,
                post_q3: this.form.post_q3,
                post_q4: this.form.post_q4,
                post_q5: this.form.post_q5,
                post_q6: this.form.post_q6,
            };

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (data.success && data.assessment) {
                    // Update local state
                    const bp = this.participants.find(p => p.id === this.activeBatchParticipantId);
                    if (bp) {
                        bp.assessment = data.assessment;
                    }

                    if (openNext) {
                        // Find next participant in the list
                        const currentIndex = this.participants.findIndex(p => p.id === this.activeBatchParticipantId);
                        if (currentIndex !== -1 && currentIndex + 1 < this.participants.length) {
                            const nextBp = this.participants[currentIndex + 1];
                            this.openModalForParticipantId(nextBp.id);
                        } else {
                            this.modalOpen = false;
                        }
                    } else {
                        this.modalOpen = false;
                    }
                } else {
                    alert(data.message || 'Error saving assessment answers.');
                }
            } catch (err) {
                console.error(err);
                alert('An unexpected error occurred while saving.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async deleteAssessmentById(bpId) {
            const bp = this.participants.find(p => p.id === bpId);
            if (!bp || !bp.assessment) return;

            if (!confirm(`Are you sure you want to delete the assessment records for ${bp.participant?.name || 'this participant'}?`)) {
                return;
            }

            const url = `/batches/${this.batchId}/assessments/${bp.assessment.id}`;
            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    bp.assessment = null;
                } else {
                    alert(data.message || 'Error deleting assessment.');
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred while deleting.');
            }
        }
    };
}
</script>
@endsection
