@extends('layouts.app', ['title' => 'Batch Feedback - ' . $batch->batch_code])

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-24"
    x-data="participantFeedbackManager({
        batchId: {{ $batch->id }},
        participants: {{ json_encode($batchParticipants) }},
        tierName: '{{ addslashes($batch->tier?->name ?? 'Tier') }}',
        venueName: '{{ addslashes($batch->venue?->name ?? '') }}',
        batchDate: '{{ $batch->scheduled_date ? $batch->scheduled_date->format('Y-m-d') : date('Y-m-d') }}'
    })">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('feedback.index') }}" class="hover:text-blue-600 transition">Batch Feedback</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $batch->batch_code }}</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center space-x-3">
                <span>{{ $batch->batch_code }}</span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                    {{ $batch->tier?->name ?? 'Tier' }}
                </span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                    Physical Feedback
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Transcribe and audit physical paper feedback questionnaires for trainees enrolled in this batch.
            </p>
        </div>

        <div class="flex items-center space-x-2.5 flex-wrap">
            <a href="{{ route('feedback.index') }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                &larr; All Batches
            </a>
            <a href="{{ route('batches.attendance.show', $batch) }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Attendance</span>
            </a>
            <a href="{{ route('batches.show', $batch) }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                Batch Details
            </a>
        </div>
    </div>

    <!-- Batch Summary Metadata Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 text-xs">
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
                <span class="text-slate-400 block font-medium">Trainer Assigned</span>
                <span class="text-slate-900 font-bold text-sm block truncate">
                    {{ $batch->trainers->first()?->name ?? 'Lead Trainer' }}
                </span>
                <span class="text-slate-500 text-[11px] block">Master Trainer</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Feedback</span>
                <span class="font-bold text-sm block text-blue-600">
                    <span x-text="enteredCount">{{ $stats['entered_count'] }}</span> / <span x-text="totalParticipants">{{ $stats['enrolled_count'] }}</span> Feedbacked
                </span>
                <span class="text-slate-500 text-[11px] block">
                    <span x-text="completionPercentage">{{ $stats['completion_rate'] }}</span>% Completed
                </span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Average Evaluation</span>
                <div class="flex items-center space-x-1.5 mt-0.5">
                    <span class="text-amber-500 text-base">★</span>
                    <span class="text-slate-900 font-extrabold text-base" x-text="overallAverage">{{ number_format($stats['overall_avg'], 1) }}</span>
                    <span class="text-slate-400 text-xs font-normal">/ 5.0</span>
                </div>
            </div>
        </div>

        <!-- 5-Star Metric Cards Breakdown (Form 1 Questions Q1-Q5) -->
        <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/80">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">1. Content Clarity</span>
                <div class="flex items-baseline space-x-1 mt-1">
                    <span class="text-base font-extrabold text-slate-900" x-text="q1Average">{{ number_format($stats['q1_avg'], 1) }}</span>
                    <span class="text-[11px] text-slate-400">/ 5.0</span>
                </div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/80">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">2. Trainer Knowledge</span>
                <div class="flex items-baseline space-x-1 mt-1">
                    <span class="text-base font-extrabold text-slate-900" x-text="q2Average">{{ number_format($stats['q2_avg'], 1) }}</span>
                    <span class="text-[11px] text-slate-400">/ 5.0</span>
                </div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/80">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">3. Work Relevance</span>
                <div class="flex items-baseline space-x-1 mt-1">
                    <span class="text-base font-extrabold text-slate-900" x-text="q3Average">{{ number_format($stats['q3_avg'], 1) }}</span>
                    <span class="text-[11px] text-slate-400">/ 5.0</span>
                </div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/80">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">4. Handbook Use</span>
                <div class="flex items-baseline space-x-1 mt-1">
                    <span class="text-base font-extrabold text-slate-900" x-text="q4Average">{{ number_format($stats['q4_avg'], 1) }}</span>
                    <span class="text-[11px] text-slate-400">/ 5.0</span>
                </div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/80">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">5. Overall Satisfaction</span>
                <div class="flex items-baseline space-x-1 mt-1">
                    <span class="text-base font-extrabold text-slate-900" x-text="q5Average">{{ number_format($stats['q5_avg'], 1) }}</span>
                    <span class="text-[11px] text-slate-400">/ 5.0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrolled Trainees Feedback Register Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <!-- Toolbar & Filter -->
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Enrolled Participants Feedback Register</h3>
                <p class="text-xs text-slate-500">Mark feedback per participant who attended this training batch</p>
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
                    <button type="button" @click="statusFilter = 'pending'"
                        :class="statusFilter === 'pending' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                        Pending (<span x-text="pendingCount">{{ $stats['pending_count'] }}</span>)
                    </button>
                    <button type="button" @click="statusFilter = 'logged'"
                        :class="statusFilter === 'logged' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-2.5 py-1 rounded-lg transition cursor-pointer">
                        Logged (<span x-text="enteredCount">{{ $stats['entered_count'] }}</span>)
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
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-center">Ratings (Q1 - Q5)</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($batchParticipants as $idx => $bp)
                    @php
                        $fb = $bp->feedback;
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
                            <span x-show="hasFeedback({{ $bp->id }})"
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 space-x-1"
                                style="{{ $fb ? '' : 'display: none;' }}">
                                <span>★</span>
                                <span x-text="getRating({{ $bp->id }})">{{ $fb ? number_format($fb->average_rating, 1) : '' }}</span>
                                <span class="text-emerald-500">&bull; Logged</span>
                            </span>
                            <span x-show="!hasFeedback({{ $bp->id }})"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                                style="{{ $fb ? 'display: none;' : '' }}">
                                Pending 
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div x-show="hasFeedback({{ $bp->id }})"
                                class="inline-flex items-center space-x-1 text-[11px] font-semibold bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200"
                                style="{{ $fb ? '' : 'display: none;' }}">
                                <span title="1. Content Clarity" class="text-slate-700">C:<strong x-text="getScore({{ $bp->id }}, 'q1_clarity')">{{ $fb?->q1_clarity }}</strong></span>
                                <span class="text-slate-300">|</span>
                                <span title="2. Trainer Knowledge" class="text-slate-700">K:<strong x-text="getScore({{ $bp->id }}, 'q2_knowledge')">{{ $fb?->q2_knowledge }}</strong></span>
                                <span class="text-slate-300">|</span>
                                <span title="3. Work Relevance" class="text-slate-700">R:<strong x-text="getScore({{ $bp->id }}, 'q3_relevance')">{{ $fb?->q3_relevance }}</strong></span>
                                <span class="text-slate-300">|</span>
                                <span title="4. Handbook Usefulness" class="text-slate-700">H:<strong x-text="getScore({{ $bp->id }}, 'q4_handbook')">{{ $fb?->q4_handbook }}</strong></span>
                                <span class="text-slate-300">|</span>
                                <span title="5. Overall Satisfaction" class="text-slate-700">O:<strong x-text="getScore({{ $bp->id }}, 'q5_overall')">{{ $fb?->q5_overall }}</strong></span>
                            </div>
                            <span x-show="!hasFeedback({{ $bp->id }})"
                                class="text-slate-400 text-xs italic"
                                style="{{ $fb ? 'display: none;' : '' }}">
                                — Not entered —
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            @can('feedback.manage')
                                <button type="button"
                                    x-show="!hasFeedback({{ $bp->id }})"
                                    style="{{ $fb ? 'display: none;' : '' }}"
                                    @click="openModalForParticipantId({{ $bp->id }})"
                                    class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-2xs transition inline-flex items-center space-x-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Mark feedback</span>
                                </button>
                                <div x-show="hasFeedback({{ $bp->id }})"
                                    style="{{ $fb ? '' : 'display: none;' }}"
                                    class="inline-flex items-center space-x-1.5">
                                    <button type="button"
                                        @click="openModalForParticipantId({{ $bp->id }})"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-semibold text-xs border border-slate-200 hover:border-blue-300 transition cursor-pointer">
                                        Edit Form
                                    </button>
                                    <button type="button"
                                        @click="deleteFeedbackById({{ $bp->id }})"
                                        class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                        title="Delete feedback">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
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

    <!-- Scoped CSS for Form 1 Modal & Rating Buttons -->
    <style>
        /* Bulletproof hidden radio input: zero-dimension, invisible to eye, fully valid in DOM */
        .form1-radio-hidden {
            position: absolute !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            pointer-events: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            clip: rect(0, 0, 0, 0) !important;
        }

        /* Sleek custom scrollbar for modal body */
        .feedback-modal-scroll {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        .feedback-modal-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .feedback-modal-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .feedback-modal-scroll::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 9999px;
        }
        .feedback-modal-scroll::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Rating circular button base */
        .rating-pill-btn {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8125rem;
            font-weight: 700;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            user-select: none;
            box-sizing: border-box;
        }

        /* Default unselected state */
        .rating-pill-default {
            background-color: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .rating-pill-default:hover {
            background-color: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
            transform: scale(1.06);
        }

        /* Active Selected State 5 = Excellent (Emerald) */
        .rating-pill-active-5 {
            background-color: #059669 !important;
            color: #ffffff !important;
            border: 2px solid #047857 !important;
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.4) !important;
            transform: scale(1.12);
        }
        .rating-cell-active-5 {
            background-color: rgba(236, 253, 245, 0.85) !important;
        }

        /* Active Selected State 4 = Very Good (Blue) */
        .rating-pill-active-4 {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border: 2px solid #1d4ed8 !important;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4) !important;
            transform: scale(1.12);
        }
        .rating-cell-active-4 {
            background-color: rgba(239, 246, 255, 0.85) !important;
        }

        /* Active Selected State 3 = Good (Amber) */
        .rating-pill-active-3 {
            background-color: #d97706 !important;
            color: #ffffff !important;
            border: 2px solid #b45309 !important;
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.4) !important;
            transform: scale(1.12);
        }
        .rating-cell-active-3 {
            background-color: rgba(254, 243, 199, 0.85) !important;
        }

        /* Active Selected State 2 = Fair (Orange) */
        .rating-pill-active-2 {
            background-color: #ea580c !important;
            color: #ffffff !important;
            border: 2px solid #c2410c !important;
            box-shadow: 0 4px 10px rgba(234, 88, 12, 0.4) !important;
            transform: scale(1.12);
        }
        .rating-cell-active-2 {
            background-color: rgba(255, 237, 213, 0.85) !important;
        }

        /* Active Selected State 1 = Poor (Rose) */
        .rating-pill-active-1 {
            background-color: #e11d48 !important;
            color: #ffffff !important;
            border: 2px solid #be123c !important;
            box-shadow: 0 4px 10px rgba(225, 29, 72, 0.4) !important;
            transform: scale(1.12);
        }
        .rating-cell-active-1 {
            background-color: rgba(255, 228, 230, 0.85) !important;
        }
    </style>

    <!-- Authentic Physical Form 1 Transcription Modal -->
    <div x-show="modalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4 md:p-6"
        style="display: none;"
        @keydown.escape.window="modalOpen = false">

        <div class="relative w-full max-w-4xl max-h-[92vh] flex flex-col rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden"
            @click.outside="modalOpen = false">

            <!-- 1. Pinned Header Banner (Never scrolls away) -->
            <div class="shrink-0 px-6 py-3.5 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex items-center justify-between border-b border-slate-700">
                <div class="flex items-center space-x-3.5">
                    <div class="w-16 h-10 rounded-2xl bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center shadow-md shrink-0">
                        <span>FORM</span>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold tracking-tight">PARTICIPANT FEEDBACK FORM</h2>
                        <p class="text-xs text-slate-300">Odisha State Data Policy 2.0 &bull; Capacity Building, Training and Awareness</p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Previous Trainee Button -->
                    <button type="button" @click="goToPreviousTrainee()" :disabled="currentParticipantIndex <= 0"
                        class="p-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition border border-slate-700 cursor-pointer"
                        title="Previous Trainee">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <!-- Trainee Index Counter -->
                    <span class="text-xs text-slate-300 font-semibold px-2.5 py-1 rounded-xl bg-slate-800/90 border border-slate-700 whitespace-nowrap"
                        x-text="'Trainee ' + (currentParticipantIndex + 1) + ' of ' + totalParticipants"></span>

                    <!-- Next Trainee Button -->
                    <button type="button" @click="goToNextTrainee()" :disabled="currentParticipantIndex >= totalParticipants - 1"
                        class="p-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed transition border border-slate-700 cursor-pointer"
                        title="Next Trainee">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-white p-1.5 rounded-xl hover:bg-slate-800 transition ml-1 cursor-pointer" title="Close">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- 2. Form Content Body (Smooth, Complete Vertical Scrolling) -->
            <form id="feedbackEntryForm" @submit.prevent="submitFeedbackForm(false)" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5 feedback-modal-scroll">
                @csrf

                <!-- Physical Form Prompt Notice -->
                <div class="px-4 py-2.5 bg-amber-50/80 border border-amber-200/90 rounded-2xl text-xs text-amber-900 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="italic font-medium">Please fill this form at the end of the session and give it to your trainer. It takes two minutes.</span>
                    </div>
                    <span class="text-[11px] font-bold text-amber-800 bg-amber-100/80 px-2.5 py-0.5 rounded-lg border border-amber-300 shrink-0 hidden sm:inline">
                        Paper Form Transcription
                    </span>
                </div>

                <!-- Form 1 Header Box (Grid replica of physical sheet header) -->
                <div class="border border-slate-300 rounded-2xl p-4 bg-slate-50/70 space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <span class="block font-bold text-slate-600 uppercase text-[10px] tracking-wider mb-1">Training Tier</span>
                            <div class="p-2 rounded-xl bg-white border border-slate-200 font-bold text-slate-800 truncate"
                                x-text="currentParticipant?.batch?.tier?.name || defaultTier"></div>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block font-bold text-slate-600 uppercase text-[10px] tracking-wider mb-1">Venue</span>
                            <div class="p-2 rounded-xl bg-white border border-slate-200 font-semibold text-slate-800 truncate"
                                x-text="currentParticipant?.batch?.venue?.name || defaultVenue"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase text-[10px] tracking-wider mb-1">Date</label>
                            <input type="date" name="session_date" x-model="form.session_date"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-600 uppercase text-[10px] tracking-wider mb-1">Designation / Office</label>
                            <input type="text" name="designation_office_snapshot" x-model="form.designation_office_snapshot"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase text-[10px] tracking-wider mb-1">
                            Participant Name <span class="text-slate-400 font-normal lowercase">(optional on paper, matched with enrolled roster)</span>
                        </label>
                        <div class="flex items-center space-x-2">
                            <input type="text" name="participant_name_snapshot" x-model="form.participant_name_snapshot"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-bold text-blue-700 focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- RATE THE SESSION (1 = Poor, 5 = Excellent) Matrix Table -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-2xs">
                    <div class="px-5 py-3 bg-slate-100/90 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 tracking-wider uppercase">RATE THE SESSION</h3>
                            <p class="text-[11px] text-slate-500">Tick one box on each line. 1 = Poor &bull; 5 = Excellent</p>
                        </div>
                        
                        <!-- Quick Fill Presets Bar -->
                        <div class="flex items-center space-x-1.5 flex-wrap">
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider hidden sm:inline mr-1">Quick Fill:</span>
                            <button type="button" @click="setAllRatings(5)"
                                class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-bold transition flex items-center space-x-1 cursor-pointer"
                                title="Set all 5 questions to Excellent (5)">
                                <span>★ All 5 (Excellent)</span>
                            </button>
                            <button type="button" @click="setAllRatings(4)"
                                class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 text-[11px] font-bold transition flex items-center space-x-1 cursor-pointer"
                                title="Set all 5 questions to Very Good (4)">
                                <span>★ All 4 (Very Good)</span>
                            </button>
                            <button type="button" @click="setAllRatings(3)"
                                class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 text-[11px] font-medium transition cursor-pointer"
                                title="Set all 5 questions to Good (3)">
                                <span>All 3</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 text-center font-bold">
                                    <th class="py-2.5 px-4 text-left font-semibold text-slate-700">Questions</th>
                                    <th class="py-2.5 px-2 w-16 text-center border-l border-slate-200 bg-rose-50/30">
                                        <div class="text-xs font-bold text-slate-800">1</div>
                                        <div class="text-[9px] uppercase tracking-wider text-rose-600 font-semibold">Poor</div>
                                    </th>
                                    <th class="py-2.5 px-2 w-16 text-center border-l border-slate-200 bg-orange-50/20">
                                        <div class="text-xs font-bold text-slate-800">2</div>
                                        <div class="text-[9px] uppercase tracking-wider text-orange-600 font-semibold">Fair</div>
                                    </th>
                                    <th class="py-2.5 px-2 w-16 text-center border-l border-slate-200 bg-amber-50/20">
                                        <div class="text-xs font-bold text-slate-800">3</div>
                                        <div class="text-[9px] uppercase tracking-wider text-amber-600 font-semibold">Good</div>
                                    </th>
                                    <th class="py-2.5 px-2 w-16 text-center border-l border-slate-200 bg-blue-50/20">
                                        <div class="text-xs font-bold text-slate-800">4</div>
                                        <div class="text-[9px] uppercase tracking-wider text-blue-600 font-semibold">V.Good</div>
                                    </th>
                                    <th class="py-2.5 px-2 w-16 text-center border-l border-slate-200 bg-emerald-50/30">
                                        <div class="text-xs font-bold text-slate-800">5</div>
                                        <div class="text-[9px] uppercase tracking-wider text-emerald-600 font-semibold">Excellent</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <!-- Row 1: Clarity of Content -->
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        1. Clarity of the training content <span class="text-rose-500">*</span>
                                    </td>
                                    <template x-for="val in [1, 2, 3, 4, 5]" :key="'q1_' + val">
                                        <td class="py-2 px-1 text-center border-l border-slate-200 transition"
                                            :class="getCellBgClass(form.q1_clarity, val)">
                                            <label class="flex items-center justify-center w-full h-full py-1 cursor-pointer">
                                                <input type="radio" name="q1_clarity" :value="val" x-model.number="form.q1_clarity" required class="form1-radio-hidden">
                                                <span class="rating-pill-btn"
                                                    :class="getRatingPillClass(form.q1_clarity, val)"
                                                    x-text="val"></span>
                                            </label>
                                        </td>
                                    </template>
                                </tr>

                                <!-- Row 2: Trainer Knowledge -->
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        2. Trainer's knowledge of the subject <span class="text-rose-500">*</span>
                                    </td>
                                    <template x-for="val in [1, 2, 3, 4, 5]" :key="'q2_' + val">
                                        <td class="py-2 px-1 text-center border-l border-slate-200 transition"
                                            :class="getCellBgClass(form.q2_knowledge, val)">
                                            <label class="flex items-center justify-center w-full h-full py-1 cursor-pointer">
                                                <input type="radio" name="q2_knowledge" :value="val" x-model.number="form.q2_knowledge" required class="form1-radio-hidden">
                                                <span class="rating-pill-btn"
                                                    :class="getRatingPillClass(form.q2_knowledge, val)"
                                                    x-text="val"></span>
                                            </label>
                                        </td>
                                    </template>
                                </tr>

                                <!-- Row 3: Work Relevance -->
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        3. Relevance of examples to your daily work <span class="text-rose-500">*</span>
                                    </td>
                                    <template x-for="val in [1, 2, 3, 4, 5]" :key="'q3_' + val">
                                        <td class="py-2 px-1 text-center border-l border-slate-200 transition"
                                            :class="getCellBgClass(form.q3_relevance, val)">
                                            <label class="flex items-center justify-center w-full h-full py-1 cursor-pointer">
                                                <input type="radio" name="q3_relevance" :value="val" x-model.number="form.q3_relevance" required class="form1-radio-hidden">
                                                <span class="rating-pill-btn"
                                                    :class="getRatingPillClass(form.q3_relevance, val)"
                                                    x-text="val"></span>
                                            </label>
                                        </td>
                                    </template>
                                </tr>

                                <!-- Row 4: Handbook Usefulness -->
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        4. Usefulness of the Participant Handbook <span class="text-rose-500">*</span>
                                    </td>
                                    <template x-for="val in [1, 2, 3, 4, 5]" :key="'q4_' + val">
                                        <td class="py-2 px-1 text-center border-l border-slate-200 transition"
                                            :class="getCellBgClass(form.q4_handbook, val)">
                                            <label class="flex items-center justify-center w-full h-full py-1 cursor-pointer">
                                                <input type="radio" name="q4_handbook" :value="val" x-model.number="form.q4_handbook" required class="form1-radio-hidden">
                                                <span class="rating-pill-btn"
                                                    :class="getRatingPillClass(form.q4_handbook, val)"
                                                    x-text="val"></span>
                                            </label>
                                        </td>
                                    </template>
                                </tr>

                                <!-- Row 5: Overall Satisfaction -->
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        5. Overall satisfaction with the session <span class="text-rose-500">*</span>
                                    </td>
                                    <template x-for="val in [1, 2, 3, 4, 5]" :key="'q5_' + val">
                                        <td class="py-2 px-1 text-center border-l border-slate-200 transition"
                                            :class="getCellBgClass(form.q5_overall, val)">
                                            <label class="flex items-center justify-center w-full h-full py-1 cursor-pointer">
                                                <input type="radio" name="q5_overall" :value="val" x-model.number="form.q5_overall" required class="form1-radio-hidden">
                                                <span class="rating-pill-btn"
                                                    :class="getRatingPillClass(form.q5_overall, val)"
                                                    x-text="val"></span>
                                            </label>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- IN YOUR OWN WORDS (3 compact question cards) -->
                <div>
                    <div class="pb-2 border-b border-slate-200 mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-900 tracking-wider uppercase">IN YOUR OWN WORDS</h3>
                            <p class="text-[11px] text-slate-500">Transcribe open-ended participant answers verbatim</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/90 space-y-1.5">
                            <label class="block font-bold text-slate-800 text-[11px] leading-tight">
                                What was the most useful part of today's session? <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="q6_most_useful" rows="3" x-model="form.q6_most_useful" required
                                placeholder="E.g. Hands-on data entry examples, policy guidelines..."
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none transition"></textarea>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/90 space-y-1.5">
                            <label class="block font-bold text-slate-800 text-[11px] leading-tight">
                                What could be improved? <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="q7_improvements" rows="3" x-model="form.q7_improvements" required
                                placeholder="E.g. More practical sessions, pace of presentation..."
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none transition"></textarea>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/90 space-y-1.5">
                            <label class="block font-bold text-slate-800 text-[11px] leading-tight">
                                Any question that was not answered? <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="q8_unanswered" rows="3" x-model="form.q8_unanswered" required
                                placeholder="E.g. Clarification on Aadhaar masking, data storage..."
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none transition"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Physical Form Disclaimer Footer -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center text-[11px] text-slate-500 italic">
                    Thank you. Your feedback is used only to improve the training. Please do not write personal or beneficiary data in this form.
                </div>
            </form>

            <!-- 3. Pinned Sticky Footer (ALWAYS visible at the bottom of the dialog) -->
            <div class="shrink-0 px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between flex-wrap gap-3">
                <button type="button" @click="modalOpen = false"
                    class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition cursor-pointer">
                    Cancel
                </button>

                <div class="flex items-center space-x-2.5">
                    <button type="submit" form="feedbackEntryForm"
                        :disabled="saving"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition flex items-center space-x-2 cursor-pointer disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="saving ? 'Saving...' : 'Save'"></span>
                    </button>

                    <button type="button"
                        @click="submitFeedbackForm(true)"
                        :disabled="saving"
                        class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition flex items-center space-x-2 cursor-pointer disabled:opacity-50">
                        <span>Save & Next Trainee</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold flex items-center space-x-2.5"
        :class="toast.type === 'success' ? 'bg-slate-900 text-white border border-slate-700' : 'bg-rose-600 text-white'"
        style="display: none;">
        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span x-text="toast.message"></span>
    </div>

