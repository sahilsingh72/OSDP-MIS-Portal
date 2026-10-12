@extends('layouts.app', ['title' => 'Schedule Training Batch', 'headerTitle' => 'New Batch Schedule'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Schedule Training Batch</h1>
            <p class="text-xs text-slate-500 mt-0.5">Plan and assign resources for an official OSDP 2.0 training session</p>
        </div>
        <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            ← Back to Batches
        </a>
    </div>

    <form action="{{ route('batches.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-8">
        @csrf

        <!-- Section 1: Hierarchy & Tier -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                1. Administrative Hierarchy & Tier
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Training Tier <span class="text-rose-500">*</span>
                    </label>
                    <select id="training_tier_id" name="training_tier_id" required
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select Training Tier --</option>
                        @foreach($tiers as $tier)
                            <option value="{{ $tier->id }}" data-size="{{ $tier->target_batch_size }}" data-code="{{ $tier->code }}"
                                {{ old('training_tier_id') == $tier->id ? 'selected' : '' }}>
                                {{ $tier->name }} (Target Size: {{ $tier->target_batch_size }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        District <span class="text-slate-400 font-normal">(Optional for Directorate Tier)</span>
                    </label>
                    <select id="district_id" name="district_id"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select District --</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}" {{ old('district_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }} ({{ $d->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Block <span class="text-slate-400 font-normal">(Applicable for Block & GP tiers)</span>
                    </label>
                    <select id="block_id" name="block_id"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select District First --</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Training Venue <span class="text-slate-400 font-normal">(Approved Locations)</span>
                    </label>
                    <select name="venue_id"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select Venue (or specify in remarks) --</option>
                        @foreach($venues as $v)
                            <option value="{{ $v->id }}" {{ old('venue_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }} (Cap: {{ $v->capacity }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Cluster Gram Panchayats selection (hidden unless GP tier selected) -->
            <div id="cluster_gp_container" class="hidden pt-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Cluster Gram Panchayats <span class="text-slate-400 font-normal">(Select all covered GPs for cluster mode)</span>
                </label>
                <div id="gp_checkboxes" class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs max-h-36 overflow-y-auto">
                    <span class="text-slate-400 col-span-3">Select a Block above to load Gram Panchayats</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Schedule & Timings -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                2. Session Date & Timings
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Scheduled Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="scheduled_date" required min="{{ date('Y-m-d') }}"
                        value="{{ old('scheduled_date', date('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Start Time <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="start_time" required
                        value="{{ old('start_time', '10:00') }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        End Time <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" name="end_time" required
                        value="{{ old('end_time', '17:30') }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Planned Participants <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="planned_participants" name="planned_participants" required min="5" max="100"
                        value="{{ old('planned_participants', 30) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
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
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Primary Master Trainer <span class="text-rose-500">*</span>
                    </label>
                    <select name="primary_trainer_id" required
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                        <option value="">-- Assign Primary Trainer --</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}" {{ old('primary_trainer_id') == $trainer->id ? 'selected' : '' }}>
                                {{ $trainer->name }} ({{ $trainer->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Co-Trainer <span class="text-slate-400 font-normal">(Optional)</span>
                    </label>
                    <select name="co_trainer_id"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs focus:border-blue-500 focus:outline-none">
                        <option value="">-- None / Single Trainer --</option>
                        @foreach($trainers as $trainer)
                            <option value="{{ $trainer->id }}" {{ old('co_trainer_id') == $trainer->id ? 'selected' : '' }}>
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
                <label class="flex items-start space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                    <input type="checkbox" name="modules[]" value="{{ $m->id }}"
                        {{ in_array($m->id, old('modules', $modules->pluck('id')->toArray())) ? 'checked' : '' }}
                        class="mt-0.5 w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                    <div>
                        <span class="font-bold text-slate-900">Module {{ $m->module_number }}:</span>
                        <span class="text-slate-700">{{ $m->title }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Section 5: Remarks -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                5. Operational Remarks
            </h2>
            <textarea name="remarks" rows="2" placeholder="Specific logistics instructions, target offices, contact details..."
                class="w-full rounded-xl border border-slate-300 p-3 text-xs focus:border-blue-500 focus:outline-none">{{ old('remarks') }}</textarea>
        </div>

        <!-- Form Submit Actions -->
        <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
            <a href="{{ route('batches.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition">
                Schedule Batch & Generate Code
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

        if (defaultSize) {
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
                    label.className = 'flex items-center space-x-2 text-slate-700';
                    label.innerHTML = `<input type="checkbox" name="cluster_gps[]" value="${gp.id}" class="rounded text-blue-600"> <span>${gp.name}</span>`;
                    gpCheckboxes.appendChild(label);
                });
            });
    });
});
</script>
@endpush
