@extends('layouts.app', ['title' => "Batch {$batch->batch_code}", 'headerTitle' => 'Batch Overview'])

@section('content')
<div class="space-y-6">

    <!-- Batch Banner Header -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <!-- Top Details Strip -->
        <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight font-mono whitespace-nowrap">
                            {{ $batch->batch_code }}
                        </h1>
                        <x-status-badge :status="$batch->status" />
                        @if($batch->is_overdue)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 whitespace-nowrap">
                                ⚠️ OVERDUE SUBMISSION (>48h)
                            </span>
                        @endif
                    </div>
                    <!-- Quick Meta Strip -->
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-2.5 text-xs text-slate-500">
                        <span>Tier: <strong class="text-slate-800">{{ $batch->tier?->name ?? 'N/A' }}</strong></span>
                        <span class="text-slate-300">•</span>
                        <span>District: <strong class="text-slate-800">{{ $batch->district?->name ?? 'State Level' }}</strong></span>
                        @if($batch->block)
                            <span class="text-slate-300">•</span>
                            <span>Block: <strong class="text-slate-800">{{ $batch->block->name }}</strong></span>
                        @endif
                        <span class="text-slate-300">•</span>
                        <span>Date: <strong class="text-slate-800">{{ $batch->scheduled_date->format('d M Y') }}</strong></span>
                        <span class="text-slate-300">•</span>
                        <span>Roster: <strong class="text-emerald-700 font-bold">{{ $batch->present_participants }} Present / {{ $batch->registered_participants }} Registered</strong></span>
                    </div>
                </div>

                <!-- Edit Action -->
                @can('update', $batch)
                <div class="self-start lg:self-center">
                    <a href="{{ route('batches.edit', $batch) }}" class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-200 transition">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Edit Batch Details</span>
                    </a>
                </div>
                @endcan
            </div>
        </div>

        <!-- Workflow Action Toolbar -->
        <div class="bg-slate-50/80 px-6 py-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Workflow & Field Operations</span>
            <div class="flex flex-wrap items-center gap-2.5">
                @can('update', $batch)
                <a href="{{ route('batches.attendance.show', $batch) }}" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm shadow-emerald-600/20 transition whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Participants & Attendance</span>
                </a>
                @endcan

                <a href="{{ route('batches.evidence.index', $batch) }}" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-sm shadow-indigo-600/20 transition whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Upload Evidence</span>
                </a>

                @if(!auth()->user()->hasRole(['super-admin', 'pmu', 'ocac']) && $evidenceStatus['is_complete'] && in_array($batch->status, ['scheduled', 'in_progress', 'awaiting_evidence', 'correction_required']))
                    @can('submit', $batch)
                    <form action="{{ route('batches.submit', $batch) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Confirm submission of batch evidence pack for verification?')"
                            class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm shadow-orange-500/20 transition whitespace-nowrap">
                            <span>Submit for Review →</span>
                        </button>
                    </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    <!-- Evidence Pack Checklist (The 6 Mandatory Items) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Evidence Pack Completeness (6 Mandatory Items)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">As per OSDP MIS Upload Checklist. Submission SLA: within 48 hours of session.</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold {{ $evidenceStatus['is_complete'] ? 'text-emerald-600' : 'text-orange-500' }}">
                    {{ $evidenceStatus['completed_count'] }} of 6 Attached
                </span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
                $checklistConfig = [
                    'attendance_sheet' => ['name' => '1. Attendance Sheet', 'desc' => 'Signed both sides (2 images or 1 PDF)'],
                    'photo_1' => ['name' => '2. Photo 1 (Group with Banner)', 'desc' => 'Full room view with 8x5 banner'],
                    'photo_2' => ['name' => '3. Photo 2 (Trainer Teaching)', 'desc' => 'Trainer instructing in progress'],
                    'video' => ['name' => '4. Video Clip (MP4)', 'desc' => '~1 min short clip, landscape mode'],
                    'feedback_forms' => ['name' => '5. Feedback Forms', 'desc' => 'Merged single PDF of all forms'],
                    'batch_completion_report' => ['name' => '6. Completion Report', 'desc' => 'Form 3 PDF & Training summary'],
                ];
            @endphp

            @foreach($checklistConfig as $key => $item)
                @php $hasUploaded = $evidenceStatus['items'][$key] ?? false; @endphp
                <div class="p-3.5 rounded-xl border {{ $hasUploaded ? 'bg-emerald-50/40 border-emerald-200' : 'bg-slate-50 border-slate-200' }} flex items-start space-x-3 transition">
                    <span class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold {{ $hasUploaded ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-slate-600' }}">
                        {{ $hasUploaded ? '✓' : '✗' }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold {{ $hasUploaded ? 'text-emerald-900' : 'text-slate-800' }} truncate">{{ $item['name'] }}</p>
                        <p class="text-[11px] text-slate-500 truncate">{{ $item['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 text-center sm:text-right">
            <a href="{{ route('batches.evidence.index', $batch) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                Manage Evidence Files in Upload Center →
            </a>
        </div>
    </div>

    <!-- Detail Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Operational Details & Form 3 Summary -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Logistics & Delivery Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-200">
                    Training Logistics & Resource Deployment
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Scheduled Timings</span>
                        <span class="text-slate-800 font-bold">{{ substr($batch->start_time, 0, 5) }} to {{ substr($batch->end_time, 0, 5) }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Actual Timings</span>
                        <span class="text-slate-800 font-bold">
                            {{ $batch->actual_start_time ? substr($batch->actual_start_time, 0, 5) . ' to ' . substr($batch->actual_end_time, 0, 5) : 'Not recorded' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Submission Deadline</span>
                        <span class="font-bold {{ $batch->is_overdue ? 'text-rose-600' : 'text-slate-800' }}">
                            {{ $batch->submission_deadline ? $batch->submission_deadline->format('d M Y, h:i A') : 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Venue</span>
                        <span class="text-slate-800 font-semibold">{{ $batch->venue?->name ?? 'Venue as planned' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Trainers Assigned</span>
                        <span class="text-slate-800 font-semibold">{{ $batch->trainers->pluck('name')->implode(', ') ?: 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Participants Roster</span>
                        <span class="text-slate-800 font-bold">{{ $batch->present_participants }} Present / {{ $batch->registered_participants }} Registered</span>
                    </div>
                </div>

                <!-- Modules Covered -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="text-slate-400 block text-xs font-medium mb-1.5">Modules Delivered</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($batch->modules as $mod)
                            <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold border border-slate-200">
                                Mod {{ $mod->module_number }}: {{ $mod->title }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Training Summary-->
            @if($batch->summary)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200 flex items-center justify-between">
                    <span>Training Summary</span>
                    <span class="text-xs text-slate-400 font-normal">Submitted by: {{ $batch->summary->submitted_by_name ?? 'Trainer' }}</span>
                </h3>

                <div class="text-xs space-y-3">
                    @if($batch->summary->what_went_well)
                    <div>
                        <strong class="text-slate-700 block">What went well:</strong>
                        <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 mt-1">{{ $batch->summary->what_went_well }}</p>
                    </div>
                    @endif

                    @if($batch->summary->problems_faced)
                    <div>
                        <strong class="text-slate-700 block">Problems faced:</strong>
                        <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 mt-1">{{ $batch->summary->problems_faced }}</p>
                    </div>
                    @endif

                    @if($batch->summary->follow_up_actions)
                    <div>
                        <strong class="text-slate-700 block">Follow-up actions:</strong>
                        <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 mt-1">{{ $batch->summary->follow_up_actions }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Participant Feedback Scorecard -->
            @if($batch->feedback)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-200 flex items-center justify-between">
                    <span>Participant Feedback Evaluation</span>
                    <span class="text-xs text-slate-400 font-normal">{{ $batch->feedback->total_forms_analyzed }} Forms Analyzed</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[11px] mb-1">Q1. Clarity</span>
                        <span class="text-base font-extrabold text-blue-600">{{ number_format($batch->feedback->q1_clarity_avg, 2) }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[11px] mb-1">Q2. Knowledge</span>
                        <span class="text-base font-extrabold text-blue-600">{{ number_format($batch->feedback->q2_knowledge_avg, 2) }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[11px] mb-1">Q3. Relevance</span>
                        <span class="text-base font-extrabold text-blue-600">{{ number_format($batch->feedback->q3_relevance_avg, 2) }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-slate-500 block text-[11px] mb-1">Q4. Handbook</span>
                        <span class="text-base font-extrabold text-blue-600">{{ number_format($batch->feedback->q4_handbook_avg, 2) }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 col-span-2 sm:col-span-1">
                        <span class="text-slate-500 block text-[11px] mb-1">Overall (Q5)</span>
                        <span class="text-base font-extrabold text-emerald-600">★ {{ number_format($batch->feedback->q5_overall_avg, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- Column 3: Verification & Review Decision Engine -->
        <div class="space-y-6">

            <!-- Review Actions & Decision Form -->
            @can('review', $batch)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                    Review Decision Engine
                </h3>

                <form action="{{ route('batches.reviews.store', $batch) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Review Decision <span class="text-rose-500">*</span></label>
                        <select name="decision" required class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
                            <option value="">-- Choose Review Decision --</option>
                            @if(auth()->user()->hasRole('district-coordinator'))
                                <option value="pack_checked">✓ Mark Physical Pack Checked (DC)</option>
                            @endif
                            <option value="verified">✅ Approve & Verify Evidence Pack</option>
                            <option value="correction_requested">⚠️ Request Resubmission / Correction</option>
                            <option value="rejected">❌ Reject Submission</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Review Comments / Findings <span class="text-rose-500">*</span></label>
                        <textarea name="remarks" required rows="3" placeholder="Provide detailed verification remarks..."
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition shadow-sm">
                        Record Decision & Log Audit
                    </button>
                </form>
            </div>
            @endcan

            <!-- Independent Field Monitor Findings -->
            @if($batch->fieldVerifications->isNotEmpty())
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                    Independent Field Audit
                </h3>
                @foreach($batch->fieldVerifications as $fv)
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800">{{ $fv->monitor?->name ?? 'Field Monitor' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800">{{ $fv->training_quality_rating }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500">
                        Visit Date: {{ $fv->visit_date->format('d M Y') }} | Headcount: <strong>{{ $fv->verified_headcount }}</strong>
                    </div>
                    <p class="text-slate-600 text-[11px] mt-1">{{ $fv->observations }}</p>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Review History Audit Trail -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                    Review History Log
                </h3>

                @forelse($batch->reviews as $rev)
                <div class="p-3 rounded-xl border border-slate-200 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800">{{ $rev->reviewer?->name ?? 'Reviewer' }}</span>
                        <span class="text-[10px] font-semibold text-slate-400">{{ $rev->created_at->format('d M, h:i A') }}</span>
                    </div>
                    <div class="text-[11px] font-semibold text-blue-600 uppercase">{{ str_replace('_', ' ', $rev->decision) }}</div>
                    <p class="text-slate-600 text-[11px]">{{ $rev->remarks }}</p>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-2">No review decisions logged yet.</p>
                @endforelse
            </div>

        </div>

    </div>

</div>
@endsection
