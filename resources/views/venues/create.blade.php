@extends('layouts.app', ['title' => 'Register Training Venue', 'headerTitle' => 'New Training Venue'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Register Training Venue</h1>
            <p class="text-xs text-slate-500 mt-0.5">Add an approved venue with seating capacity and technical infrastructure for OSDP sessions</p>
        </div>
        <a href="{{ route('master-data.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Master Data
        </a>
    </div>

    <form action="{{ route('venues.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6 text-xs">
        @csrf

        <div class="space-y-4">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                1. Venue Identification & Location
            </h2>

            <div>
                <label for="name" class="block font-semibold text-slate-700 mb-1">
                    Venue Facility Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" required
                    value="{{ old('name') }}" placeholder="e.g. OCAC Conference Hall / DRDA Meeting Hall"
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
                            <option value="{{ $d->id }}" {{ (string)old('district_id') === (string)$d->id ? 'selected' : '' }}>
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
                        <option value="">-- Select District First --</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="address" class="block font-semibold text-slate-700 mb-1">
                    Full Physical Address / Landmark
                </label>
                <textarea id="address" name="address" rows="2"
                    placeholder="Campus building, road name, PIN code..."
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('address') }}</textarea>
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
                        value="{{ old('capacity', 50) }}" placeholder="50"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label for="contact_person" class="block font-semibold text-slate-700 mb-1">
                        Facility In-Charge Name
                    </label>
                    <input type="text" id="contact_person" name="contact_person"
                        value="{{ old('contact_person') }}" placeholder="Contact Officer / Caretaker"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>

                <div>
                    <label for="contact_phone" class="block font-semibold text-slate-700 mb-1">
                        Contact Mobile Number
                    </label>
                    <input type="text" id="contact_phone" name="contact_phone"
                        value="{{ old('contact_phone') }}" placeholder="10-digit mobile"
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-2">
                    Available Classroom Facilities
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer select-none">
                        <input type="checkbox" name="facilities[projector]" value="1" {{ old('facilities.projector', '1') ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="font-medium text-slate-700">Projector & Screen</span>
                    </label>

                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer select-none">
                        <input type="checkbox" name="facilities[ac]" value="1" {{ old('facilities.ac', '1') ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="font-medium text-slate-700">Air Conditioning</span>
                    </label>

                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer select-none">
                        <input type="checkbox" name="facilities[internet]" value="1" {{ old('facilities.internet', '1') ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="font-medium text-slate-700">Internet / WiFi</span>
                    </label>

                    <label class="flex items-center space-x-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer select-none">
                        <input type="checkbox" name="facilities[audio_system]" value="1" {{ old('facilities.audio_system', '1') ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="font-medium text-slate-700">Audio / PA System</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <label class="flex items-start space-x-3 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                    class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="font-semibold text-slate-800 block text-xs">Active Venue</span>
                    <span class="text-[11px] text-slate-500 block">Active venues are available for selection when scheduling new training batches.</span>
                </div>
            </label>
        </div>

        <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
            <a href="{{ route('master-data.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 font-semibold text-slate-700 text-xs transition">
                Cancel
            </a>
            <button type="submit"
                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-600/20 transition">
                Register Venue
            </button>
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
