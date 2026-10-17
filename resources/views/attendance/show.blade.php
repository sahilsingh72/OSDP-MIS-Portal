@extends('layouts.app', ['title' => "Attendance - {$batch->batch_code}", 'headerTitle' => 'Participant Attendance'])

@section('content')
<div class="space-y-6">

    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Participant Attendance Register</h1>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-semibold">{{ $batch->batch_code }}</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Digital transcription of signed paper attendance sheet. Sign on arrival (Morning) and after lunch (Afternoon).
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('batches.show', $batch) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                ← Back to Batch Details
            </a>
        </div>
    </div>

    <!-- Official Paper Form 2 Header Replica -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="border-b border-slate-200 pb-3 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-orange-600 uppercase tracking-wider block">Odisha State Data Policy 2.0</span>
                <span class="text-sm font-extrabold text-slate-900">ATTENDANCE SHEET (FORM - SIDES 1 & 2)</span>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold text-slate-700">Tier: {{ $batch->tier?->name }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block font-medium">District / Block / GP</span>
                <span class="text-slate-800 font-bold">{{ $batch->district?->name }} / {{ $batch->block?->name ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Venue & Date</span>
                <span class="text-slate-800 font-bold">{{ $batch->venue?->name ?? 'Venue' }}, {{ $batch->scheduled_date->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Trainer Name(s)</span>
                <span class="text-slate-800 font-bold">{{ $batch->trainers->pluck('name')->implode(', ') ?: 'Trainer' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Registered / Present Headcount</span>
                <span class="text-slate-800 font-extrabold text-sm text-emerald-600">{{ $batch->present_participants }} Present / {{ $batch->registered_participants }} Registered</span>
            </div>
        </div>
    </div>

    <!-- Participant Enrollment (Bulk Excel Upload & Manual Transcription) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm" x-data="{ mode: 'excel' }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Enroll Participants into Batch</h3>
                    <p class="text-xs text-slate-500">Register all attendees in bulk using an Excel spreadsheet (.xlsx, .csv) or enter individually</p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <!-- Mode Switcher -->
                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/80 text-xs font-semibold">
                    <button type="button" @click="mode = 'excel'"
                        :class="mode === 'excel' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Bulk Upload (Excel)</span>
                    </button>
                    <button type="button" @click="mode = 'manual'"
                        :class="mode === 'manual' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Single Entry</span>
                    </button>
                </div>

                <a href="{{ route('batches.participants.template', $batch) }}"
                    class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs border border-emerald-200 transition flex items-center space-x-1.5"
                    title="Download official sample Excel template">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download Excel Template</span>
                </a>
            </div>
        </div>

        <!-- Bulk Excel Upload Form -->
        <div x-show="mode === 'excel'" class="pt-4"
            x-data="{
                fileName: '',
                fileSize: '',
                isDragging: false,
                handleFile(file) {
                    if (file) {
                        this.fileName = file.name;
                        this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
                    }
                },
                clearFile() {
                    this.fileName = '';
                    this.fileSize = '';
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                }
            }">
            <form action="{{ route('batches.participants.bulk-upload', $batch) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                
                <input type="file" name="file" x-ref="fileInput" required accept=".xlsx,.xls,.csv" class="hidden"
                    @change="handleFile($event.target.files[0])">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Dropzone -->
                    <div class="md:col-span-2">
                        <div
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length) { $refs.fileInput.files = $event.dataTransfer.files; handleFile($event.dataTransfer.files[0]); }"
                            @click="$refs.fileInput.click()"
                            :class="isDragging ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-300/80 bg-slate-50/50 hover:bg-emerald-50/20 hover:border-emerald-400'"
                            class="border-2 border-dashed rounded-2xl p-3.5 text-center cursor-pointer transition group">
                            
                            <!-- Empty State -->
                            <div x-show="!fileName" class="flex items-center justify-center space-x-3 py-1">
                                <div class="w-9 h-9 rounded-xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-slate-400 group-hover:text-emerald-600 group-hover:border-emerald-200 transition">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div>
                                        <span class="font-bold text-slate-700 group-hover:text-emerald-700 text-xs transition">Select or drag & drop spreadsheet</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">Excel (.xlsx, .xls) or CSV up to 10MB</div>
                                </div>
                            </div>

                            <!-- File Selected State -->
                            <div x-show="fileName" class="flex items-center justify-between p-2 rounded-xl bg-white border border-emerald-200 shadow-2xs text-left" @click.stop style="display: none;">
                                <div class="flex items-center space-x-3 truncate">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        XLS
                                    </div>
                                    <div class="truncate">
                                        <div class="font-bold text-slate-900 text-xs truncate" x-text="fileName"></div>
                                        <div class="text-[11px] text-emerald-600 font-medium" x-text="fileSize + ' &bull; Ready to enroll'"></div>
                                    </div>
                                </div>
                                <button type="button" @click="clearFile()" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition shrink-0 ml-2" title="Remove file">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Action -->
                    <div class="flex items-center">
                        <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <span>Import & Enroll</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Manual Single Entry Form -->
        <div x-show="mode === 'manual'" class="pt-4" style="display: none;">
            <form action="{{ route('batches.participants.store', $batch) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Participant Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Full Name"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 p-2.5 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Designation <span class="text-rose-500">*</span></label>
                    <input type="text" name="designation" required placeholder="e.g. PEO / Data Operator"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 p-2.5 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Office / GP <span class="text-rose-500">*</span></label>
                    <input type="text" name="office_name" required placeholder="e.g. Mendhasala GP"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 p-2.5 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Phone (Optional)</label>
                    <input type="text" name="phone" placeholder="e.g. 9861012345"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 p-2.5 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Email ID (Optional)</label>
                    <input type="email" name="email" placeholder="e.g. trainee@gov.in"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 p-2.5 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition text-center shadow-md shadow-blue-600/20 flex items-center justify-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>Add & Enroll</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Transcription Form -->
    <form action="{{ route('batches.attendance.update', $batch) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        @csrf

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3 w-16 text-center">S.No</th>
                        <th class="px-5 py-3">Participant Name</th>
                        <th class="px-5 py-3">Designation</th>
                        <th class="px-5 py-3">Office / Gram Panchayat</th>
                        <th class="px-5 py-3 text-center w-32">Morning Sign</th>
                        <th class="px-5 py-3 text-center w-32">Afternoon Sign</th>
                        <th class="px-5 py-3">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @php
                        $activeBatchParticipants = $batch->batchParticipants->filter(function ($bp) {
                            return $bp->participant && !$bp->participant->trashed();
                        })->values();
                    @endphp
                    @forelse($activeBatchParticipants as $idx => $bp)
                    @php $att = $bp->attendance; @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3 text-center font-bold text-slate-500">
                            {{ $idx + 1 }}
                            <input type="hidden" name="attendance[{{ $idx }}][batch_participant_id]" value="{{ $bp->id }}">
                        </td>
                        <td class="px-5 py-3 font-bold text-slate-900">
                            {{ $bp->participant?->name ?? 'Participant Record' }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">
                            {{ $bp->participant?->designation ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">
                            {{ $bp->participant?->office_name ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="attendance[{{ $idx }}][morning_present]" value="1"
                                    {{ $att && $att->morning_present ? 'checked' : '' }}
                                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            </label>
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($batch->tier?->code === 'gram_panchayat')
                                <span class="text-slate-400 text-[10px]">Half-Day</span>
                            @else
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="attendance[{{ $idx }}][afternoon_present]" value="1"
                                        {{ $att && $att->afternoon_present ? 'checked' : '' }}
                                        class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                </label>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <input type="text" name="attendance[{{ $idx }}][remarks]" value="{{ $att?->remarks }}"
                                placeholder="Optional note"
                                class="w-full rounded-lg border border-slate-200 px-2.5 py-1 text-xs focus:border-blue-500 focus:outline-none">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            No active participants enrolled yet. Use the "Add New Participant" section above to transcribe the paper sheet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activeBatchParticipants->isNotEmpty())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-medium">
                Note: Updating attendance recalculates total registered and present counts immediately.
            </span>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                Save & Update Attendance
            </button>
        </div>
        @endif
    </form>

</div>
@endsection
