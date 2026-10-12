@extends('layouts.app', ['title' => 'System Settings', 'headerTitle' => 'System Configuration'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">System Settings & Operational Parameters</h1>
        <p class="text-xs text-slate-500 mt-0.5">Configure 48-hour submission deadline SLAs, evidence file size thresholds, and extensions</p>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6 text-xs">
        @csrf

        @foreach($settings as $group => $groupSettings)
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                {{ ucfirst($group) }} Configuration
            </h2>

            <div class="space-y-4">
                @foreach($groupSettings as $setting)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 items-center">
                    <div>
                        <label class="block font-bold text-slate-800">{{ str_replace('_', ' ', ucfirst($setting->key)) }}</label>
                        <span class="text-[11px] text-slate-400 block">{{ $setting->description }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <input type="text" name="{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}"
                            class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="pt-4 border-t border-slate-200 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition shadow-md shadow-blue-500/20">
                Save System Settings
            </button>
        </div>
    </form>

</div>
@endsection
