@extends('layouts.app', ['title' => 'Field Trainers Directory', 'headerTitle' => 'Field Trainers'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs">
        <div class="space-y-1">
            <div class="flex items-center space-x-2.5">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    FACULTY ROSTER
                </span>
                <span class="text-xs text-slate-400 font-medium">OSDP MIS Policy 2.0</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Field Trainers Directory</h1>
            <p class="text-xs text-slate-500 max-w-2xl">
                Provision and manage certified Master Trainers, user login accounts, and local geographic jurisdictions (District &rarr; Block &rarr; Gram Panchayat).
            </p>
        </div>

        @if(auth()->user()->can('trainers.create') || auth()->user()->hasRole(['super-admin', 'pmu', 'district-coordinator']))
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('trainers.create') }}"
                class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Add Field Trainer</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Trainers</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalTrainers) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg border border-blue-100">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Active Faculty</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($activeTrainers) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg border border-emerald-100">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-rose-500 uppercase tracking-wider">Inactive / Suspended</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($inactiveTrainers) }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center font-bold text-lg border border-rose-100">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form method="GET" action="{{ route('trainers.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search trainer by name, email, phone, or department..."
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
                    @foreach($filterDistricts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <select name="status" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 focus:bg-white focus:border-blue-500 focus:outline-none transition">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold transition shrink-0">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'district_id', 'status']))
                    <a href="{{ route('trainers.index') }}" class="p-2.5 text-slate-400 hover:text-slate-600 transition shrink-0" title="Clear Filters">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Trainers Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-5 py-3 w-16 text-center">S.No</th>
                        <th class="px-5 py-3">Trainer Details</th>
                        <th class="px-5 py-3">Designation & Department</th>
                        <th class="px-5 py-3">Jurisdiction (District &rarr; Block &rarr; GP)</th>
                        <th class="px-5 py-3 text-center">Batches Assigned</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($trainers as $index => $trainer)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3.5 text-center text-slate-400 font-medium">
                            {{ $trainers->firstItem() + $index }}
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 font-extrabold flex items-center justify-center shrink-0 border border-blue-200">
                                    {{ strtoupper(substr($trainer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-xs">{{ $trainer->name }}</div>
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $trainer->email }}</div>
                                    <div class="text-[11px] text-blue-600 font-medium">{{ $trainer->phone ?? 'No Phone' }}</div>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-800">{{ $trainer->designation ?? 'Field Master Trainer' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $trainer->department ?? 'General' }}</div>
                        </td>

                        <td class="px-5 py-3.5">
                            <div class="space-y-0.5">
                                <div class="font-semibold text-slate-800 flex items-center space-x-1.5">
                                    <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span>{{ $trainer->district?->name ?? 'State-wide' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 pl-3.5">
                                    <span>Block: <strong class="text-slate-700">{{ $trainer->block?->name ?? 'All Blocks' }}</strong></span>
                                </div>
                                <div class="text-[11px] text-slate-500 pl-3.5">
                                    <span>GP: <strong class="text-slate-700">{{ $trainer->gramPanchayat?->name ?? 'All GPs' }}</strong></span>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $trainer->user?->trainerBatches?->count() ?? 0 }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5 text-center">
                            @if($trainer->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Suspended
                                </span>
                            @endif
                        </td>

                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('trainers.edit', $trainer) }}"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition"
                                    title="Edit Trainer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                <form action="{{ route('trainers.toggle-status', $trainer) }}" method="POST" class="inline" onsubmit="return confirm('Toggle active status for {{ addslashes($trainer->name) }}?')">
                                    @csrf
                                    <button type="submit"
                                        class="p-1.5 rounded-lg {{ $trainer->is_active ? 'text-rose-500 hover:text-rose-700 hover:bg-rose-50' : 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50' }} transition"
                                        title="{{ $trainer->is_active ? 'Suspend Account' : 'Activate Account' }}">
                                        @if($trainer->is_active)
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <p class="font-bold text-slate-700 text-sm">No Field Trainers Found</p>
                            <p class="text-xs text-slate-400 mt-1">Get started by provisioning a new field master trainer account.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trainers->hasPages())
        <div class="px-5 py-3 border-t border-slate-200">
            {{ $trainers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
