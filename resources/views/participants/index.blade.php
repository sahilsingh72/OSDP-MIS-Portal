@extends('layouts.app', ['title' => 'Participant Directory & Roster', 'headerTitle' => 'Participant Directory'])

@section('content')
<div class="space-y-6" x-data="{
    uploadModalOpen: false,
    createModalOpen: {{ $errors->hasAny(['name', 'designation', 'office_name', 'department', 'phone', 'email', 'batch_id']) ? 'true' : 'false' }},
    editModalOpen: false,
    deleteModalOpen: false,
    restoreModalOpen: false,
    createForm: {
        name: '{{ addslashes(old('name', '')) }}',
        designation: '{{ addslashes(old('designation', '')) }}',
        office_name: '{{ addslashes(old('office_name', '')) }}',
        department: '{{ addslashes(old('department', '')) }}',
        phone: '{{ addslashes(old('phone', '')) }}',
        email: '{{ addslashes(old('email', '')) }}',
        batch_id: '{{ old('batch_id', request('batch_id', '')) }}',
        is_active: true
    },
    editForm: {
        id: '',
        name: '',
        designation: '',
        department: '',
        office_name: '',
        phone: '',
        email: '',
        is_active: true,
        actionUrl: ''
    },
    deleteForm: {
        name: '',
        actionUrl: ''
    },
    restoreForm: {
        name: '',
        actionUrl: ''
    },
    openCreate() {
        this.createForm = {
            name: '',
            designation: '',
            office_name: '',
            department: '',
            phone: '',
            email: '',
            batch_id: '{{ request('batch_id', '') }}',
            is_active: true
        };
        this.createModalOpen = true;
    },
    openEdit(participant, url) {
        this.editForm = {
            id: participant.id,
            name: participant.name,
            designation: participant.designation || '',
            department: participant.department || '',
            office_name: participant.office_name || '',
            phone: participant.phone_masked || '',
            email: participant.email || '',
            is_active: Boolean(participant.is_active),
            actionUrl: url
        };
        this.editModalOpen = true;
    },
    openDelete(name, url) {
        this.deleteForm = {
            name: name,
            actionUrl: url
        };
        this.deleteModalOpen = true;
    },
    openRestore(name, url) {
        this.restoreForm = {
            name: name,
            actionUrl: url
        };
        this.restoreModalOpen = true;
    }
}">

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">
                    Form 2 Trainee Roster
                </span>
                <span class="text-xs text-slate-400 font-medium">OSDP MIS Policy 2.0</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight mt-1">Participants Directory & Roster</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage trainee accounts, office affiliations, and batch enrollments across all tiers</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('participants.template') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200/90 shadow-sm transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download Excel Template</span>
            </a>

            @can('participants.manage')
            <button type="button" @click="openCreate()"
                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Add Participant</span>
            </button>

            <button type="button" @click="uploadModalOpen = true"
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <span>Bulk Upload (Excel)</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- Status Tabs (Active vs Trashed) -->
    <div class="flex items-center space-x-2 border-b border-slate-200 pb-2">
        <a href="{{ route('participants.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
            class="px-3.5 py-1.5 rounded-xl font-semibold text-xs transition flex items-center space-x-2 {{ $status !== 'trashed' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <span>Active Participants</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $status !== 'trashed' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700 font-bold' }}">{{ number_format($activeCount) }}</span>
        </a>
        <a href="{{ route('participants.index', array_merge(request()->except(['status', 'page']), ['status' => 'trashed'])) }}"
            class="px-3.5 py-1.5 rounded-xl font-semibold text-xs transition flex items-center space-x-2 {{ $status === 'trashed' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <span>Trash / Deleted</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $status === 'trashed' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700 font-bold' }}">{{ number_format($trashedCount) }}</span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('participants.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search participant by name, designation, or Gram Panchayat..."
                    class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <select name="batch_id" class="w-full py-2 px-3 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none">
                    <option value="">All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->batch_code }} ({{ $b->district?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'batch_id']))
                <a href="{{ route('participants.index', ['status' => $status]) }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition" title="Clear Filters">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Participants Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5 w-16">S.No</th>
                        <th class="px-6 py-3.5">Participant Details</th>
                        <th class="px-6 py-3.5">Designation</th>
                        <th class="px-6 py-3.5">Office / Gram Panchayat</th>
                        <th class="px-6 py-3.5">Contact</th>
                        <th class="px-6 py-3.5">Enrolled Batches</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($participants as $p)
                    <tr class="hover:bg-slate-50 transition {{ $p->trashed() ? 'bg-rose-50/20' : '' }}">
                        <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                            {{ $participants->firstItem() ? ($participants->firstItem() + $loop->index) : $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-slate-900">{{ $p->name }}</span>
                                @if($p->trashed())
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">Deleted</span>
                                @elseif(!$p->is_active)
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">Inactive</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 flex items-center space-x-1.5 mt-0.5">
                                <span>{{ $p->department ?? 'General Administration' }}</span>
                                @if($p->creator)
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="text-slate-400 font-medium">Added by {{ $p->creator->name }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $p->designation ?? 'Trainee' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $p->office_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-[11px] text-slate-600">
                            <div>{{ $p->phone_masked ?? 'No phone' }}</div>
                            @if($p->email)
                            <div class="text-slate-400 text-[10px]">{{ $p->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($p->batches as $batch)
                                <a href="{{ route('batches.attendance.show', $batch) }}" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition">
                                    {{ $batch->batch_code }} ({{ $batch->district?->name }})
                                </a>
                                @empty
                                <span class="text-slate-400 text-[11px]">Not enrolled in active batch</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                @if($p->trashed())
                                    @can('restore', $p)
                                    <button type="button"
                                        @click="openRestore('{{ addslashes($p->name) }}', '{{ route('participants.restore', $p->id) }}')"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs border border-emerald-200 transition flex items-center space-x-1 shadow-2xs" title="Restore this participant">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Restore</span>
                                    </button>
                                    @endcan
                                @else
                                    @can('update', $p)
                                    <button type="button"
                                        @click="openEdit({{ json_encode($p) }}, '{{ route('participants.update', $p) }}')"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit Participant">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @endcan

                                    @can('delete', $p)
                                    <button type="button"
                                        @click="openDelete('{{ addslashes($p->name) }}', '{{ route('participants.destroy', $p) }}')"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete Participant">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            @if($status === 'trashed')
                                No soft-deleted participants found in Trash.
                            @else
                                No participants found matching your criteria. Use "+ Add Participant" or "Bulk Upload (Excel)" to register participants.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($participants->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $participants->links() }}
        </div>
        @endif
    </div>

    <!-- Add Single Participant Modal -->
    <div x-show="createModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="createModalOpen = false"></div>

            <!-- Modal Card -->
            <div class="relative inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100">
                <!-- Header -->
                <div class="relative px-6 pt-6 pb-5 bg-gradient-to-b from-blue-50/50 via-white to-white border-b border-slate-100 flex items-start justify-between">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center shadow-inner border border-blue-500/20">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">Add Single Participant</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Register a trainee into the roster & assign to batch</p>
                        </div>
                    </div>
                    <button type="button" @click="createModalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('participants.store') }}" method="POST" class="p-6 space-y-4 text-xs">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Participant Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="createForm.name" required placeholder="e.g. Rajesh Kumar Mohapatra"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Designation <span class="text-rose-500">*</span></label>
                            <input type="text" name="designation" x-model="createForm.designation" required placeholder="e.g. Panchayat Executive Officer"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Office / Gram Panchayat <span class="text-rose-500">*</span></label>
                            <input type="text" name="office_name" x-model="createForm.office_name" required placeholder="e.g. Baranga GP Office"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Department <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <input type="text" name="department" x-model="createForm.department" placeholder="e.g. PR & DW"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Contact Phone <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <input type="tel" name="phone" x-model="createForm.phone" placeholder="e.g. 9861012345" maxlength="20"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Email ID <span class="text-slate-400 font-normal">(Optional)</span></label>
                            <input type="email" name="email" x-model="createForm.email" placeholder="e.g. trainee@odisha.gov.in"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        </div>

                        <!-- Target Training Batch (Optional) -->
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">
                                Target Training Batch <span class="text-slate-400 font-normal">(Optional instant enrollment)</span>
                            </label>
                            <div class="relative">
                                <select name="batch_id" x-model="createForm.batch_id"
                                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-3.5 pr-9 text-xs text-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
                                    <option value="">-- General Roster (No specific batch) --</option>
                                    @foreach($batches as $b)
                                        <option value="{{ $b->id }}">
                                            {{ $b->batch_code }} &bull; {{ $b->district?->name }} ({{ $b->scheduled_date->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="sm:col-span-2 pt-1">
                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="createForm.is_active" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="font-medium text-slate-700">Account is Active and eligible for batch nominations</span>
                            </label>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-end space-x-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModalOpen = false"
                            class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Save Participant</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Participant Modal -->
    <div x-show="editModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="editModalOpen = false"></div>

            <div class="relative inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100">
                <div class="relative px-6 pt-6 pb-5 bg-gradient-to-b from-blue-50/40 via-white to-white border-b border-slate-100 flex items-start justify-between">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">Edit Participant Details</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Update trainee record & affiliations</p>
                        </div>
                    </div>
                    <button type="button" @click="editModalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="editForm.actionUrl" method="POST" class="p-6 space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Participant Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editForm.name" required
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Designation <span class="text-rose-500">*</span></label>
                            <input type="text" name="designation" x-model="editForm.designation" required
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Office / GP <span class="text-rose-500">*</span></label>
                            <input type="text" name="office_name" x-model="editForm.office_name" required
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Department</label>
                            <input type="text" name="department" x-model="editForm.department"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Contact Phone</label>
                            <input type="text" name="phone" x-model="editForm.phone" placeholder="e.g. 9861012345"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Email ID</label>
                            <input type="email" name="email" x-model="editForm.email" placeholder="e.g. trainee@gov.in"
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <div class="sm:col-span-2 pt-1">
                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" x-model="editForm.is_active" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="font-medium text-slate-700">Account is Active and eligible for batch nominations</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2.5 pt-3 border-t border-slate-100">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (Soft Delete) -->
    <div x-show="deleteModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="deleteModalOpen = false"></div>

            <div class="relative inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100 p-6">
                <div class="flex items-center space-x-3.5 mb-4">
                    <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-200 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Delete Participant</h3>
                        <p class="text-xs text-slate-500">Soft delete with instant recovery support</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200/80 text-xs text-amber-800 space-y-1 mb-4">
                    <div class="font-bold flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>Soft Delete Protection</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-amber-700">
                        Participant <strong class="text-slate-900" x-text="deleteForm.name"></strong> will be moved to the <strong>Trash</strong> tab. Historical training data remains safe and they can be restored anytime.
                    </p>
                </div>

                <form :action="deleteForm.actionUrl" method="POST" class="flex items-center justify-end space-x-2.5 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-md shadow-rose-600/20 transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Delete Participant</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div x-show="restoreModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="restoreModalOpen = false"></div>

            <div class="relative inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100 p-6">
                <div class="flex items-center space-x-3.5 mb-4">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 tracking-tight">Restore Participant</h3>
                        <p class="text-xs text-slate-500">Recover participant to active roster</p>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-xs text-emerald-800 space-y-1 mb-4">
                    <div class="font-bold flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Roster Reinstatement</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-emerald-700">
                        Participant <strong class="text-slate-900" x-text="restoreForm.name"></strong> will be restored back to the <strong>Active Participants</strong> roster. They will be immediately available for batch assignments and attendance tracking.
                    </p>
                </div>

                <form :action="restoreForm.actionUrl" method="POST" class="flex items-center justify-end space-x-2.5 pt-2">
                    @csrf
                    <button type="button" @click="restoreModalOpen = false" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Restore Participant</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div x-show="uploadModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
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
        <div class="flex items-center justify-center min-h-screen px-4 py-8 text-center sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="uploadModalOpen = false"></div>

            <!-- Modal Card -->
            <div class="relative inline-block w-full max-w-xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100">
                
                <!-- Header -->
                <div class="relative px-6 pt-6 pb-5 bg-gradient-to-b from-emerald-50/40 via-white to-white border-b border-slate-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shadow-inner border border-emerald-500/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 tracking-tight">Bulk Upload Participants</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Import official trainee roster via Excel (.xlsx) or CSV format</p>
                            </div>
                        </div>
                        <button type="button" @click="uploadModalOpen = false" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('participants.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs">
                    @csrf

                    <!-- Official Template Banner -->
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-emerald-600 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-xs">Official Excel Template</div>
                                <div class="text-[11px] text-slate-500">6 standard columns ready for trainee details</div>
                            </div>
                        </div>
                        <a href="{{ route('participants.template') }}"
                            class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 hover:text-emerald-800 border border-emerald-300 font-semibold text-xs shadow-2xs transition flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download .xlsx</span>
                        </a>
                    </div>

                    <!-- Target Training Batch (Optional) -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">
                            Target Training Batch <span class="text-slate-400 font-normal">(Optional enrollment)</span>
                        </label>
                        <div class="relative">
                            <select name="batch_id" class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-3.5 pr-9 text-xs text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition">
                                <option value="">-- General Roster (No specific batch) --</option>
                                @foreach($batches as $b)
                                    <option value="{{ $b->id }}" {{ request('batch_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->batch_code }} &bull; {{ $b->district?->name }} ({{ $b->scheduled_date->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Drag & Drop Upload Zone -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">
                            Spreadsheet File <span class="text-rose-500">*</span>
                        </label>

                        <input type="file" name="file" x-ref="fileInput" required accept=".xlsx,.xls,.csv" class="hidden"
                            @change="handleFile($event.target.files[0])">

                        <div
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length) { $refs.fileInput.files = $event.dataTransfer.files; handleFile($event.dataTransfer.files[0]); }"
                            @click="$refs.fileInput.click()"
                            :class="isDragging ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-300/80 bg-slate-50/50 hover:bg-emerald-50/20 hover:border-emerald-400'"
                            class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition group">
                            
                            <!-- Empty State -->
                            <div x-show="!fileName" class="space-y-2">
                                <div class="w-12 h-12 mx-auto rounded-2xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center text-slate-400 group-hover:text-emerald-600 group-hover:border-emerald-200 group-hover:scale-105 transition">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-700 group-hover:text-emerald-700 transition">Choose a file</span>
                                    <span class="text-slate-500"> or drag and drop here</span>
                                </div>
                                <div class="text-[11px] text-slate-400">Microsoft Excel (.xlsx, .xls) or CSV up to 10MB</div>
                            </div>

                            <!-- File Selected State -->
                            <div x-show="fileName" class="flex items-center justify-between p-3 rounded-xl bg-white border border-emerald-200 shadow-2xs text-left" @click.stop style="display: none;">
                                <div class="flex items-center space-x-3 truncate">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        XLS
                                    </div>
                                    <div class="truncate">
                                        <div class="font-bold text-slate-900 text-xs truncate" x-text="fileName"></div>
                                        <div class="text-[11px] text-emerald-600 font-medium" x-text="fileSize + ' &bull; Ready to import'"></div>
                                    </div>
                                </div>
                                <button type="button" @click="clearFile()" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition shrink-0 ml-2" title="Remove file">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Expected Column Format Badges -->
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Expected Column Headers</span>
                            <span class="text-[11px] text-slate-400">Row 1 in spreadsheet</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <div class="px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                                <span class="font-medium text-slate-700">Participant Name</span>
                                <span class="text-[10px] text-rose-500 font-bold">*</span>
                            </div>
                            <div class="px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                                <span class="font-medium text-slate-700">Designation</span>
                                <span class="text-[10px] text-rose-500 font-bold">*</span>
                            </div>
                            <div class="px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                                <span class="font-medium text-slate-700">Office / GP</span>
                                <span class="text-[10px] text-rose-500 font-bold">*</span>
                            </div>
                            <div class="px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                                <span class="font-medium text-slate-600">Department</span>
                                <span class="text-[10px] text-slate-400 font-medium">opt</span>
                            </div>
                            <div class="px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                                <span class="font-medium text-slate-600">Mobile No</span>
                                <span class="text-[10px] text-slate-400 font-medium">opt</span>
                            </div>
                            <div class="px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs flex items-center justify-between">
                                <span class="font-medium text-slate-600">Email ID</span>
                                <span class="text-[10px] text-slate-400 font-medium">opt</span>
                            </div>
                        </div>
                        <div class="text-[11px] text-amber-700/90 flex items-center space-x-1.5 pt-0.5">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            <span>Attendance is not imported; it must be recorded manually on Form 2.</span>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="uploadModalOpen = false"
                            class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <span>Upload & Import</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
