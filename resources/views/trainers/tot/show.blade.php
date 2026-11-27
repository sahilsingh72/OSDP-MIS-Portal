@extends('layouts.app', ['title' => "TOT Batch - {$totBatch->batch_code}", 'headerTitle' => 'TOT Batch Attendance'])

@section('content')
@php
    $openEditOnLoad = $errors->any() && old('_form_mode') === 'edit';
    $editDefaultVenueMode = ($totBatch->tot_venue_id && $totVenues->isNotEmpty()) ? 'existing' : 'new';
    $trainerPickerData = $availableTrainers->map(function ($t) use ($enrolledTrainerIds) {
        return [
            'id' => $t->id,
            'name' => $t->name,
            'phone' => $t->phone ?? '',
            'email' => $t->email ?? '',
            'designation' => $t->designation ?? 'Field Master Trainer',
            'department' => $t->department ?? '',
            'district_id' => $t->district_id,
            'district_name' => $t->district?->name ?? 'State-wide',
            'block_name' => $t->block?->name ?? 'All Blocks',
            'already_enrolled' => in_array($t->id, $enrolledTrainerIds, true),
        ];
    })->values();
@endphp
<div class="space-y-6"
    x-data="{
        editModalOpen: {{ $openEditOnLoad ? 'true' : 'false' }},
        editForm: {
            batch_code: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('batch_code', $totBatch->batch_code) : $totBatch->batch_code)) }},
            title: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('title', $totBatch->title ?? '') : ($totBatch->title ?? ''))) }},
            training_date: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('training_date', $totBatch->training_date?->format('Y-m-d')) : $totBatch->training_date?->format('Y-m-d'))) }},
            district_id: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('district_id', $totBatch->district_id ?? '') : ($totBatch->district_id ?? ''))) }},
            venue_mode: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('venue_mode', $editDefaultVenueMode) : $editDefaultVenueMode)) }},
            tot_venue_id: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('tot_venue_id', $totBatch->tot_venue_id ?? '') : ($totBatch->tot_venue_id ?? ''))) }},
            venue_name: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('venue_name', $totBatch->venue_name ?? '') : ($editDefaultVenueMode === 'new' ? ($totBatch->venue_name ?? '') : ''))) }},
            venue_address: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('venue_address', $totBatch->venue_address ?? '') : ($editDefaultVenueMode === 'new' ? ($totBatch->venue_address ?? '') : ''))) }},
            status: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('status', $totBatch->status) : $totBatch->status)) }},
            remarks: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('remarks', $totBatch->remarks ?? '') : ($totBatch->remarks ?? ''))) }}
        },
        searchTrainer: '',
        filterDistrictId: '',
        selectedTrainerIds: [],
        trainers: {{ \Illuminate\Support\Js::from($trainerPickerData) }},
        get visibleTrainers() {
            const q = this.searchTrainer.trim().toLowerCase();
            const dId = this.filterDistrictId;
            return this.trainers.filter(t => {
                if (dId && String(t.district_id || '') !== String(dId)) return false;
                if (q !== '') {
                    return t.name.toLowerCase().includes(q)
                        || t.phone.toLowerCase().includes(q)
                        || t.email.toLowerCase().includes(q)
                        || t.designation.toLowerCase().includes(q)
                        || t.district_name.toLowerCase().includes(q);
                }
                return true;
            });
        },
        toggleSelectAllVisible() {
            const eligibleIds = this.visibleTrainers
                .filter(t => !t.already_enrolled)
                .map(t => String(t.id));
            if (eligibleIds.length === 0) return;

            const allSelected = eligibleIds.every(id => this.selectedTrainerIds.includes(id));
            if (allSelected) {
                this.selectedTrainerIds = this.selectedTrainerIds.filter(id => !eligibleIds.includes(id));
            } else {
                const merged = new Set([...this.selectedTrainerIds, ...eligibleIds]);
                this.selectedTrainerIds = Array.from(merged);
            }
        }
    }">

    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('trainers.index') }}" class="hover:text-blue-600 transition">Field Trainers</a>
                <span>/</span>
                <a href="{{ route('trainers.tot.index') }}" class="hover:text-blue-600 transition">TOT</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $totBatch->batch_code }}</span>
            </div>
            <div class="flex items-center space-x-2.5 flex-wrap gap-y-1">
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $totBatch->batch_code }}</h1>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">
                    1-Day TOT Program
                </span>
                @if($totBatch->status === 'completed')
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 uppercase">
                        Completed
                    </span>
                @else
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-200 uppercase">
                        Scheduled
                    </span>
                @endif
            </div>
            @if($totBatch->title)
                <p class="text-xs text-slate-600 font-medium mt-0.5">{{ $totBatch->title }}</p>
            @endif
        </div>

        <div class="flex items-center space-x-2.5 shrink-0">
            <a href="{{ route('trainers.tot.index') }}"
                class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                &larr; All TOT Batches
            </a>
            @if($canManage)
                <button type="button" @click="editModalOpen = true"
                    class="px-3.5 py-2 rounded-xl bg-white hover:bg-blue-50 text-blue-700 font-semibold text-xs border border-blue-200 shadow-2xs transition cursor-pointer">
                    Edit Batch / Venue
                </button>
            @endif
        </div>
    </div>

    <!-- TOT Batch & Venue Summary Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block font-medium">Training Date (1-Day Program)</span>
                <span class="text-slate-900 font-bold text-sm block mt-0.5">{{ $totBatch->training_date?->format('d M Y') }}</span>
                <span class="text-slate-500 text-[11px]">Single-day TOT schedule</span>
            </div>

            <div>
                <span class="text-slate-400 block font-medium">Venue of TOT</span>
                <span class="text-slate-900 font-bold text-sm block mt-0.5">{{ $totBatch->venue_name }}</span>
                @if($totBatch->venue_address)
                    <span class="text-slate-500 text-[11px] block">{{ $totBatch->venue_address }}</span>
                @endif
            </div>

            <div>
                <span class="text-slate-400 block font-medium">District Scope</span>
                <span class="text-slate-900 font-bold text-sm block mt-0.5">{{ $totBatch->district?->name ?? 'State Level (All Districts)' }}</span>
                <span class="text-slate-500 text-[11px]">Created by {{ $totBatch->creator?->name ?? 'Admin' }}</span>
            </div>

            <div>
                <span class="text-slate-400 block font-medium">1-Day Trainer Attendance</span>
                <span class="text-emerald-600 font-extrabold text-base block mt-0.5">
                    {{ $presentCount }} Present <span class="text-slate-400 font-semibold text-xs">/ {{ $enrolledCount }} Enrolled</span>
                </span>
            </div>
        </div>

        @if($totBatch->remarks)
        <div class="pt-3 border-t border-slate-100 text-xs text-slate-600">
            <span class="font-semibold text-slate-700">Batch Notes:</span> {{ $totBatch->remarks }}
        </div>
        @endif
    </div>

    <!-- Add Field Trainers to Batch Panel (Super Admin / PMU only) -->
    @if($canManage)
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Add Field Trainers to TOT Batch</h2>
                <p class="text-xs text-slate-500">Select trainers from the Field Trainers directory to enroll into {{ $totBatch->batch_code }}.</p>
            </div>
            <div class="flex items-center space-x-2 text-xs">
                <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-200">
                    <span x-text="selectedTrainerIds.length">0</span> Selected
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('trainers.tot.enroll', $totBatch) }}" class="space-y-4 text-xs">
            @csrf

            <!-- Search & District Filters inside Picker -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 relative">
                    <input type="text" x-model="searchTrainer" placeholder="Search field trainer by name, phone, email, or designation..."
                        class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-xs focus:bg-white focus:border-blue-500 focus:outline-none">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <select x-model="filterDistrictId" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-50/60 text-xs focus:bg-white focus:border-blue-500 focus:outline-none">
                        <option value="">All Districts</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>

                    <button type="button" @click="toggleSelectAllVisible()"
                        class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold whitespace-nowrap transition cursor-pointer">
                        Toggle Visible
                    </button>
                </div>
            </div>

            <!-- Trainers Checklist -->
            <div class="max-h-64 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-100">
                <template x-for="trainer in visibleTrainers" :key="trainer.id">
                    <label class="flex items-center justify-between px-4 py-2.5 transition"
                        :class="trainer.already_enrolled ? 'bg-slate-50/80 opacity-65 cursor-not-allowed' : 'hover:bg-blue-50/40 cursor-pointer'">
                        <div class="flex items-center space-x-3 min-w-0">
                            <input type="checkbox"
                                name="trainer_ids[]"
                                :value="String(trainer.id)"
                                x-model="selectedTrainerIds"
                                :disabled="trainer.already_enrolled"
                                class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 shrink-0">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 truncate" x-text="trainer.name"></div>
                                <div class="text-[11px] text-slate-500 truncate">
                                    <span x-text="trainer.designation"></span>
                                    <span x-show="trainer.phone"> &bull; <span x-text="trainer.phone"></span></span>
                                    <span> &bull; <span class="text-blue-600 font-medium" x-text="trainer.district_name"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 ml-3">
                            <span x-show="trainer.already_enrolled" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Enrolled
                            </span>
                        </div>
                    </label>
                </template>

                <div x-show="visibleTrainers.length === 0" class="px-4 py-6 text-center text-slate-400">
                    No active field trainers match your search criteria.
                </div>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" :disabled="selectedTrainerIds.length === 0"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold shadow-md shadow-blue-600/20 transition cursor-pointer">
                    + Add Selected Trainers to Batch
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Selected TOT Batch Trainers List & 1-Day Digital Attendance Sheet -->
    <form action="{{ route('trainers.tot.attendance', $totBatch) }}" method="POST"
        class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        @csrf

        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Trainers List &amp; 1-Day Digital Attendance</h2>
                <p class="text-xs text-slate-500">
                    All trainers enrolled in <strong>{{ $totBatch->batch_code }}</strong> ({{ $totBatch->venue_name }}). Mark Present for the 1-day session.
                </p>
            </div>
            <div class="text-xs font-semibold text-slate-600">
                Total Enrolled: <span class="font-extrabold text-slate-900">{{ $enrolledCount }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3 w-14 text-center">S.No</th>
                        <th class="px-5 py-3">Trainer Name &amp; Contact</th>
                        <th class="px-5 py-3">Designation &amp; Department</th>
                        <th class="px-5 py-3">District / Block</th>
                        <th class="px-5 py-3 text-center w-32">
                            <div class="inline-flex items-center justify-center space-x-1">
                                <span>Present</span>
                                @if($canManage && $enrollments->isNotEmpty())
                                    <input type="checkbox"
                                        @change="document.querySelectorAll('.tot-present-cb').forEach(el => { el.checked = $event.target.checked; })"
                                        title="Toggle all trainers present"
                                        class="w-3.5 h-3.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer ml-1">
                                @endif
                            </div>
                        </th>
                        <th class="px-5 py-3">Remarks</th>
                        @if($canManage)
                            <th class="px-5 py-3 w-20 text-right">Remove</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($enrollments as $idx => $enrollment)
                    @php $trainer = $enrollment->fieldTrainer; @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 text-center font-bold text-slate-500">
                            {{ $idx + 1 }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $trainer?->name ?? 'Trainer' }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                {{ $trainer?->phone ?? 'No Phone' }}
                                @if($trainer?->email)
                                    &bull; {{ $trainer->email }}
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-slate-800">{{ $trainer?->designation ?? 'Field Master Trainer' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $trainer?->department ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">
                            <div class="font-semibold text-slate-800">{{ $trainer?->district?->name ?? 'State-wide' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $trainer?->block?->name ?? 'All Blocks' }}</div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($canManage)
                                <input type="checkbox"
                                    name="attendance[{{ $enrollment->id }}][is_present]"
                                    value="1"
                                    {{ $enrollment->is_present ? 'checked' : '' }}
                                    class="tot-present-cb w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500 cursor-pointer">
                            @else
                                @if($enrollment->is_present)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Present
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        Absent
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if($canManage)
                                <input type="text"
                                    name="attendance[{{ $enrollment->id }}][remarks]"
                                    value="{{ $enrollment->remarks }}"
                                    placeholder="Optional note"
                                    class="w-full min-w-[140px] rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                            @else
                                <span class="text-slate-600">{{ $enrollment->remarks ?: '—' }}</span>
                            @endif
                        </td>
                        @if($canManage)
                        <td class="px-5 py-3.5 text-right">
                            <button type="submit"
                                form="remove-trainer-form-{{ $trainer->id }}"
                                onclick="return confirm('Remove {{ addslashes($trainer?->name ?? 'this trainer') }} from TOT Batch?')"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                title="Remove from batch">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canManage ? 7 : 6 }}" class="px-6 py-12 text-center text-slate-400">
                            No field trainers enrolled in this TOT batch yet. {{ $canManage ? 'Select and add trainers above to begin marking attendance.' : '' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($canManage && $enrollments->isNotEmpty())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <label class="inline-flex items-center space-x-2 text-slate-700 font-medium cursor-pointer">
                <input type="checkbox" name="mark_completed" value="1"
                    {{ $totBatch->status === 'completed' ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                <span>Mark this 1-day TOT batch as <strong>Completed</strong></span>
            </label>

            <button type="submit"
                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-md shadow-emerald-600/20 transition cursor-pointer">
                Save 1-Day Trainer Attendance
            </button>
        </div>
        @endif
    </form>

    <!-- Standalone Hidden Forms for Removing Trainers (prevents nested forms) -->
    @if($canManage)
        @foreach($enrollments as $enrollment)
            @if($enrollment->fieldTrainer)
            <form id="remove-trainer-form-{{ $enrollment->fieldTrainer->id }}"
                method="POST"
                action="{{ route('trainers.tot.remove-trainer', [$totBatch, $enrollment->fieldTrainer]) }}"
                class="hidden">
                @csrf
                @method('DELETE')
            </form>
            @endif
        @endforeach

        <!-- Edit TOT Batch Modal -->
        <div x-show="editModalOpen"
            x-transition.opacity
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="editModalOpen = false">
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200" @click.away="editModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Edit TOT Batch Details</h3>
                        <p class="text-xs text-slate-500">Update batch code, 1-day date, or TOT venue.</p>
                    </div>
                    <button type="button" @click="editModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('trainers.tot.update', $totBatch) }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form_mode" value="edit">

                    @include('trainers.tot._batch_form_fields', [
                        'modelPrefix' => 'editForm',
                        'formMode' => 'edit',
                        'showStatus' => true,
                    ])

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md shadow-blue-600/20 transition cursor-pointer">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
