@extends('layouts.app', ['title' => "Evidence Upload Center - {$batch->batch_code}", 'headerTitle' => 'Evidence Management'])

@section('content')
<div class="space-y-6">

    <!-- Top Header & SLA Tracker -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="text-xl font-extrabold text-slate-900 tracking-tight font-mono">{{ $batch->batch_code }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $evidenceStatus['is_complete'] ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                    {{ $evidenceStatus['completed_count'] }}/6 Items Attached
                </span>
            </div>
            <p class="text-xs text-slate-500">
                Official Evidence Pack. Files stored privately and securely outside public webroot.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if($evidenceStatus['is_complete'] && in_array($batch->status, ['scheduled', 'in_progress', 'awaiting_evidence', 'correction_required']))
                <form action="{{ route('batches.submit', $batch) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Confirm submission of batch evidence pack for verification?')"
                        class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-md shadow-orange-500/20 transition">
                        ✓ Ready - Submit for Verification
                    </button>
                </form>
            @endif
            <a href="{{ route('batches.show', $batch) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                ← Return to Batch
            </a>
        </div>
    </div>

    <!-- Upload Form Card -->
    @can('uploadEvidence', $batch)
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-2">
            Upload Evidence Item
        </h2>
        <p class="text-xs text-slate-500 mb-4">
            Allowed file sizes: Photos (≤ {{ $maxPhotoMb }} MB), Video (≤ {{ $maxVideoMb }} MB), PDFs (≤ {{ $maxPdfMb }} MB). Video must be landscape orientation (~1 min).
        </p>

        <form action="{{ route('batches.evidence.store', $batch) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Evidence Item Type <span class="text-rose-500">*</span></label>
                <select name="evidence_type" required class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
                    <option value="">-- Choose Item Type --</option>
                    <option value="attendance_sheet">1. Attendance Sheet (Form 2 - Both Sides)</option>
                    <option value="photo_1">2. Photograph 1 (Full Group with 8x5 Banner)</option>
                    <option value="photo_2">3. Photograph 2 (Trainer Teaching)</option>
                    <option value="video">4. Short Video Clip (MP4, ~1 min, landscape)</option>
                    <option value="feedback_forms">5. Feedback Forms (Form 1 - Merged PDF)</option>
                    <option value="batch_completion_report">6. Batch Completion Report (Form 3 PDF)</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Select File <span class="text-rose-500">*</span></label>
                <input type="file" name="file" required
                    class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition shadow-md shadow-blue-500/20 text-center">
                    Upload & Hash Verify File
                </button>
            </div>
        </form>
    </div>
    @endcan

    <!-- Attached Files Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                Current Uploaded Evidence Items
            </h3>
            <span class="text-xs text-slate-400 font-medium">{{ $batch->uploads->count() }} active files</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Evidence Item</th>
                        <th class="px-6 py-3.5">Original File Name</th>
                        <th class="px-6 py-3.5">Size</th>
                        <th class="px-6 py-3.5">Version</th>
                        <th class="px-6 py-3.5">Uploaded By</th>
                        <th class="px-6 py-3.5">Timestamp</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($batch->uploads as $upload)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">
                            {{ $upload->type_label }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-mono text-[11px] truncate max-w-xs">
                            {{ $upload->original_filename }}
                        </td>
                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                            {{ $upload->formatted_size }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">v{{ $upload->version }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $upload->uploader?->name ?? 'User' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                            {{ $upload->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                            <a href="{{ route('evidence.download', $upload) }}"
                                class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold transition">
                                ⬇ Download
                            </a>
                            @can('delete', $upload)
                            <form action="{{ route('evidence.destroy', $upload) }}" method="POST" class="inline" onsubmit="return confirm('Remove this evidence item?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold transition">
                                    Delete
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            No evidence items uploaded yet. Please attach all 6 items to complete the pack.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Structured Data Entry Forms: Form 3 Summary & Form 1 Feedback -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Form 3 Part C Summary Entry -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                Form 3 Training Summary Entry (Part C)
            </h3>

            <form action="{{ route('batches.summary.store', $batch) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Registered</label>
                        <input type="number" name="registered_count" required value="{{ old('registered_count', $batch->summary?->registered_count ?? $batch->registered_participants) }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Present</label>
                        <input type="number" name="present_count" required value="{{ old('present_count', $batch->summary?->present_count ?? $batch->present_participants) }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Feedback Forms</label>
                        <input type="number" name="feedback_forms_received" required value="{{ old('feedback_forms_received', $batch->summary?->feedback_forms_received ?? $batch->present_participants) }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Actual Start Time</label>
                        <input type="time" name="actual_start_time" value="{{ old('actual_start_time', $batch->summary?->actual_start_time ? substr($batch->summary->actual_start_time, 0, 5) : substr($batch->start_time, 0, 5)) }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Actual End Time</label>
                        <input type="time" name="actual_end_time" value="{{ old('actual_end_time', $batch->summary?->actual_end_time ? substr($batch->summary->actual_end_time, 0, 5) : substr($batch->end_time, 0, 5)) }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">What went well (2-3 lines)</label>
                    <textarea name="what_went_well" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('what_went_well', $batch->summary?->what_went_well) }}</textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Problems faced (venue, power, projector, etc.)</label>
                    <textarea name="problems_faced" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('problems_faced', $batch->summary?->problems_faced) }}</textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Follow-up actions needed</label>
                    <textarea name="follow_up_actions" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('follow_up_actions', $batch->summary?->follow_up_actions) }}</textarea>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition text-center">
                    Save Form 3 Summary
                </button>
            </form>
        </div>

        <!-- Form 1 Feedback Metrics Entry -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                Form 1 Feedback Aggregates Entry
            </h3>

            <form action="{{ route('batches.feedback.store', $batch) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Q1 Clarity (1-5)</label>
                        <input type="number" step="0.01" min="1" max="5" name="q1_clarity_avg" required value="{{ old('q1_clarity_avg', $batch->feedback?->q1_clarity_avg ?? '4.50') }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Q2 Knowledge (1-5)</label>
                        <input type="number" step="0.01" min="1" max="5" name="q2_knowledge_avg" required value="{{ old('q2_knowledge_avg', $batch->feedback?->q2_knowledge_avg ?? '4.80') }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Q3 Relevance (1-5)</label>
                        <input type="number" step="0.01" min="1" max="5" name="q3_relevance_avg" required value="{{ old('q3_relevance_avg', $batch->feedback?->q3_relevance_avg ?? '4.60') }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Q4 Handbook (1-5)</label>
                        <input type="number" step="0.01" min="1" max="5" name="q4_handbook_avg" required value="{{ old('q4_handbook_avg', $batch->feedback?->q4_handbook_avg ?? '4.70') }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Q5 Overall (1-5)</label>
                        <input type="number" step="0.01" min="1" max="5" name="q5_overall_avg" required value="{{ old('q5_overall_avg', $batch->feedback?->q5_overall_avg ?? '4.75') }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Forms Count</label>
                        <input type="number" min="1" name="total_forms_analyzed" required value="{{ old('total_forms_analyzed', $batch->feedback?->total_forms_analyzed ?? $batch->present_participants) }}"
                            class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Key Qualitative Highlights</label>
                    <textarea name="qualitative_highlights" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('qualitative_highlights', $batch->feedback?->qualitative_highlights) }}</textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Improvement Suggestions</label>
                    <textarea name="improvement_suggestions" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('improvement_suggestions', $batch->feedback?->improvement_suggestions) }}</textarea>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition text-center">
                    Save Feedback Evaluation
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
