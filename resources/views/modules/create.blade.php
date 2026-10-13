@extends('layouts.app', ['title' => 'Create Curriculum Module', 'headerTitle' => 'New Curriculum Module'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Create Curriculum Module</h1>
            <p class="text-xs text-slate-500 mt-0.5">Define a new training module, syllabus topics, and active status for OSDP 2.0</p>
        </div>
        <a href="{{ route('master-data.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Master Data
        </a>
    </div>

    <form action="{{ route('modules.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5 text-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="module_number" class="block font-semibold text-slate-700 mb-1">
                    Module Number <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="module_number" name="module_number" min="1" max="65535" required
                    value="{{ old('module_number', $nextModuleNumber) }}" placeholder="e.g. {{ $nextModuleNumber }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                <span class="text-[10px] text-slate-400 mt-1 block">Numeric sequence in curriculum (must be unique).</span>
            </div>

            <div>
                <label for="code" class="block font-semibold text-slate-700 mb-1">
                    Module Code <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="code" name="code" required
                    value="{{ old('code', $suggestedCode) }}" placeholder="e.g. MOD-{{ $nextModuleNumber }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs uppercase font-mono text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                <span class="text-[10px] text-slate-400 mt-1 block">Short identifier used in batch tracking (e.g. MOD-9).</span>
            </div>
        </div>

        <div>
            <label for="title" class="block font-semibold text-slate-700 mb-1">
                Module Title / Curriculum Subject <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="title" name="title" required
                value="{{ old('title') }}" placeholder="e.g. Data Governance, Grievance Redressal & Public Transparency"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
        </div>

        <div>
            <label for="description" class="block font-semibold text-slate-700 mb-1">
                Curriculum Description & Learning Objectives
            </label>
            <textarea id="description" name="description" rows="3"
                placeholder="Explain the module's core learning goals, regulatory framework, and target audience benefits..."
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="topics" class="block font-semibold text-slate-700 mb-1">
                Syllabus Topics (One topic per line)
            </label>
            <textarea id="topics" name="topics" rows="4"
                placeholder="Topic 1: Overview of the framework&#10;Topic 2: Statutory roles and obligations&#10;Topic 3: Hands-on case exercises&#10;Topic 4: Frontline compliance checklist"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-mono text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('topics') }}</textarea>
            <span class="text-[10px] text-slate-400 mt-1 block">Each line will be saved as an individual syllabus item in the module syllabus.</span>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <label class="flex items-start space-x-3 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                    class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="font-semibold text-slate-800 block text-xs">Active Module</span>
                    <span class="text-[11px] text-slate-500 block">Active modules are available for selection when scheduling new training batches.</span>
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
                Create Module
            </button>
        </div>
    </form>

</div>
@endsection
