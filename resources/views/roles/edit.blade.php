@extends('layouts.app', ['title' => 'Assign Permissions - ' . $roleInfo['display_name'], 'headerTitle' => 'Role Permission Allocation'])

@section('content')
<div class="space-y-6 pb-20" x-data="rolePermissionsManager({
    total: {{ $totalPermissions }},
    initial: {{ json_encode($assignedPermissions) }}
})">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('roles.index') }}" class="hover:text-blue-600 transition flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>System Roles</span>
                </a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">{{ $roleInfo['display_name'] }}</span>
                <span>/</span>
                <span class="text-blue-600 font-semibold">Assign Permissions</span>
            </div>

            <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center space-x-3">
                <span>Assign Permissions: {{ $roleInfo['display_name'] }}</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Toggle specific feature capabilities and access privileges for this role</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-sm transition">
                Back to Roles Directory
            </a>
            <a href="{{ route('roles.matrix') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 shadow-sm transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Compare Matrix</span>
            </a>
        </div>
    </div>

    <!-- Role Summary Metadata Banner -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center font-extrabold text-lg shadow-md shadow-blue-500/20 flex-shrink-0">
                    {{ substr($roleInfo['display_name'], 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center space-x-2.5">
                        <h2 class="text-base font-bold text-slate-900">{{ $roleInfo['display_name'] }}</h2>
                        <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                            {{ $role->name }}
                        </span>
                        @if($roleInfo['is_core'])
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            System Core Role
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Custom Role
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-1 max-w-3xl leading-relaxed">{{ $roleInfo['description'] }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-6 border-t lg:border-t-0 lg:border-l border-slate-100 pt-3 lg:pt-0 lg:pl-6 text-xs flex-shrink-0">
                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Assigned Users</span>
                    <span class="font-bold text-slate-800 text-sm">{{ $role->users_count }} {{ Str::plural('Account', $role->users_count) }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] font-medium">Active Clearance</span>
                    <span class="font-bold text-blue-600 text-sm">
                        <span x-text="selectedCount"></span> / {{ $totalPermissions }}
                        <span class="text-slate-400 text-xs font-normal">(<span x-text="coveragePercentage"></span>%)</span>
                    </span>
                </div>
            </div>
        </div>

        @if($role->name === 'super-admin')
        <div class="mt-4 p-3 bg-amber-50/80 border border-amber-200/90 rounded-xl flex items-start space-x-3 text-xs text-amber-900">
            <svg class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <strong class="font-semibold">Super Administrator Policy Clearance:</strong>
                Users with the Super Admin role bypass Gate checks automatically (`Gate::before`) and possess absolute authority. The permissions synced here serve as system explicit grant records.
            </div>
        </div>
        @endif
    </div>

    <!-- Interactive Filter & Global Bulk Controls -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4 sticky top-4 z-20 backdrop-blur-md bg-white/95">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text"
                x-model="searchQuery"
                placeholder="Filter permissions by keyword (e.g. batch, evidence, user)..."
                class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 bg-slate-50/60 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition">
            <button type="button"
                x-show="searchQuery.length > 0"
                @click="searchQuery = ''"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                style="display: none;">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Global Select/Clear Actions -->
        <div class="flex items-center space-x-3 text-xs">
            <button type="button"
                @click="selectAll()"
                class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold border border-blue-200/70 transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Select All</span>
            </button>

            <button type="button"
                @click="deselectAll()"
                class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold border border-slate-200 transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Clear All</span>
            </button>

            <div class="h-4 w-px bg-slate-200"></div>

            <div class="text-slate-600 font-medium">
                <span class="font-bold text-slate-900" x-text="selectedCount"></span> of {{ $totalPermissions }} active
            </div>
        </div>
    </div>

    <!-- Main Assignment Form -->
    <form id="permissionForm" action="{{ route('roles.permissions.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($groupedPermissions as $categoryName => $group)
        @php
            $catSlug = Str::slug($categoryName);
            $groupPermissions = $group['permissions'];
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden category-card"
            x-show="categoryMatchesSearch('{{ addslashes($categoryName) }}', {{ json_encode($groupPermissions->pluck('name')->toArray()) }}, {{ json_encode($groupPermissions->pluck('label')->toArray()) }})"
            x-transition>

            <!-- Category Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-700 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ $categoryName }}</h3>
                        <p class="text-[11px] text-slate-500">{{ $group['meta']['description'] }}</p>
                    </div>
                </div>

                <!-- Category Bulk Selection & Counter -->
                <div class="flex items-center space-x-3 text-xs">
                    <span class="text-slate-500 text-[11px] font-medium">
                        <span class="font-bold text-slate-800" x-text="categorySelectedCount({{ json_encode($groupPermissions->pluck('name')->toArray()) }})"></span> / {{ $groupPermissions->count() }} selected
                    </span>

                    <button type="button"
                        @click="toggleCategory({{ json_encode($groupPermissions->pluck('name')->toArray()) }})"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition shadow-xs">
                        <span x-text="categoryAllSelected({{ json_encode($groupPermissions->pluck('name')->toArray()) }}) ? 'Deselect All' : 'Select All'"></span>
                    </button>
                </div>
            </div>

            <!-- Permission Checkbox Items Grid -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($groupPermissions as $permission)
                @php
                    $permName = $permission->name;
                    $permLabel = $permission->label;
                    $permDesc = $permission->description;
                @endphp

                <label
                    x-show="permissionMatchesSearch('{{ addslashes($permName) }}', '{{ addslashes($permLabel) }}', '{{ addslashes($permDesc) }}')"
                    :class="selected.includes('{{ $permName }}') ? 'bg-blue-50/50 border-blue-300 ring-1 ring-blue-500/20' : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/50'"
                    class="relative flex items-start p-4 rounded-xl border transition cursor-pointer select-none">

                    <!-- Checkbox Input -->
                    <div class="flex items-center h-5 mr-3 mt-0.5">
                        <input type="checkbox"
                            name="permissions[]"
                            value="{{ $permName }}"
                            x-model="selected"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                    </div>

                    <!-- Details -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-900 block"
                                :class="selected.includes('{{ $permName }}') ? 'text-blue-900' : 'text-slate-900'">
                                {{ $permLabel }}
                            </span>
                            <span class="font-mono text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 flex-shrink-0">
                                {{ $permName }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                            {{ $permDesc }}
                        </p>
                    </div>
                </label>
                @endforeach
            </div>

        </div>
        @endforeach

        <!-- Floating Sticky Bottom Bar for Quick Saving -->
        <div class="fixed bottom-0 inset-x-0 lg:pl-64 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 py-3.5 px-6 shadow-lg">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-3 text-xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <span class="text-slate-600">
                        Updating role: <strong class="text-slate-900 font-bold">{{ $roleInfo['display_name'] }}</strong>
                    </span>
                    <span class="text-slate-300">|</span>
                    <span class="text-slate-500">
                        <span class="font-bold text-slate-900" x-text="selectedCount"></span> of {{ $totalPermissions }} permissions active
                    </span>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-6 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Save & Apply Permissions</span>
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

            get coveragePercentage() {
                if (this.total === 0) return 0;
                return Math.round((this.selected.length / this.total) * 100);
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
                    // Deselect all in category
                    this.selected = this.selected.filter(name => !permNames.includes(name));
                } else {
                    // Select all in category
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
