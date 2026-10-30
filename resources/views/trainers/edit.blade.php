@extends('layouts.app', ['title' => 'Edit Field Trainer', 'headerTitle' => 'Field Trainers'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
    x-data="{
        districtId: '{{ old('district_id', $trainer->district_id ?? $trainer->assignedDistricts->first()?->id ?? '') }}',
        blockId: '{{ old('block_id', $trainer->block_id ?? '') }}',
        gpId: '{{ old('gram_panchayat_id', $trainer->gram_panchayat_id ?? '') }}',
        blocks: @json($blocks),
        gps: @json($gps),
        loadingBlocks: false,
        loadingGps: false,
        async loadBlocks(district, selectedBlock = null) {
            if (!district) {
                this.blocks = [];
                this.gps = [];
                this.blockId = '';
                this.gpId = '';
                return;
            }
            this.loadingBlocks = true;
            try {
                const res = await fetch(`/api/districts/${district}/blocks`);
                this.blocks = await res.json();
                if (selectedBlock) {
                    this.blockId = String(selectedBlock);
                    await this.loadGps(selectedBlock, '{{ old('gram_panchayat_id', $trainer->gram_panchayat_id ?? '') }}');
                } else {
                    this.blockId = '';
                    this.gpId = '';
                    this.gps = [];
                }
            } catch (e) {
                console.error('Failed to load blocks:', e);
            } finally {
                this.loadingBlocks = false;
            }
        },
        async loadGps(block, selectedGp = null) {
            if (!block) {
                this.gps = [];
                this.gpId = '';
                return;
            }
            this.loadingGps = true;
            try {
                const res = await fetch(`/api/blocks/${block}/gram-panchayats`);
                this.gps = await res.json();
                if (selectedGp) {
                    this.gpId = String(selectedGp);
                } else {
                    this.gpId = '';
                }
            } catch (e) {
                console.error('Failed to load gram panchayats:', e);
            } finally {
                this.loadingGps = false;
            }
        }
    }">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-xs text-slate-500">
                <a href="{{ route('trainers.index') }}" class="hover:text-blue-600 transition">Field Trainers</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Edit Trainer</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Field Trainer: {{ $trainer->name }}</h1>
            <p class="text-xs text-slate-500">
                Update account profile, credentials, and geographic jurisdiction.
            </p>
        </div>

        <a href="{{ route('trainers.index') }}"
            class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center space-x-1.5">
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Back to Directory</span>
        </a>
    </div>

    <!-- Main Card & Form -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="p-6 sm:p-8 bg-gradient-to-b from-blue-50/40 via-white to-white border-b border-slate-100 flex items-start space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-blue-500/20 shrink-0">
                {{ strtoupper(substr($trainer->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-base font-bold text-slate-900">{{ $trainer->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5 font-mono">
                    {{ $trainer->email }} &bull; Role: field-trainer
                </p>
            </div>
        </div>

        <form action="{{ route('trainers.update', $trainer) }}" method="POST" class="p-6 sm:p-8 space-y-6 text-xs">
            @csrf
            @method('PUT')

            <!-- Section 1: Personal & Account Identity -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">1</span>
                    <span>Trainer Profile & Login Credentials</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Trainer Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $trainer->name) }}" required
                            class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition @error('name') border-rose-500 @enderror">
                        @error('name')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address (Login ID) -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Email Address (Login Username) <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $trainer->email) }}" required
                            class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition @error('email') border-rose-500 @enderror">
                        @error('email')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Contact Mobile Number <span class="text-rose-500">*</span>
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone', $trainer->phone) }}" required
                            maxlength="10" pattern="[6-9][0-9]{9}" title="10-digit mobile number starting with 6, 7, 8, or 9"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition @error('phone') border-rose-500 @enderror">
                        @error('phone')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reset Password (Optional) -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Change Password <span class="text-slate-400 font-normal">(Leave blank to keep unchanged)</span>
                        </label>
                        <input type="password" name="password" placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                    </div>

                    <!-- Designation -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Designation <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="designation" value="{{ old('designation', $trainer->designation) }}" required
                            class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition @error('designation') border-rose-500 @enderror">
                        @error('designation')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Department / Organization <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="department" value="{{ old('department', $trainer->department) }}" required
                            class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition @error('department') border-rose-500 @enderror">
                        @error('department')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Geographic Jurisdiction (District -> Block -> GP) -->
            <div class="space-y-4 pt-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 flex items-center space-x-2">
                    <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">2</span>
                    <span>Geographic Jurisdiction (District &rarr; Block &rarr; Gram Panchayat)</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- District Selection -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Assigned District <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="district_id" x-model="districtId"
                                @change="loadBlocks(districtId)"
                                required
                                class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-3.5 pr-9 text-xs text-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition @error('district_id') border-rose-500 @enderror">
                                <option value="" disabled :selected="!districtId">-- Select District --</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('district_id')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Block Selection -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Assigned Block <span class="text-rose-500">*</span>
                            <span x-show="loadingBlocks" class="text-blue-600 text-[10px] font-normal animate-pulse ml-1">(Loading blocks...)</span>
                        </label>
                        <div class="relative">
                            <select name="block_id" x-model="blockId"
                                @change="loadGps(blockId)"
                                required
                                :disabled="loadingBlocks || !districtId"
                                class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-3.5 pr-9 text-xs text-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition disabled:bg-slate-50 disabled:text-slate-400 @error('block_id') border-rose-500 @enderror">
                                <option value="" disabled :selected="!blockId">-- Select Block --</option>
                                <template x-for="b in blocks" :key="b.id">
                                    <option :value="b.id" :selected="String(b.id) === String(blockId)" x-text="b.name"></option>
                                </template>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('block_id')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gram Panchayat Selection -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Gram Panchayat <span class="text-rose-500">*</span>
                            <span x-show="loadingGps" class="text-blue-600 text-[10px] font-normal animate-pulse ml-1">(Loading GPs...)</span>
                        </label>
                        <div class="relative">
                            <select name="gram_panchayat_id" x-model="gpId"
                                required
                                :disabled="loadingGps || !blockId"
                                class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-3.5 pr-9 text-xs text-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition disabled:bg-slate-50 disabled:text-slate-400 @error('gram_panchayat_id') border-rose-500 @enderror">
                                <option value="" disabled :selected="!gpId">-- Select Gram Panchayat --</option>
                                <template x-for="gp in gps" :key="gp.id">
                                    <option :value="gp.id" :selected="String(gp.id) === String(gpId)" x-text="gp.name"></option>
                                </template>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                        @error('gram_panchayat_id')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Active Status Checkbox -->
            <div class="pt-2">
                <input type="hidden" name="is_active" value="0">
                <label class="inline-flex items-center space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $trainer->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                    <span class="font-medium text-slate-700">Account is Active and authorized to conduct training sessions</span>
                </label>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100">
                <a href="{{ route('trainers.index') }}"
                    class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Update Field Trainer Account</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
