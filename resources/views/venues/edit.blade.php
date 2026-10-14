@extends('layouts.app', ['title' => "Edit {$venue->name}", 'headerTitle' => 'Edit Training Venue'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Venue: {{ $venue->name }}</h1>
                @if($venue->is_active)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Update location, seating capacity, contact person, or infrastructure facilities</p>
        </div>
        <a href="{{ route('master-data.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Master Data
        </a>
    </div>

    @if($batchesCount > 0)
    <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 text-xs text-blue-800 flex items-start space-x-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <span class="font-bold">Active Batch Dependency:</span> This venue has hosted or is scheduled for <span class="font-bold underline">{{ $batchesCount }} training batch(es)</span>. 
            Updates will reflect across records. Deletion is locked while batch associations exist.
        </div>
    </div>
    @endif

    <form action="{{ route('venues.update', $venue) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6 text-xs">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                1. Venue Identification & Location
            </h2>

            <div>
                <label for="name" class="block font-semibold text-slate-700 mb-1">
                    Venue Facility Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" required
                    value="{{ old('name', $venue->name) }}" placeholder="e.g. OCAC Conference Hall"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="district_id" class="block font-semibold text-slate-700 mb-1">
                        District <span class="text-rose-500">*</span>
                    </label>
                    <select id="district_id" name="district_id" required
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select District --</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}" {{ (string)old('district_id', $venue->district_id) === (string)$d->id ? 'selected' : '' }}>
                                {{ $d->name }} ({{ $d->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="block_id" class="block font-semibold text-slate-700 mb-1">
                        Block <span class="text-slate-400 font-normal">(Optional for District HQ)</span>
                    </label>
                    <select id="block_id" name="block_id"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-none">
                        <option value="">-- Select Block (Optional) --</option>
                        @foreach($blocks as $b)
                            <option value="{{ $b->id }}" {{ (string)old('block_id', $venue->block_id) === (string)$b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="address" class="block font-semibold text-slate-700 mb-1">
                    Full Physical Address / Landmark
                </label>
                <textarea id="address" name="address" rows="2"
                    placeholder="Campus building, road name, PIN code..."
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('address', $venue->address) }}</textarea>
            </div>
        </div>

        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                2. Capacity & Available Infrastructure
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="capacity" class="block font-semibold text-slate-700 mb-1">
                        Seating Capacity <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" id="capacity" name="capacity" min="5" max="1000" required
                        value="{{ old('capacity', $venue->capacity) }}" placeholder="50"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label for="contact_person" class="block font-semibold text-slate-700 mb-1">
                        Facility In-Charge Name
                    </label>
                    <input type="text" id="contact_person" name="contact_person"
                        value="{{ old('contact_person', $venue->contact_person) }}" placeholder="Contact Officer / Caretaker"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label for="contact_phone" class="block font-semibold text-slate-700 mb-1">
                        Contact Mobile Number
                    </label>
                    <input type="text" id="contact_phone" name="contact_phone"
                        value="{{ old('contact_phone', $venue->contact_phone) }}" placeholder="10-digit mobile"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            @php
                $fac = $venue->facilities ?? [];
                $hasProjector = !empty($fac['projector']);
                $hasAc = !empty($fac['ac']);
                $hasInternet = !empty($fac['internet']);
                $hasAudio = !empty($fac['audio_system']);
            @endphp

            <div>
                <label class="block font-semibold text-slate-700 mb-2">
                    Available Classroom Facilities
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer select-none">
                        <input type="checkbox" name="facilities[projector]" value="1" {{ old('facilities.projector', $hasProjector) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="font-medium text-slate-700">Projector & Screen</span>
                    </label>

                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer select-none">
                        <input type="checkbox" name="facilities[ac]" value="1" {{ old('facilities.ac', $hasAc) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="font-medium text-slate-700">Air Conditioning</span>
                    </label>

                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer select-none">
                        <input type="checkbox" name="facilities[internet]" value="1" {{ old('facilities.internet', $hasInternet) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="font-medium text-slate-700">Internet / WiFi</span>
                    </label>

                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer select-none">
                        <input type="checkbox" name="facilities[audio_system]" value="1" {{ old('facilities.audio_system', $hasAudio) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="font-medium text-slate-700">Audio / PA System</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <label class="flex items-start space-x-3 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $venue->is_active) ? 'checked' : '' }}
                    class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="font-semibold text-slate-800 block text-xs">Active Venue</span>
                    <span class="text-[11px] text-slate-500 block">Active venues are available for selection when scheduling new training batches.</span>
                </div>
            </label>
        </div>

        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
            <div>
                @can('delete', $venue)
                @if($batchesCount === 0)
                <form action="{{ route('venues.destroy', $venue) }}" method="POST" class="inline"
                    onsubmit="return confirm('Are you sure you want to permanently delete venue \'{{ addslashes($venue->name) }}\'? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="text-rose-600 hover:text-rose-800 font-semibold text-xs inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Venue
                    </button>
                </form>
                @else
                <span class="text-[11px] text-slate-400" title="Cannot delete: linked to {{ $batchesCount }} batch(es)">
                    🔒 Deletion locked (linked to {{ $batchesCount }} batches)
                </span>
                @endif
                @endcan
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('master-data.index') }}"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 font-semibold text-slate-700 text-xs transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition">
                    Save Changes
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const districtSelect = document.getElementById('district_id');
    const blockSelect = document.getElementById('block_id');

    districtSelect.addEventListener('change', function() {
        const districtId = this.value;
        blockSelect.innerHTML = '<option value="">Loading blocks...</option>';

        if (!districtId) {
            blockSelect.innerHTML = '<option value="">-- Select District First --</option>';
            return;
        }

        fetch(`/api/districts/${districtId}/blocks`)
            .then(res => res.json())
            .then(data => {
                blockSelect.innerHTML = '<option value="">-- Select Block (Optional) --</option>';
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
});
</script>
@endpush
