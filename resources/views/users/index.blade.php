@extends('layouts.app', ['title' => 'User Management', 'headerTitle' => 'Users & Access Control'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">System Users & Roles Directory</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage administrative accounts, role allocations, and district scopes</p>
        </div>

        <div class="flex items-center space-x-3">
            @can('roles.manage')
            <a href="{{ route('roles.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 shadow-sm transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Roles & Permissions</span>
            </a>
            @endcan

            @can('users.create')
            <a href="{{ route('users.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <span>Create New User</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Name</th>
                        <th class="px-6 py-3.5">Email</th>
                        <th class="px-6 py-3.5">Designation / Dept</th>
                        <th class="px-6 py-3.5">Assigned Role</th>
                        <th class="px-6 py-3.5">District Scope</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                            {{ $u->name }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-mono text-[11px] whitespace-nowrap">
                            {{ $u->email }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $u->designation ?? 'N/A' }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $u->department ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 uppercase">
                                {{ str_replace('-', ' ', $u->roles->first()?->name ?? 'None') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($u->assignedDistricts->isNotEmpty())
                                <span class="text-xs font-semibold text-slate-700">{{ $u->assignedDistricts->pluck('name')->implode(', ') }}</span>
                            @else
                                <span class="text-slate-400 text-[11px]">Statewide / Unrestricted</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $u->is_active ? 'Active' : 'Suspended' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                            @can('users.update')
                            <a href="{{ route('users.edit', $u) }}" class="text-blue-600 hover:text-blue-900 font-semibold">Edit</a>
                            @if($u->id !== auth()->id())
                            <form action="{{ route('users.toggle-status', $u) }}" method="POST" class="inline" onsubmit="return confirm('Toggle status for this account?')">
                                @csrf
                                <button type="submit" class="text-xs font-semibold {{ $u->is_active ? 'text-rose-600 hover:text-rose-800' : 'text-emerald-600 hover:text-emerald-800' }}">
                                    {{ $u->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            @endif
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
