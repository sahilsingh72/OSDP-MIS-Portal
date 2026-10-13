@extends('layouts.app', ['title' => 'Edit Module ' . $module->module_number, 'headerTitle' => 'Edit Curriculum Module'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Edit Module {{ $module->module_number }}</h1>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 font-mono border border-blue-200">
                    {{ $module->code }}
                </span>
                @if($module->is_active)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Update curriculum title, syllabus topics, code, and active status</p>
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
            <span class="font-bold">Active Curriculum Dependency:</span> This module is currently linked to <span class="font-bold underline">{{ $batchesCount }} training batch(es)</span>. 
            Modifications will be reflected across active records. Deletion is restricted while batch associations exist.
        </div>
    </div>
    @endif

    <form action="{{ route('modules.update', $module) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="module_number" class="block font-semibold text-slate-700 mb-1">
                    Module Number <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="module_number" name="module_number" min="1" max="65535" required
                    value="{{ old('module_number', $module->module_number) }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                <span class="text-[10px] text-slate-400 mt-1 block">Numeric sequence in curriculum.</span>
            </div>

            <div>
                <label for="code" class="block font-semibold text-slate-700 mb-1">
                    Module Code <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="code" name="code" required
                    value="{{ old('code', $module->code) }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs uppercase font-mono text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                <span class="text-[10px] text-slate-400 mt-1 block">Short identifier (e.g. MOD-1).</span>
            </div>
        </div>

        <div>
            <label for="title" class="block font-semibold text-slate-700 mb-1">
                Module Title / Curriculum Subject <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="title" name="title" required
                value="{{ old('title', $module->title) }}"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
        </div>

        <div>
            <label for="description" class="block font-semibold text-slate-700 mb-1">
                Curriculum Description & Learning Objectives
            </label>
            <textarea id="description" name="description" rows="3"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('description', $module->description) }}</textarea>
        </div>

        <div>
            <label for="topics" class="block font-semibold text-slate-700 mb-1">
                Syllabus Topics (One topic per line)
            </label>
            <textarea id="topics" name="topics" rows="5"
                placeholder="Topic 1: Overview&#10;Topic 2: Details"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs font-mono text-slate-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('topics', is_array($module->topics) ? implode("\n", $module->topics) : '') }}</textarea>
            <span class="text-[10px] text-slate-400 mt-1 block">Each line will be saved as an individual syllabus item in the module syllabus.</span>
        </div>

        <div class="pt-2 border-t border-slate-100">
            <label class="flex items-start space-x-3 cursor-pointer select-none">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $module->is_active) ? 'checked' : '' }}
                    class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="font-semibold text-slate-800 block text-xs">Active Module</span>
                    <span class="text-[11px] text-slate-500 block">Active modules are available for selection when scheduling new training batches.</span>
                </div>
            </label>
        </div>

        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
            <div>
                @can('delete', $module)
                @if($batchesCount === 0)
                <button type="button" onclick="document.getElementById('delete-module-form').submit();"
                    class="text-rose-600 hover:text-rose-800 font-semibold text-xs inline-flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Module
                </button>
                @else
                <span class="text-[11px] text-slate-400" title="Cannot delete because {{ $batchesCount }} batch(es) are associated.">
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

    @can('delete', $module)
    @if($batchesCount === 0)
    <form id="delete-module-form" action="{{ route('modules.destroy', $module) }}" method="POST" class="hidden"
        onsubmit="return confirm('Are you sure you want to permanently delete Module {{ $module->module_number }} ({{ $module->code }})? This action cannot be undone.');">
        @csrf
        @method('DELETE')
    </form>
    @endif
    @endcan

</div>
@endsection
