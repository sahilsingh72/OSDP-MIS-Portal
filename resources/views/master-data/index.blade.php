@extends('layouts.app', ['title' => 'Master Data Management', 'headerTitle' => 'Master Data Directory'])

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Administrative & Master Reference Data</h1>
        <p class="text-xs text-slate-500 mt-0.5">30 Districts of Odisha, Training Tiers, Curriculum Modules 1 to 8, and Certified Venues</p>
    </div>

    <!-- Tiers & Modules Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Training Tiers Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                Training Tiers Hierarchy
            </h2>
            <div class="space-y-3">
                @foreach($tiers as $tier)
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-slate-900 text-sm">{{ $tier->name }}</div>
                        <div class="text-slate-500 text-[11px] mt-0.5">{{ $tier->description }}</div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-3">
                        <span class="block font-extrabold text-blue-600">{{ $tier->batches_count }} Batches</span>
                        <span class="text-[10px] text-slate-400">Target: ~{{ $tier->target_batch_size }} / batch</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Curriculum Modules Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
                Curriculum Modules (OSDP 2.0 Syllabus)
            </h2>
            <div class="space-y-2 max-h-96 overflow-y-auto pr-1 text-xs">
                @foreach($modules as $mod)
                <div class="p-3 rounded-xl border border-slate-200 hover:border-blue-300 transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900">Module {{ $mod->module_number }}: {{ $mod->title }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">{{ $mod->code }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ $mod->description }}</p>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- 30 Districts of Odisha Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                Districts of Odisha (30 Administrative Units)
            </h2>
            <span class="text-xs text-slate-400 font-semibold">{{ $districts->count() }} Districts</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3">Code</th>
                        <th class="px-6 py-3">District Name</th>
                        <th class="px-6 py-3">Blocks Registered</th>
                        <th class="px-6 py-3">Venues Configured</th>
                        <th class="px-6 py-3">Batches Scheduled</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @foreach($districts as $d)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-3.5 font-bold font-mono text-slate-500">{{ $d->code }}</td>
                        <td class="px-6 py-3.5 font-bold text-slate-900">{{ $d->name }}</td>
                        <td class="px-6 py-3.5">{{ $d->blocks_count }} Blocks</td>
                        <td class="px-6 py-3.5">{{ $d->venues_count }} Venues</td>
                        <td class="px-6 py-3.5 font-semibold text-blue-600">{{ $d->batches_count }} Batches</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
