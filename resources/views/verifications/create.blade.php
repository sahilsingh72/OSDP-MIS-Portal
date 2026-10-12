@extends('layouts.app', ['title' => 'Log Field Assessment', 'headerTitle' => 'New Field Verification'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Log Field Verification Assessment</h1>
            <p class="text-xs text-slate-500 mt-0.5">Submit independent on-site audit findings for training quality assurance</p>
        </div>
        <a href="{{ route('verifications.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            ← Cancel
        </a>
    </div>

    <form action="{{ route('verifications.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5 text-xs">
        @csrf

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Select Training Batch Visited <span class="text-rose-500">*</span></label>
            <select name="training_batch_id" required class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
                <option value="">-- Choose Batch --</option>
                @foreach($batches as $b)
                    <option value="{{ $b->id }}" {{ (isset($batch) && $batch->id === $b->id) ? 'selected' : '' }}>
                        {{ $b->batch_code }} - {{ $b->tier?->name }} ({{ $b->district?->name ?? 'State' }}, {{ $b->scheduled_date->format('d/m/Y') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Visit Date <span class="text-rose-500">*</span></label>
                <input type="date" name="visit_date" required value="{{ date('Y-m-d') }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Verified Headcount <span class="text-rose-500">*</span></label>
                <input type="number" name="verified_headcount" required min="0" placeholder="Actual attendees present"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="space-y-2 pt-2">
            <span class="block font-semibold text-slate-700">On-Site Logistics & Branding Checks</span>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center space-x-2 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="session_in_progress" value="1" checked class="rounded text-blue-600">
                    <span class="text-slate-800">Session in progress during visit</span>
                </label>
                <label class="flex items-center space-x-2 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="banner_displayed" value="1" checked class="rounded text-blue-600">
                    <span class="text-slate-800">8x5 OSDP banner displayed</span>
                </label>
                <label class="flex items-center space-x-2 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="kits_distributed" value="1" checked class="rounded text-blue-600">
                    <span class="text-slate-800">Handbooks & kits distributed</span>
                </label>
                <label class="flex items-center space-x-2 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="tea_lunch_provided" value="1" checked class="rounded text-blue-600">
                    <span class="text-slate-800">Tea/Lunch refreshments provided</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Training Quality Rating <span class="text-rose-500">*</span></label>
            <select name="training_quality_rating" required class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
                <option value="excellent">Excellent - Trainer mastery, active interaction, full compliance</option>
                <option value="good" selected>Good - Well delivered, clear examples, Minor issues</option>
                <option value="average">Average - Adequate but needs improvement in interaction</option>
                <option value="poor">Poor - Severe deviation or lack of preparation</option>
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Detailed Observations <span class="text-rose-500">*</span></label>
            <textarea name="observations" required rows="3" placeholder="Describe participant engagement, venue condition, trainer delivery..."
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none"></textarea>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Corrective Actions Needed (if any)</label>
            <textarea name="corrective_actions_needed" rows="2" placeholder="Recommendations for follow-up by District Coordinator or PMU..."
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none"></textarea>
        </div>

        <div class="pt-3 border-t border-slate-200 flex items-center justify-end space-x-3">
            <a href="{{ route('verifications.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition shadow-md shadow-blue-500/20">
                Submit Verification Report
            </button>
        </div>
    </form>

</div>
@endsection
