@extends('layouts.app', ['title' => 'Create Custom Role', 'headerTitle' => 'Role Creation & Access Allocation'])

@section('content')
<div class="space-y-6 pb-20" x-data="rolePermissionsManager({
    total: {{ $totalPermissions }},
    initial: {{ json_encode(old('permissions', [])) }}
})">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('roles.index') }}" class="hover:text-blue-600 transition flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>System Roles</span>
                </a>
                <span>/</span>
                <span class="text-blue-600 font-semibold">Create New Role</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Provision Custom System Role</h1>
            <p class="text-xs text-slate-500 mt-0.5">Define role identifier and configure initial module clearance permissions</p>
        </div>

        <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-sm transition">
            Back to Directory
        </a>
    </div>

    <!-- Creation Form -->
    <form id="createRoleForm" action="{{ route('roles.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Role Identifier Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center space-x-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Role Identification</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-800 mb-1">
                        Role Slug / Key <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                        placeholder="e.g. block-development-officer"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none @error('name') border-rose-500 @enderror">
                    <p class="text-[11px] text-slate-400 mt-1">Lowercase letters, numbers, hyphens only. Used internally by security policies.</p>
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="display_name" class="block text-xs font-bold text-slate-800 mb-1">
                        Human-Readable Title
                    </label>
                    <input type="text"
                        name="display_name"
                        id="display_name"
                        value="{{ old('display_name') }}"
                        placeholder="e.g. Block Development Officer (BDO)"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Friendly title displayed throughout the MIS administrative interface.</p>
                </div>
            </div>
        </div>

        <!-- Toolbar for Permissions Selection -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 sticky top-4 z-20 backdrop-blur-md bg-white/95">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                    x-model="searchQuery"
                    placeholder="Filter permissions by keyword..."
                    class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition">
            </div>

            <div class="flex items-center space-x-3 text-xs">
                <button type="button"
                    @click="selectAll()"
                    class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold border border-blue-200/70 transition">
                    Select All
                </button>
                <button type="button"
                    @click="deselectAll()"
                    class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold border border-slate-200 transition">
                    Clear All
                </button>
                <div class="h-4 w-px bg-slate-200"></div>
                <div class="text-slate-600 font-medium">
                    <span class="font-bold text-slate-900" x-text="selectedCount"></span> of {{ $totalPermissions }} selected
                </div>
            </div>
        </div>

        <!-- Categorized Permission Blocks -->
        @foreach($groupedPermissions as $categoryName => $group)
        @php
            $groupPermissions = $group['permissions'];
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden"
            x-show="categoryMatchesSearch('{{ addslashes($categoryName) }}', {{ json_encode($groupPermissions->pluck('name')->toArray()) }}, {{ json_encode($groupPermissions->pluck('label')->toArray()) }})"
            x-transition>

            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">{{ $categoryName }}</h3>
                    <p class="text-[11px] text-slate-500">{{ $group['meta']['description'] }}</p>
                </div>

                <div class="flex items-center space-x-3 text-xs">
                    <span class="text-slate-500 text-[11px] font-medium">
                        <span class="font-bold text-slate-800" x-text="categorySelectedCount({{ json_encode($groupPermissions->pluck('name')->toArray()) }})"></span> / {{ $groupPermissions->count() }}
                    </span>
                    <button type="button"
                        @click="toggleCategory({{ json_encode($groupPermissions->pluck('name')->toArray()) }})"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                        <span x-text="categoryAllSelected({{ json_encode($groupPermissions->pluck('name')->toArray()) }}) ? 'Deselect All' : 'Select All'"></span>
                    </button>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($groupPermissions as $permission)
                @php
                    $permName = $permission->name;
                    $permLabel = $permission->label;
                    $permDesc = $permission->description;
                @endphp

                <label
                    x-show="permissionMatchesSearch('{{ addslashes($permName) }}', '{{ addslashes($permLabel) }}', '{{ addslashes($permDesc) }}')"
                    :class="selected.includes('{{ $permName }}') ? 'bg-blue-50/50 border-blue-300 ring-1 ring-blue-500/20' : 'bg-white border-slate-200 hover:border-slate-300'"
                    class="relative flex items-start p-4 rounded-xl border transition cursor-pointer select-none">

                    <div class="flex items-center h-5 mr-3 mt-0.5">
                        <input type="checkbox"
                            name="permissions[]"
                            value="{{ $permName }}"
                            x-model="selected"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-900 block">{{ $permLabel }}</span>
                            <span class="font-mono text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 flex-shrink-0">
                                {{ $permName }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ $permDesc }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        <!-- Floating Sticky Bottom Bar -->
        <div class="fixed bottom-0 inset-x-0 lg:pl-64 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 py-3.5 px-6 shadow-lg">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-3 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-600">Creating new custom role with <strong class="text-slate-900 font-bold" x-text="selectedCount"></strong> permissions assigned</span>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Create Role & Assign Permissions</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    function rolePermissionsManager(config) {
        return {
            total: config.total || 0,
            selected: config.initial || [],
            searchQuery: '',

            get selectedCount() {
                return this.selected.length;
            },

            selectAll() {
                const checkboxes = document.querySelectorAll('input[type="checkbox"][name="permissions[]"]');
                const allValues = [];
                checkboxes.forEach(cb => allValues.push(cb.value));
                this.selected = [...new Set(allValues)];
            },

            deselectAll() {
                this.selected = [];
            },

            categorySelectedCount(permNames) {
                return permNames.filter(name => this.selected.includes(name)).length;
            },

            categoryAllSelected(permNames) {
                if (permNames.length === 0) return false;
                return permNames.every(name => this.selected.includes(name));
            },

            toggleCategory(permNames) {
                if (this.categoryAllSelected(permNames)) {
                    this.selected = this.selected.filter(name => !permNames.includes(name));
                } else {
                    const newSet = new Set([...this.selected, ...permNames]);
                    this.selected = Array.from(newSet);
                }
            },

            permissionMatchesSearch(name, label, desc) {
                if (!this.searchQuery.trim()) return true;
                const q = this.searchQuery.toLowerCase().trim();
                return name.toLowerCase().includes(q) ||
                       label.toLowerCase().includes(q) ||
                       desc.toLowerCase().includes(q);
            },

            categoryMatchesSearch(categoryName, names, labels) {
                if (!this.searchQuery.trim()) return true;
                const q = this.searchQuery.toLowerCase().trim();
                if (categoryName.toLowerCase().includes(q)) return true;
                return names.some(n => n.toLowerCase().includes(q)) ||
                       labels.some(l => l.toLowerCase().includes(q));
            }
        };
    }
</script>
@endpush
