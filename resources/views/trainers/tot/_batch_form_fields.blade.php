@if($errors->any() && old('_form_mode') === $formMode)
<div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 space-y-1">
    @foreach($errors->all() as $err)
        <div>&bull; {{ $err }}</div>
    @endforeach
</div>
@endif

<input type="hidden" name="venue_mode" :value="{{ $modelPrefix }}.venue_mode">

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block font-semibold text-slate-700 mb-1">TOT Batch Code <span class="text-rose-500">*</span></label>
        <input type="text" name="batch_code" x-model="{{ $modelPrefix }}.batch_code" required
            placeholder="e.g. TOT-2026-01"
            class="w-full rounded-xl border border-slate-300 bg-slate-50/60 p-2.5 text-xs focus:bg-white focus:border-blue-500 focus:outline-none">
    </div>

    <div>
        <label class="block font-semibold text-slate-700 mb-1">Training Date (1-Day) <span class="text-rose-500">*</span></label>
        <input type="date" name="training_date" x-model="{{ $modelPrefix }}.training_date" required
            class="w-full rounded-xl border border-slate-300 bg-slate-50/60 p-2.5 text-xs focus:bg-white focus:border-blue-500 focus:outline-none">
    </div>
</div>

<div>
    <label class="block font-semibold text-slate-700 mb-1">Batch Title / Topic</label>
    <input type="text" name="title" x-model="{{ $modelPrefix }}.title"
        placeholder="e.g. OSDP 2.0 Master Trainer Orientation - Batch 1"
        class="w-full rounded-xl border border-slate-300 bg-slate-50/60 p-2.5 text-xs focus:bg-white focus:border-blue-500 focus:outline-none">
</div>

<!-- Venue of TOT: Select Existing OR Create New Inline -->
<div class="space-y-3 rounded-xl border border-slate-200/90 bg-slate-50/60 p-3.5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <label class="block font-semibold text-slate-700">
            Venue of TOT <span class="text-rose-500">*</span>
        </label>

        <div class="inline-flex rounded-lg bg-slate-200/80 p-0.5 text-[11px] font-semibold">
            @if($totVenues->isNotEmpty())
                <button type="button"
                    @click="{{ $modelPrefix }}.venue_mode = 'existing'"
                    :class="{{ $modelPrefix }}.venue_mode === 'existing' ? 'bg-white text-blue-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                    class="px-2.5 py-1 rounded-md transition cursor-pointer">
                    Existing TOT Venue
                </button>
            @endif
            <button type="button"
                @click="{{ $modelPrefix }}.venue_mode = 'new'; {{ $modelPrefix }}.tot_venue_id = '';"
                :class="{{ $modelPrefix }}.venue_mode === 'new' ? 'bg-white text-emerald-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer">
                + Create New Venue
            </button>
        </div>
    </div>

    @if($totVenues->isNotEmpty())
    <div x-show="{{ $modelPrefix }}.venue_mode === 'existing'">
        <select name="tot_venue_id"
            x-model="{{ $modelPrefix }}.tot_venue_id"
            :disabled="{{ $modelPrefix }}.venue_mode !== 'existing'"
            :required="{{ $modelPrefix }}.venue_mode === 'existing'"
            @change="if ($event.target.value === '__new__') { {{ $modelPrefix }}.tot_venue_id = ''; {{ $modelPrefix }}.venue_mode = 'new'; }"
            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            <option value="">-- Select Existing TOT Venue --</option>
            @foreach($totVenues as $v)
                <option value="{{ $v->id }}">
                    {{ $v->name }}{{ $v->address ? ' — ' . $v->address : '' }}{{ $v->district ? ' (' . $v->district->name . ')' : '' }}
                </option>
            @endforeach
            <option value="__new__">+ Create New TOT Venue...</option>
        </select>
        <p class="text-[11px] text-slate-500 mt-1.5">
            Select an existing TOT venue, or click <strong>+ Create New Venue</strong> to add a new one.
        </p>
    </div>
    @endif

    <div x-show="{{ $modelPrefix }}.venue_mode === 'new'" class="space-y-3" style="{{ $totVenues->isNotEmpty() ? 'display: none;' : '' }}">
        <div>
            <label class="block font-semibold text-slate-700 mb-1">
                New TOT Venue Name <span class="text-rose-500">*</span>
            </label>
            <input type="text"
                name="venue_name"
                x-model="{{ $modelPrefix }}.venue_name"
                :disabled="{{ $modelPrefix }}.venue_mode !== 'new'"
                :required="{{ $modelPrefix }}.venue_mode === 'new'"
                placeholder="Enter exact new TOT venue name (e.g. OCAC Conference Hall, Bhubaneswar)"
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs focus:border-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Venue Address / Hall Details</label>
            <input type="text"
                name="venue_address"
                x-model="{{ $modelPrefix }}.venue_address"
                :disabled="{{ $modelPrefix }}.venue_mode !== 'new'"
                placeholder="Floor / Building / City"
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs focus:border-blue-500 focus:outline-none">
        </div>

        <p class="text-[11px] text-emerald-700 font-medium">
            This new venue will automatically be saved to your existing TOT venues list for future batches.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 {{ !empty($showStatus) ? 'sm:grid-cols-2' : '' }} gap-4">
    <div>
        <label class="block font-semibold text-slate-700 mb-1">District (Optional)</label>
        <select name="district_id" x-model="{{ $modelPrefix }}.district_id" class="w-full rounded-xl border border-slate-300 bg-slate-50/60 p-2.5 text-xs focus:bg-white focus:border-blue-500 focus:outline-none">
            <option value="">-- State Level (All Districts) --</option>
            @foreach($districts as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
            @endforeach
        </select>
    </div>

    @if(!empty($showStatus))
    <div>
        <label class="block font-semibold text-slate-700 mb-1">Status</label>
        <select name="status" x-model="{{ $modelPrefix }}.status" class="w-full rounded-xl border border-slate-300 bg-slate-50/60 p-2.5 text-xs focus:bg-white focus:border-blue-500 focus:outline-none">
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
        </select>
    </div>
    @endif
</div>

<div>
    <label class="block font-semibold text-slate-700 mb-1">Remarks</label>
    <textarea name="remarks" x-model="{{ $modelPrefix }}.remarks" rows="2" placeholder="Optional notes about this 1-day TOT batch..."
        class="w-full rounded-xl border border-slate-300 bg-slate-50/60 p-2.5 text-xs focus:bg-white focus:border-blue-500 focus:outline-none"></textarea>
</div>
