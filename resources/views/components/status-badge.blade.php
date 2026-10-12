@props(['status'])

@php
    $config = match ($status) {
        'planned' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-300', 'label' => 'Planned'],
        'scheduled' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-700', 'border' => 'border-sky-300', 'label' => 'Scheduled'],
        'in_progress' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-300', 'label' => 'In Progress'],
        'awaiting_evidence' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-300', 'label' => 'Awaiting Evidence'],
        'submitted' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-300', 'label' => 'Submitted for Review'],
        'under_review' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-300', 'label' => 'Under Review'],
        'correction_required' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'border' => 'border-orange-300', 'label' => 'Correction Required'],
        'verified' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-300', 'label' => 'Verified & Accepted'],
        'rejected' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-300', 'label' => 'Rejected'],
        'closed' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'border' => 'border-gray-300', 'label' => 'Closed'],
        default => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'label' => ucfirst(str_replace('_', ' ', $status))],
    };
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ str_replace('text-', 'bg-', $config['text']) }}"></span>
    {{ $config['label'] }}
</span>