</div>

<script>
function participantFeedbackManager(config) {
    return {
        batchId: config.batchId,
        participantsList: config.participants || [],
        defaultTier: config.tierName,
        defaultVenue: config.venueName,
        defaultDate: config.batchDate,
        searchQuery: '',
        statusFilter: 'all', // 'all', 'pending', 'logged'
        modalOpen: false,
        saving: false,
        currentParticipant: null,
        currentParticipantIndex: 0,
        form: {
            session_date: '',
            designation_office_snapshot: '',
            participant_name_snapshot: '',
            q1_clarity: null,
            q2_knowledge: null,
            q3_relevance: null,
            q4_handbook: null,
            q5_overall: null,
            q6_most_useful: '',
            q7_improvements: '',
            q8_unanswered: '',
        },
        toast: {
            show: false,
            message: '',
            type: 'success',
            timeout: null,
        },

        get totalParticipants() {
            return this.participantsList.length;
        },

        get enteredCount() {
            return this.participantsList.filter(bp => bp.feedback !== null).length;
        },

        get pendingCount() {
            return Math.max(0, this.totalParticipants - this.enteredCount);
        },

        get completionPercentage() {
            return this.totalParticipants > 0 ? Math.round((this.enteredCount / this.totalParticipants) * 100) : 0;
        },

        get q1Average() {
            const feedbacks = this.participantsList.filter(bp => bp.feedback).map(bp => Number(bp.feedback.q1_clarity));
            return feedbacks.length ? (feedbacks.reduce((a, b) => a + b, 0) / feedbacks.length).toFixed(1) : '0.0';
        },

        get q2Average() {
            const feedbacks = this.participantsList.filter(bp => bp.feedback).map(bp => Number(bp.feedback.q2_knowledge));
            return feedbacks.length ? (feedbacks.reduce((a, b) => a + b, 0) / feedbacks.length).toFixed(1) : '0.0';
        },

        get q3Average() {
            const feedbacks = this.participantsList.filter(bp => bp.feedback).map(bp => Number(bp.feedback.q3_relevance));
            return feedbacks.length ? (feedbacks.reduce((a, b) => a + b, 0) / feedbacks.length).toFixed(1) : '0.0';
        },

        get q4Average() {
            const feedbacks = this.participantsList.filter(bp => bp.feedback).map(bp => Number(bp.feedback.q4_handbook));
            return feedbacks.length ? (feedbacks.reduce((a, b) => a + b, 0) / feedbacks.length).toFixed(1) : '0.0';
        },

        get q5Average() {
            const feedbacks = this.participantsList.filter(bp => bp.feedback).map(bp => Number(bp.feedback.q5_overall));
            return feedbacks.length ? (feedbacks.reduce((a, b) => a + b, 0) / feedbacks.length).toFixed(1) : '0.0';
        },

        get overallAverage() {
            const feedbacks = this.participantsList.filter(bp => bp.feedback).map(bp => Number(bp.feedback.average_rating));
            return feedbacks.length ? (feedbacks.reduce((a, b) => a + b, 0) / feedbacks.length).toFixed(1) : '0.0';
        },

        isParticipantVisible(bpId) {
            const bp = this.participantsList.find(item => item.id === bpId);
            if (!bp) return true;

            // Status Filter
            if (this.statusFilter === 'pending' && bp.feedback !== null) return false;
            if (this.statusFilter === 'logged' && bp.feedback === null) return false;

            // Search query
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase();
                const name = (bp.participant?.name || '').toLowerCase();
                const office = (bp.participant?.office_name || '').toLowerCase();
                const designation = (bp.participant?.designation || '').toLowerCase();
                const phone = (bp.participant?.phone || '').toLowerCase();
                return name.includes(q) || office.includes(q) || designation.includes(q) || phone.includes(q);
            }

            return true;
        },

        hasFeedback(bpId) {
            const bp = this.participantsList.find(item => item.id === bpId);
            return bp && bp.feedback !== null;
        },

        getRating(bpId) {
            const bp = this.participantsList.find(item => item.id === bpId);
            return bp && bp.feedback ? Number(bp.feedback.average_rating).toFixed(1) : '';
        },

        getScore(bpId, field) {
            const bp = this.participantsList.find(item => item.id === bpId);
            return bp && bp.feedback ? bp.feedback[field] : '';
        },

        getNote(bpId) {
            const bp = this.participantsList.find(item => item.id === bpId);
            if (!bp || !bp.feedback) return '—';
            const fb = bp.feedback;
            if (fb.q6_most_useful) return 'Useful: ' + fb.q6_most_useful;
            if (fb.q7_improvements) return 'Improve: ' + fb.q7_improvements;
            if (fb.q8_unanswered) return 'Query: ' + fb.q8_unanswered;
            return '—';
        },

        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            if (this.toast.timeout) clearTimeout(this.toast.timeout);
            this.toast.timeout = setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        openModalForParticipantId(bpId) {
            const bp = this.participantsList.find(item => item.id === bpId);
            if (!bp) return;

            this.currentParticipant = bp;
            this.currentParticipantIndex = this.participantsList.findIndex(item => item.id === bp.id);

            const p = bp.participant;
            const fb = bp.feedback;

            if (fb) {
                Object.assign(this.form, {
                    session_date: fb.session_date ? fb.session_date.split('T')[0] : this.defaultDate,
                    designation_office_snapshot: fb.designation_office_snapshot || ((p?.designation || '') + ' / ' + (p?.office_name || '')),
                    participant_name_snapshot: fb.participant_name_snapshot || p?.name || '',
                    q1_clarity: fb.q1_clarity ? Number(fb.q1_clarity) : null,
                    q2_knowledge: fb.q2_knowledge ? Number(fb.q2_knowledge) : null,
                    q3_relevance: fb.q3_relevance ? Number(fb.q3_relevance) : null,
                    q4_handbook: fb.q4_handbook ? Number(fb.q4_handbook) : null,
                    q5_overall: fb.q5_overall ? Number(fb.q5_overall) : null,
                    q6_most_useful: fb.q6_most_useful || '',
                    q7_improvements: fb.q7_improvements || '',
                    q8_unanswered: fb.q8_unanswered || '',
                });
            } else {
                Object.assign(this.form, {
                    session_date: this.defaultDate,
                    designation_office_snapshot: ((p?.designation || '') + ' / ' + (p?.office_name || '')).trim(),
                    participant_name_snapshot: p?.name || '',
                    q1_clarity: null,
                    q2_knowledge: null,
                    q3_relevance: null,
                    q4_handbook: null,
                    q5_overall: null,
                    q6_most_useful: '',
                    q7_improvements: '',
                    q8_unanswered: '',
                });
            }

            this.modalOpen = true;
        },

        goToPreviousTrainee() {
            if (this.currentParticipantIndex > 0) {
                const prevBp = this.participantsList[this.currentParticipantIndex - 1];
                if (prevBp) this.openModalForParticipantId(prevBp.id);
            }
        },

        goToNextTrainee() {
            if (this.currentParticipantIndex < this.participantsList.length - 1) {
                const nextBp = this.participantsList[this.currentParticipantIndex + 1];
                if (nextBp) this.openModalForParticipantId(nextBp.id);
            }
        },

        setAllRatings(score) {
            const val = Number(score);
            this.form.q1_clarity = val;
            this.form.q2_knowledge = val;
            this.form.q3_relevance = val;
            this.form.q4_handbook = val;
            this.form.q5_overall = val;
        },

        getRatingPillClass(selectedVal, val) {
            if (Number(selectedVal) !== Number(val)) {
                return 'rating-pill-default';
            }
            return 'rating-pill-active-' + val;
        },

        getCellBgClass(selectedVal, val) {
            if (Number(selectedVal) !== Number(val)) return '';
            return 'rating-cell-active-' + val;
        },

        async submitFeedbackForm(advanceToNext = false) {
            if (!this.currentParticipant) return;

            // Validate ratings 1-5
            if (!this.form.q1_clarity || !this.form.q2_knowledge || !this.form.q3_relevance || !this.form.q4_handbook || !this.form.q5_overall) {
                alert('Please provide a rating (1-5) for all 5 questions in "RATE THE SESSION".');
                return;
            }

            // Validate mandatory open-ended questions
            const useful = (this.form.q6_most_useful || '').trim();
            const improve = (this.form.q7_improvements || '').trim();
            const unanswered = (this.form.q8_unanswered || '').trim();

            if (!useful) {
                alert('Question 6 (What was the most useful part of today\'s session?) is mandatory.');
                return;
            }
            if (!improve) {
                alert('Question 7 (What could be improved?) is mandatory.');
                return;
            }
            if (!unanswered) {
                alert('Question 8 (Any question that was not answered during session?) is mandatory.');
                return;
            }

            this.saving = true;
            const url = `/batches/${this.batchId}/participants/${this.currentParticipant.id}/feedback`;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        training_tier_snapshot: this.defaultTier,
                        venue_snapshot: this.defaultVenue,
                        session_date: this.form.session_date,
                        designation_office_snapshot: this.form.designation_office_snapshot,
                        participant_name_snapshot: this.form.participant_name_snapshot,
                        q1_clarity: this.form.q1_clarity,
                        q2_knowledge: this.form.q2_knowledge,
                        q3_relevance: this.form.q3_relevance,
                        q4_handbook: this.form.q4_handbook,
                        q5_overall: this.form.q5_overall,
                        q6_most_useful: useful,
                        q7_improvements: improve,
                        q8_unanswered: unanswered,
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Update in-memory participant feedback
                    const idx = this.participantsList.findIndex(item => item.id === this.currentParticipant.id);
                    if (idx !== -1) {
                        this.participantsList[idx].feedback = data.feedback;
                        this.participantsList = [...this.participantsList];
                    }

                    this.showToast(`Feedback for ${this.form.participant_name_snapshot} saved successfully!`);

                    if (advanceToNext) {
                        // Find next pending or next participant
                        const nextPending = this.participantsList.find((item, i) => i > idx && item.feedback === null)
                            || this.participantsList.find(item => item.feedback === null);

                        if (nextPending) {
                            this.openModalForParticipantId(nextPending.id);
                        } else {
                            this.modalOpen = false;
                            this.showToast('All participant feedback forms for this batch are completed! 🎉');
                        }
                    } else {
                        this.modalOpen = false;
                    }
                } else {
                    alert(data.message || 'Error saving feedback. Please ensure all 5 ratings and all 3 open-ended questions are filled.');
                }
            } catch (err) {
                console.error('Save feedback error:', err);
                alert('Network error occurred while saving feedback. Please retry.');
            } finally {
                this.saving = false;
            }
        },

        async deleteFeedbackById(bpId) {
            const bp = this.participantsList.find(item => item.id === bpId);
            if (!bp || !bp.feedback || !confirm(`Remove feedback for ${bp.participant?.name}?`)) return;

            const url = `/batches/${this.batchId}/feedback-forms/${bp.feedback.id}`;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                if (response.ok) {
                    const idx = this.participantsList.findIndex(item => item.id === bp.id);
                    if (idx !== -1) {
                        this.participantsList[idx].feedback = null;
                        this.participantsList = [...this.participantsList];
                    }
                    this.showToast('Participant feedback deleted.');
                } else {
                    alert('Could not delete feedback.');
                }
            } catch (e) {
                console.error(e);
            }
        }
    };
}
</script>
@endsection
