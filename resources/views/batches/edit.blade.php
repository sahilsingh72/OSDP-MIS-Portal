@extends('layouts.app', ['title' => "Edit {$batch->batch_code}", 'headerTitle' => 'Edit Batch'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Batch {{ $batch->batch_code }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Update session timings, planned capacity, and lifecycle status</p>
        </div>
        <a href="{{ route('batches.show', $batch) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            ← Cancel & Return
        </a>
    </div>

    <form action="{{ route('batches.update', $batch) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Scheduled Date <span class="text-rose-500">*</span></label>
                <input type="date" name="scheduled_date" required value="{{ old('scheduled_date', $batch->scheduled_date->format('Y-m-d')) }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Lifecycle Status <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
                    <option value="planned" {{ $batch->status === 'planned' ? 'selected' : '' }}>Planned</option>
                    <option value="scheduled" {{ $batch->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="in_progress" {{ $batch->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="awaiting_evidence" {{ $batch->status === 'awaiting_evidence' ? 'selected' : '' }}>Awaiting Evidence</option>
                    <option value="submitted" {{ $batch->status === 'submitted' ? 'selected' : '' }}>Submitted for Review</option>
                    <option value="under_review" {{ $batch->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="correction_required" {{ $batch->status === 'correction_required' ? 'selected' : '' }}>Correction Required</option>
                    <option value="verified" {{ $batch->status === 'verified' ? 'selected' : '' }}>Verified & Accepted</option>
                    <option value="rejected" {{ $batch->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="closed" {{ $batch->status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Scheduled Start Time</label>
                <input type="time" name="start_time" required value="{{ old('start_time', substr($batch->start_time, 0, 5)) }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Scheduled End Time</label>
                <input type="time" name="end_time" required value="{{ old('end_time', substr($batch->end_time, 0, 5)) }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Actual Start Time</label>
                <input type="time" name="actual_start_time" value="{{ old('actual_start_time', $batch->actual_start_time ? substr($batch->actual_start_time, 0, 5) : '') }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Actual End Time</label>
                <input type="time" name="actual_end_time" value="{{ old('actual_end_time', $batch->actual_end_time ? substr($batch->actual_end_time, 0, 5) : '') }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Planned Participants</label>
                <input type="number" name="planned_participants" required min="5" value="{{ old('planned_participants', $batch->planned_participants) }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Remarks</label>
            <textarea name="remarks" rows="3" class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">{{ old('remarks', $batch->remarks) }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
            <a href="{{ route('batches.show', $batch) }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 transition">
                Save Changes
            </button>
        </div>
    </form>

</div>
@endsection
