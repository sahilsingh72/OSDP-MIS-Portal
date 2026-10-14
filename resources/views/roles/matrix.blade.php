@extends('layouts.app', ['title' => 'Permissions Matrix', 'headerTitle' => 'Role Permission Matrix Overview'])

@section('content')
<div class="space-y-6" x-data="{ searchQuery: '' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('roles.index') }}" class="hover:text-blue-600 transition flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>System Roles</span>
                </a>
                <span>/</span>
                <span class="text-blue-600 font-semibold">Governance Matrix</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Permissions Clearance Matrix</h1>
            <p class="text-xs text-slate-500 mt-0.5">High-level comparative mapping of all operational permissions allocated to system roles</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-sm transition">
                Back to Roles Directory
            </a>
            @can('roles.manage')
            <a href="{{ route('roles.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Role</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex items-center justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text"
                x-model="searchQuery"
                placeholder="Search permission or module in matrix..."
                class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition">
        </div>

        <div class="flex items-center space-x-4 text-xs text-slate-500">
            <div class="flex items-center space-x-1.5">
                <div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px]">
                    ✓
                </div>
                <span>Permitted</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <div class="w-4 h-4 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-[10px]">
                    —
                </div>
                <span>Restricted</span>
            </div>
        </div>
    </div>

    <!-- Matrix Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <!-- Table Headers -->
                <thead class="bg-slate-900 text-white font-semibold">
                    <tr>
                        <th class="px-6 py-4 min-w-[280px] sticky left-0 z-10 bg-slate-900 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.3)]">
                            <span class="uppercase tracking-wider text-[11px] text-slate-300">Feature Permission / Scope</span>
                        </th>
                        @foreach($roles as $r)
                        <th class="px-4 py-4 text-center min-w-[140px] whitespace-nowrap border-l border-slate-800">
                            <div class="font-bold text-white text-xs">{{ $r->display_name }}</div>
                            <div class="font-mono text-[10px] text-slate-400 mt-0.5">{{ $r->name }}</div>
                            <div class="mt-2">
                                <a href="{{ route('roles.permissions.edit', $r) }}"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-600/30 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/40 transition">
                                    Edit Role
                                </a>
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>

                <!-- Table Body with Grouped Permissions -->
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @foreach($groupedPermissions as $categoryName => $group)
                    @php
                        $groupPermissions = $group['permissions'];
                    @endphp

                    <!-- Category Group Row -->
                    <tr class="bg-slate-100/80 text-slate-900 font-bold">
                        <td colspan="{{ 1 + $roles->count() }}" class="px-6 py-2.5 text-[11px] uppercase tracking-wider bg-slate-100 text-slate-800 border-t border-b border-slate-200">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                <span>{{ $categoryName }}</span>
                                <span class="text-slate-400 font-normal">({{ $groupPermissions->count() }})</span>
                            </div>
                        </td>
                    </tr>

                    <!-- Individual Permission Rows -->
                    @foreach($groupPermissions as $permission)
                    @php
                        $pName = $permission->name;
                        $pLabel = $permission->label;
                    @endphp
                    <tr class="hover:bg-slate-50 transition"
                        x-show="!searchQuery.trim() ||
                                '{{ addslashes(strtolower($pName)) }}'.includes(searchQuery.toLowerCase().trim()) ||
                                '{{ addslashes(strtolower($pLabel)) }}'.includes(searchQuery.toLowerCase().trim()) ||
                                '{{ addslashes(strtolower($categoryName)) }}'.includes(searchQuery.toLowerCase().trim())">

                        <!-- Permission Label Column -->
                        <td class="px-6 py-3 sticky left-0 z-10 bg-white hover:bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            <div class="font-semibold text-slate-900">{{ $pLabel }}</div>
                            <div class="font-mono text-[10px] text-slate-400">{{ $pName }}</div>
                        </td>

                        <!-- Role Status Columns -->
                        @foreach($roles as $r)
                        @php
                            $hasPerm = $r->hasPermissionTo($pName);
                        @endphp
                        <td class="px-4 py-3 text-center border-l border-slate-100">
                            @if($hasPerm)
                            <div class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs shadow-xs" title="Enabled for {{ $r->name }}">
                                ✓
                            </div>
                            @else
                            <div class="inline-flex items-center justify-center w-6 h-6 rounded-full text-slate-300 text-xs">
                                —
                            </div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
