@extends('layouts.app', ['title' => "Form 2 Attendance - {$batch->batch_code}", 'headerTitle' => 'Participant Attendance (Form 2)'])

@section('content')
<div class="space-y-6">

    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Form 2: Participant Attendance Register</h1>
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
                <span class="text-sm font-extrabold text-slate-900">ATTENDANCE SHEET (FORM 2 - SIDES 1 & 2)</span>
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

    <!-- Add New Participant Accordion -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm" x-data="{ open: false }">
        <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">+</div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Add New Participant to Register</h3>
                    <p class="text-[11px] text-slate-500">Transcribe extra names or additions from supplementary attendance sheets</p>
                </div>
            </div>
            <span class="text-xs text-blue-600 font-semibold" x-text="open ? 'Close Form' : 'Expand Form'"></span>
        </div>

        <form x-show="open" action="{{ route('batches.participants.store', $batch) }}" method="POST" class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-5 gap-3 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Participant Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="Full Name"
                    class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Designation <span class="text-rose-500">*</span></label>
                <input type="text" name="designation" required placeholder="e.g. PEO / Data Operator"
                    class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Office / GP <span class="text-rose-500">*</span></label>
                <input type="text" name="office_name" required placeholder="e.g. Mendhasala GP"
                    class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Phone (Optional)</label>
                <input type="text" name="phone" placeholder="Contact Number"
                    class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition text-center shadow-sm">
                    Add & Enroll
                </button>
            </div>
        </form>
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
                    @forelse($batch->batchParticipants as $idx => $bp)
                    @php $att = $bp->attendance; @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3 text-center font-bold text-slate-500">
                            {{ $bp->serial_number ?? ($idx + 1) }}
                            <input type="hidden" name="attendance[{{ $idx }}][batch_participant_id]" value="{{ $bp->id }}">
                        </td>
                        <td class="px-5 py-3 font-bold text-slate-900">
                            {{ $bp->participant?->name }}
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
                            No participants enrolled yet. Use the "Add New Participant" section above to transcribe the paper sheet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batch->batchParticipants->isNotEmpty())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-medium">
                Note: Updating attendance recalculates total registered and present counts immediately.
            </span>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                Save & Update Form 2 Register
            </button>
        </div>
        @endif
    </form>

</div>
@endsection
