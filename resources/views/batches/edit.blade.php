@extends('layouts.app', ['title' => "Edit {$batch->batch_code}", 'headerTitle' => 'Edit Batch'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Batch {{ $batch->batch_code }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider
                    @if($batch->status === 'verified') bg-emerald-100 text-emerald-800
                    @elseif($batch->status === 'submitted' || $batch->status === 'under_review') bg-blue-100 text-blue-800
                    @elseif($batch->status === 'correction_required') bg-amber-100 text-amber-800
                    @elseif($batch->status === 'rejected') bg-rose-100 text-rose-800
                    @else bg-slate-100 text-slate-700 @endif">
                    {{ str_replace('_', ' ', $batch->status) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Update training tier, geographic jurisdiction, trainers, syllabus modules, timings, and status</p>
        </div>
        <a href="{{ route('batches.show', $batch) }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Cancel & Return
        </a>
    </div>

    <form action="{{ route('batches.update', $batch) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-8 text-xs">
        @csrf
        @method('PUT')

        <!-- Section 1: Administrative Hierarchy & Tier -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                1. Administrative Hierarchy & Tier
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Training Tier <span class="text-rose-500">*</span>
                    </label>
                    <select id="training_tier_id" name="training_tier_id" required
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select Training Tier --</option>
                        @foreach($tiers as $tier)
                            <option value="{{ $tier->id }}" data-size="{{ $tier->target_batch_size }}" data-code="{{ $tier->code }}"
                                {{ (string)old('training_tier_id', $batch->training_tier_id) === (string)$tier->id ? 'selected' : '' }}>
                                {{ $tier->name }} (Target Size: {{ $tier->target_batch_size }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        District <span class="text-slate-400 font-normal">(Optional for Directorate Tier)</span>
                    </label>
                    <select id="district_id" name="district_id"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select District --</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}" {{ (string)old('district_id', $batch->district_id) === (string)$d->id ? 'selected' : '' }}>
                                {{ $d->name }} ({{ $d->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Block <span class="text-slate-400 font-normal">(Applicable for Block & GP tiers)</span>
                    </label>
                    <select id="block_id" name="block_id"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select Block --</option>
                        @foreach($blocks as $b)
                            <option value="{{ $b->id }}" {{ (string)old('block_id', $batch->block_id) === (string)$b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Training Venue <span class="text-slate-400 font-normal">(Approved Locations)</span>
                    </label>
                    <select name="venue_id"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select Venue (or specify in remarks) --</option>
                        @foreach($venues as $v)
                            <option value="{{ $v->id }}" {{ (string)old('venue_id', $batch->venue_id) === (string)$v->id ? 'selected' : '' }}>
                                {{ $v->name }} (Cap: {{ $v->capacity }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Cluster Gram Panchayats selection (hidden unless GP tier selected) -->
            @php
                $isGpTier = $batch->tier?->code === 'gram_panchayat' || old('training_tier_id') == optional($tiers->firstWhere('code', 'gram_panchayat'))->id;
            @endphp
            <div id="cluster_gp_container" class="{{ $isGpTier ? '' : 'hidden' }} pt-2">
                <label class="block font-semibold text-slate-700 mb-1">
                    Cluster Gram Panchayats <span class="text-slate-400 font-normal">(Select all covered GPs for cluster mode)</span>
                </label>
                <div id="gp_checkboxes" class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs max-h-36 overflow-y-auto">
                    @forelse($gps as $gp)
                        <label class="flex items-center space-x-2 text-slate-700 cursor-pointer">
                            <input type="checkbox" name="cluster_gps[]" value="{{ $gp->id }}"
                                {{ in_array($gp->id, old('cluster_gps', $selectedGpIds)) ? 'checked' : '' }}
                                class="rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span>{{ $gp->name }}</span>
                        </label>
                    @empty
                        <span class="text-slate-400 col-span-3">Select a Block above to load Gram Panchayats</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Section 2: Schedule & Timings -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                2. Session Date, Timings & Lifecycle Status
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Scheduled Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="scheduled_date" required
                        value="{{ old('scheduled_date', $batch->scheduled_date->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Lifecycle Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="planned" {{ old('status', $batch->status) === 'planned' ? 'selected' : '' }}>Planned</option>
                        <option value="scheduled" {{ old('status', $batch->status) === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ old('status', $batch->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="awaiting_evidence" {{ old('status', $batch->status) === 'awaiting_evidence' ? 'selected' : '' }}>Awaiting Evidence</option>
                        <option value="submitted" {{ old('status', $batch->status) === 'submitted' ? 'selected' : '' }}>Submitted for Review</option>
                        <option value="under_review" {{ old('status', $batch->status) === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="correction_required" {{ old('status', $batch->status) === 'correction_required' ? 'selected' : '' }}>Correction Required</option>
                        <option value="verified" {{ old('status', $batch->status) === 'verified' ? 'selected' : '' }}>Verified & Accepted</option>
                        <option value="rejected" {{ old('status', $batch->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="closed" {{ old('status', $batch->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Planned Participants <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="planned_participants" name="planned_participants" required min="5" max="500"
                        value="{{ old('planned_participants', $batch->planned_participants) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Scheduled Start <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="start_time" required
                        value="{{ old('start_time', substr($batch->start_time, 0, 5)) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Scheduled End <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="end_time" required
                        value="{{ old('end_time', substr($batch->end_time, 0, 5)) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Actual Start Time <span class="text-slate-400 font-normal">(Form 3 Part C)</span>
                    </label>
                    <input type="time" name="actual_start_time"
                        value="{{ old('actual_start_time', $batch->actual_start_time ? substr($batch->actual_start_time, 0, 5) : '') }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Actual End Time <span class="text-slate-400 font-normal">(Form 3 Part C)</span>
                    </label>
                    <input type="time" name="actual_end_time"
                        value="{{ old('actual_end_time', $batch->actual_end_time ? substr($batch->actual_end_time, 0, 5) : '') }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Section 3: Trainer Deployment -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                3. Trainer Deployment
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Primary Master Trainer <span class="text-rose-500">*</span>
                    </label>
                    <select name="primary_trainer_id" required
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Assign Primary Trainer --</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}" {{ (string)old('primary_trainer_id', $primaryTrainerId) === (string)$trainer->id ? 'selected' : '' }}>
                                {{ $trainer->name }} ({{ $trainer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Co-Trainer <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <select name="co_trainer_id"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="">-- None / Single Trainer --</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}" {{ (string)old('co_trainer_id', $coTrainerId) === (string)$trainer->id ? 'selected' : '' }}>
                                {{ $trainer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 4: Curricular Modules Covered -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                4. Curriculum Modules Covered <span class="text-rose-500">*</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                @foreach($modules as $m)
                <label class="flex items-start space-x-2.5 p-3 rounded-xl border {{ in_array($m->id, old('modules', $selectedModuleIds)) ? 'border-blue-300 bg-blue-50/40' : 'border-slate-200' }} hover:bg-slate-50 cursor-pointer transition">
                    <input type="checkbox" name="modules[]" value="{{ $m->id }}"
                        {{ in_array($m->id, old('modules', $selectedModuleIds)) ? 'checked' : '' }}
                        class="mt-0.5 w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                    <div>
                        <div class="flex items-center space-x-1.5">
                            <span class="font-bold text-slate-900">Module {{ $m->module_number }}:</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-100 text-blue-700 font-mono">{{ $m->code }}</span>
                        </div>
                        <span class="text-slate-600 text-[11px] block mt-0.5">{{ $m->title }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Section 5: Operational Remarks -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                5. Operational Remarks
            </h2>
            <textarea name="remarks" rows="3" placeholder="Specific logistics instructions, target offices, contact details..."
                class="w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">{{ old('remarks', $batch->remarks) }}</textarea>
        </div>

        <!-- Form Actions -->
        <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
            <a href="{{ route('batches.show', $batch) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition">
                Save Changes
            </button>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tierSelect = document.getElementById('training_tier_id');
    const districtSelect = document.getElementById('district_id');
    const blockSelect = document.getElementById('block_id');
    const gpContainer = document.getElementById('cluster_gp_container');
    const gpCheckboxes = document.getElementById('gp_checkboxes');
    const plannedInput = document.getElementById('planned_participants');

    // Auto update target size based on tier
    tierSelect.addEventListener('change', function() {
        const selectedOption = tierSelect.options[tierSelect.selectedIndex];
        const defaultSize = selectedOption.getAttribute('data-size');
        const tierCode = selectedOption.getAttribute('data-code');

        if (defaultSize && !plannedInput.value) {
            plannedInput.value = defaultSize;
        }

        if (tierCode === 'gram_panchayat') {
            gpContainer.classList.remove('hidden');
        } else {
            gpContainer.classList.add('hidden');
        }
    });

    // Cascading Blocks dropdown on District selection
    districtSelect.addEventListener('change', function() {
        const districtId = this.value;
        blockSelect.innerHTML = '<option value="">Loading blocks...</option>';
        gpCheckboxes.innerHTML = '<span class="text-slate-400 col-span-3">Select a Block above</span>';

        if (!districtId) {
            blockSelect.innerHTML = '<option value="">-- Select District First --</option>';
            return;
        }

        fetch(`/api/districts/${districtId}/blocks`)
            .then(res => res.json())
            .then(data => {
                blockSelect.innerHTML = '<option value="">-- Select Block --</option>';
                data.forEach(block => {
                    const opt = document.createElement('option');
                    opt.value = block.id;
                    opt.textContent = block.name;
                    blockSelect.appendChild(opt);
                });
            })
            .catch(() => {
                blockSelect.innerHTML = '<option value="">Error loading blocks</option>';
            });
    });

    // Cascading GPs on Block selection
    blockSelect.addEventListener('change', function() {
        const blockId = this.value;
        if (!blockId) return;

        fetch(`/api/blocks/${blockId}/gram-panchayats`)
            .then(res => res.json())
            .then(data => {
                gpCheckboxes.innerHTML = '';
                if (data.length === 0) {
                    gpCheckboxes.innerHTML = '<span class="text-slate-400 col-span-3">No GPs found for this block</span>';
                    return;
                }
                data.forEach(gp => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center space-x-2 text-slate-700 cursor-pointer';
                    label.innerHTML = `<input type="checkbox" name="cluster_gps[]" value="${gp.id}" class="rounded text-blue-600 focus:ring-blue-500 border-slate-300"> <span>${gp.name}</span>`;
                    gpCheckboxes.appendChild(label);
                });
            });
    });
});
</script>
@endpush
