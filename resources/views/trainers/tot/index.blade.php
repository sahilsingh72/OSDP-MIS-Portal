@extends('layouts.app', ['title' => 'TOT Batches (Training of Trainers)', 'headerTitle' => 'TOT Management'])

@section('content')
@php
    $openCreateOnLoad = $errors->any() && old('_form_mode') === 'create';
    $openEditOnLoad = $errors->any() && old('_form_mode') === 'edit';
    $defaultVenueMode = $totVenues->isNotEmpty() ? 'existing' : 'new';
@endphp
<div class="space-y-6"
    x-data="{
        createModalOpen: {{ $openCreateOnLoad ? 'true' : 'false' }},
        editModalOpen: {{ $openEditOnLoad ? 'true' : 'false' }},
        editAction: {{ \Illuminate\Support\Js::from((string) old('_edit_action', '')) }},
        createForm: {
            batch_code: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('batch_code', $suggestedCode) : $suggestedCode)) }},
            title: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('title', '') : '')) }},
            training_date: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('training_date', date('Y-m-d')) : date('Y-m-d'))) }},
            district_id: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('district_id', '') : '')) }},
            venue_mode: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('venue_mode', $defaultVenueMode) : $defaultVenueMode)) }},
            tot_venue_id: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('tot_venue_id', '') : '')) }},
            venue_name: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('venue_name', '') : '')) }},
            venue_address: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('venue_address', '') : '')) }},
            status: 'scheduled',
            remarks: {{ \Illuminate\Support\Js::from((string) ($openCreateOnLoad ? old('remarks', '') : '')) }}
        },
        editForm: {
            batch_code: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('batch_code', '') : '')) }},
            title: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('title', '') : '')) }},
            training_date: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('training_date', '') : '')) }},
            district_id: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('district_id', '') : '')) }},
            venue_mode: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('venue_mode', $defaultVenueMode) : $defaultVenueMode)) }},
            tot_venue_id: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('tot_venue_id', '') : '')) }},
            venue_name: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('venue_name', '') : '')) }},
            venue_address: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('venue_address', '') : '')) }},
            status: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('status', 'scheduled') : 'scheduled')) }},
            remarks: {{ \Illuminate\Support\Js::from((string) ($openEditOnLoad ? old('remarks', '') : '')) }}
        },
        openEditModal(batch, actionUrl) {
            this.editAction = actionUrl;
            const hasExisting = Boolean(batch.tot_venue_id);
            this.editForm = {
                batch_code: batch.batch_code || '',
                title: batch.title || '',
                training_date: batch.training_date || '',
                district_id: batch.district_id ? String(batch.district_id) : '',
                venue_mode: hasExisting ? 'existing' : 'new',
                tot_venue_id: hasExisting ? String(batch.tot_venue_id) : '',
                venue_name: hasExisting ? '' : (batch.venue_name || ''),
                venue_address: hasExisting ? '' : (batch.venue_address || ''),
                status: batch.status || 'scheduled',
                remarks: batch.remarks || ''
            };
            this.editModalOpen = true;
        }
    }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs">
        <div class="space-y-1">
            <div class="flex items-center space-x-2.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    TRAINING OF TRAINERS (TOT)
                </span>
                <span class="text-xs text-slate-400 font-medium">1-Day Training Program</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">TOT Batches &amp; Trainer Attendance</h1>
            <p class="text-xs text-slate-500 max-w-2xl">
                Create 1-day TOT batches, record the TOT venue, assign field trainers to each batch, and mark trainer attendance.
            </p>
        </div>

        <div class="flex items-center space-x-2.5 shrink-0">
            <a href="{{ route('trainers.index') }}"
                class="px-4 py-2.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                &larr; Trainers Directory
            </a>

            @if($canManage)
            <button type="button" @click="createModalOpen = true"
                class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create TOT Batch</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total TOT Batches</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalBatches) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Trainers in TOT Batches</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalEnrolled) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Trainers Present (1-Day)</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalPresent) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('trainers.tot.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by TOT batch code, title, or venue..."
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none transition">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div>
                <select name="district_id" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none transition">
                    <option value="">-- All Districts --</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <select name="status" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none transition">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>

                <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold transition shrink-0 cursor-pointer">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'district_id', 'status']))
                    <a href="{{ route('trainers.tot.index') }}" class="p-2.5 text-slate-400 hover:text-slate-600 transition shrink-0" title="Clear Filters">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TOT Batches Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3 w-14 text-center">S.No</th>
                        <th class="px-5 py-3">TOT Batch</th>
                        <th class="px-5 py-3">Training Date</th>
                        <th class="px-5 py-3">Venue of TOT</th>
                        <th class="px-5 py-3 text-center">Trainers</th>
                        <th class="px-5 py-3 text-center">1-Day Attendance</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($batches as $index => $batch)
                    @php
                        $batchJson = [
                            'batch_code' => $batch->batch_code,
                            'title' => $batch->title,
                            'training_date' => $batch->training_date?->format('Y-m-d'),
                            'district_id' => $batch->district_id,
                            'tot_venue_id' => $batch->tot_venue_id,
                            'venue_name' => $batch->venue_name,
                            'venue_address' => $batch->venue_address,
                            'status' => $batch->status,
                            'remarks' => $batch->remarks,
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 text-center text-slate-400 font-medium">
                            {{ $batches->firstItem() + $index }}
                        </td>

                        <td class="px-5 py-3.5">
                            <a href="{{ route('trainers.tot.show', $batch) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline text-xs block">
                                {{ $batch->batch_code }}
                            </a>
                            @if($batch->title)
                                <div class="text-[11px] text-slate-600 mt-0.5">{{ $batch->title }}</div>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="font-semibold text-slate-800">{{ $batch->training_date?->format('d M Y') }}</div>
                            <span class="text-[10px] font-medium text-slate-400">1-Day Session</span>
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-900">{{ $batch->venue_name }}</div>
                            @if($batch->venue_address)
                                <div class="text-[11px] text-slate-500">{{ $batch->venue_address }}</div>
                            @endif
                            <div class="text-[10px] text-blue-600 font-medium mt-0.5">
                                {{ $batch->district?->name ?? 'State Level' }}
                            </div>
                        </td>

                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $batch->enrolled_count }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            @if($batch->enrolled_count > 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $batch->present_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ $batch->present_count }} / {{ $batch->enrolled_count }} Present
                                </span>
                            @else
                                <span class="text-slate-400 italic text-[11px]">No trainers added</span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            @if($batch->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                    Completed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase">
                                    Scheduled
                                </span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end space-x-1.5">
                                <a href="{{ route('trainers.tot.show', $batch) }}"
                                    class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-2xs transition inline-flex items-center space-x-1">
                                    <span>{{ $canManage ? 'Trainers & Attendance' : 'View Roster' }}</span>
                                    <span>&rarr;</span>
                                </a>

                                @if($canManage)
                                    <button type="button"
                                        @click="openEditModal({{ \Illuminate\Support\Js::from($batchJson) }}, '{{ route('trainers.tot.update', $batch) }}')"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition cursor-pointer"
                                        title="Edit TOT Batch">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <form action="{{ route('trainers.tot.destroy', $batch) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete TOT Batch {{ addslashes($batch->batch_code) }} and its attendance records?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                            title="Delete TOT Batch">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <p class="font-bold text-slate-700 text-sm">No TOT Batches Found</p>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ $canManage ? 'Click "Create TOT Batch" to schedule a 1-day Training of Trainers session.' : 'No TOT batches match the selected filters.' }}
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
        <div class="px-5 py-3 border-t border-slate-200">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

    @if($canManage)
    <!-- Create TOT Batch Modal -->
    <div x-show="createModalOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        style="display: none;"
        @keydown.escape.window="createModalOpen = false">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200" @click.away="createModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Create 1-Day TOT Batch</h3>
                    <p class="text-xs text-slate-500">Define TOT batch code, training date, and the venue where TOT happens.</p>
                </div>
                <button type="button" @click="createModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('trainers.tot.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="_form_mode" value="create">

                @include('trainers.tot._batch_form_fields', [
                    'modelPrefix' => 'createForm',
                    'formMode' => 'create',
                    'showStatus' => false,
                ])

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="createModalOpen = false"
                        class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md shadow-blue-600/20 transition cursor-pointer">
                        Create Batch &amp; Add Trainers &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit TOT Batch Modal -->
    <div x-show="editModalOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
        style="display: none;"
        @keydown.escape.window="editModalOpen = false">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-slate-200" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Edit TOT Batch</h3>
                    <p class="text-xs text-slate-500">Update TOT batch details, training date, or venue.</p>
                </div>
                <button type="button" @click="editModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" :action="editAction" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form_mode" value="edit">
                <input type="hidden" name="_edit_action" :value="editAction">

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
