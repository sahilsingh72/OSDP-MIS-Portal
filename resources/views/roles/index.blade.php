@extends('layouts.app', ['title' => 'Roles & Permissions Governance', 'headerTitle' => 'Access Control Governance'])

@section('content')
<div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">
                    Super Admin Clearance
                </span>
                <span class="text-xs text-slate-400 font-medium">OSDP MIS Policy 2.0</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight mt-1">Role Permissions & Access Governance</h1>
            <p class="text-xs text-slate-500 mt-0.5">Assign, audit, and configure module privilege matrices across all administrative and operational roles</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('roles.matrix') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200/90 shadow-sm transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Permissions Matrix</span>
            </a>

            @can('roles.manage')
            <a href="{{ route('roles.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create New Role</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Governance KPI Metrics Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Defined Roles</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $stats['total_roles'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1 flex items-center space-x-1">
                <span class="text-emerald-600 font-semibold">6 System Core</span>
                <span>+ {{ max(0, $stats['total_roles'] - 6) }} Custom</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Permissions</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $stats['total_permissions'] }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Granular module privileges active</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Users Bound</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-2">{{ $stats['total_active_users'] }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1">100% RBAC Enforced</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cache Synchronization</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 mt-2 flex items-center space-x-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Live Sync</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Changes applied instantaneously</div>
        </div>
    </div>

    <!-- Roles Grid Directory -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($roles as $role)
        @php
            $coveragePct = $stats['total_permissions'] > 0 ? round(($role->permissions_count / $stats['total_permissions']) * 100) : 0;
            $colorClasses = match($role->badge_color) {
                'rose' => 'bg-rose-50 text-rose-700 border-rose-200',
                'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'blue' => 'bg-blue-50 text-blue-700 border-blue-200',
                'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
                'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'purple' => 'bg-purple-50 text-purple-700 border-purple-200',
                default => 'bg-slate-50 text-slate-700 border-slate-200',
            };
            $barColor = match($role->badge_color) {
                'rose' => 'bg-rose-500',
                'indigo' => 'bg-indigo-500',
                'blue' => 'bg-blue-600',
                'amber' => 'bg-amber-500',
                'emerald' => 'bg-emerald-500',
                'purple' => 'bg-purple-500',
                default => 'bg-slate-600',
            };
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden">
            <!-- Card Body -->
            <div class="p-6 space-y-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-base font-bold text-slate-900 tracking-tight">{{ $role->display_name }}</h2>
                        </div>
                        <span class="inline-block mt-1 font-mono text-[11px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                            {{ $role->name }}
                        </span>
                    </div>

                    @if($role->is_core)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        System Core
                    </span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Custom Role
                    </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 leading-relaxed min-h-[3rem]">
                    {{ $role->description }}
                </p>

                <!-- Users & Permissions Indicators -->
                <div class="pt-3 border-t border-slate-100 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Assigned Users</span>
                        </span>
                        <span class="font-bold text-slate-800">{{ $role->users_count }} active {{ Str::plural('account', $role->users_count) }}</span>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Privilege Clearance</span>
                            </span>
                            <span class="font-bold text-slate-900">{{ $role->permissions_count }} / {{ $stats['total_permissions'] }} ({{ $coveragePct }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="{{ $barColor }} h-1.5 rounded-full transition-all duration-500" style="width: {{ $coveragePct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Action Footer -->
            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('roles.permissions.edit', $role) }}"
                    class="flex-1 text-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center justify-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Assign Permissions</span>
                </a>

                @if(!$role->is_core && $role->users_count === 0)
                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Are you sure you want to permanently delete custom role {{ $role->name }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition" title="Delete custom role">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
