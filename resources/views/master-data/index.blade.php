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
        <div id="modules" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                <div class="flex items-center space-x-2">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                        Curriculum Modules (OSDP 2.0 Syllabus)
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                        {{ $modules->count() }}
                    </span>
                </div>
                @can('create', App\Models\TrainingModule::class)
                <a href="{{ route('modules.create') }}"
                    class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm transition">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Module
                </a>
                @endcan
            </div>

            <div class="space-y-3 max-h-[28rem] overflow-y-auto pr-1 text-xs">
                @forelse($modules as $mod)
                <div class="p-3.5 rounded-xl border {{ $mod->is_active ? 'border-slate-200 bg-white hover:border-blue-300' : 'border-slate-200/60 bg-slate-50/70 opacity-80' }} transition space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center flex-wrap gap-1.5">
                                <span class="font-bold text-slate-900 text-xs">
                                    Module {{ $mod->module_number }}: {{ $mod->title }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 font-mono border border-blue-200">
                                    {{ $mod->code }}
                                </span>
                                @if($mod->is_active)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Active
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center space-x-1.5 flex-shrink-0">
                            @can('update', $mod)
                            <a href="{{ route('modules.edit', $mod) }}"
                                class="p-1 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition"
                                title="Edit Module {{ $mod->module_number }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            @endcan

                            @can('delete', $mod)
                            @if(($mod->batches_count ?? 0) === 0)
                            <form action="{{ route('modules.destroy', $mod) }}" method="POST" class="inline"
                                onsubmit="return confirm('Are you sure you want to delete Module {{ $mod->module_number }} ({{ $mod->code }})?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                    title="Delete Module">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="p-1 text-slate-300 cursor-not-allowed" title="Cannot delete: linked to {{ $mod->batches_count }} batch(es)">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            @endif
                            @endcan
                        </div>
                    </div>

                    @if($mod->description)
                    <p class="text-[11px] text-slate-600 leading-relaxed">{{ $mod->description }}</p>
                    @endif

                    <div class="flex items-center justify-between pt-1 text-[10px] text-slate-400">
                        <span class="font-medium text-slate-500">
                            Linked Batches: <strong class="text-blue-600 font-bold">{{ $mod->batches_count ?? 0 }}</strong>
                        </span>
                        @if(!empty($mod->topics) && is_array($mod->topics))
                            <span>{{ count($mod->topics) }} Topics Covered</span>
                        @endif
                    </div>

                    @if(!empty($mod->topics) && is_array($mod->topics))
                    <div class="flex flex-wrap gap-1 pt-1">
                        @foreach(array_slice($mod->topics, 0, 4) as $top)
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px]">
                                • {{ $top }}
                            </span>
                        @endforeach
                        @if(count($mod->topics) > 4)
                            <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px]">
                                +{{ count($mod->topics) - 4 }} more
                            </span>
                        @endif
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-slate-400">
                    <p class="font-medium">No curriculum modules configured yet.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Certified Training Venues Table -->
    <div id="venues" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Certified Training Venues (OSDP Delivery Infrastructure)
                </h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                    {{ $venues->count() }}
                </span>
            </div>
            @can('create', App\Models\Venue::class)
            <a href="{{ route('venues.create') }}"
                class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm transition">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Venue
            </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3">Venue & Address</th>
                        <th class="px-6 py-3">District / Block</th>
                        <th class="px-6 py-3">Capacity</th>
                        <th class="px-6 py-3">Classroom Facilities</th>
                        <th class="px-6 py-3">In-Charge Contact</th>
                        <th class="px-6 py-3">Batches Hosted</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($venues as $v)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-3.5">
                            <div class="font-bold text-slate-900">{{ $v->name }}</div>
                            @if($v->address)
                                <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1" title="{{ $v->address }}">{{ $v->address }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="font-medium text-slate-900">{{ $v->district->name ?? 'N/A' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $v->block->name ?? 'District HQ' }}</div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                {{ $v->capacity }} seats
                            </span>
                        </td>
                        <td class="px-6 py-3.5">
                            @php
                                $fac = $v->facilities ?? [];
                            @endphp
                            <div class="flex flex-wrap gap-1">
                                @if(!empty($fac['projector']))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-blue-50 text-blue-700 border border-blue-200" title="Projector Available">📽️ Projector</span>
                                @endif
                                @if(!empty($fac['ac']))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-sky-50 text-sky-700 border border-sky-200" title="Air Conditioning">❄️ AC</span>
                                @endif
                                @if(!empty($fac['internet']))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200" title="Internet / WiFi">🌐 WiFi</span>
                                @endif
                                @if(!empty($fac['audio_system']))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] bg-purple-50 text-purple-700 border border-purple-200" title="Audio / PA System">🎙️ Audio</span>
                                @endif
                                @if(empty($fac['projector']) && empty($fac['ac']) && empty($fac['internet']) && empty($fac['audio_system']))
                                    <span class="text-slate-400 text-[11px]">Basic</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            @if($v->contact_person || $v->contact_phone)
                                <div class="font-medium text-slate-800">{{ $v->contact_person ?? 'Caretaker' }}</div>
                                @if($v->contact_phone)
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $v->contact_phone }}</div>
                                @endif
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="font-bold text-blue-600">{{ $v->batches_count }} Batches</span>
                        </td>
                        <td class="px-6 py-3.5">
                            @if($v->is_active)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Active</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                @can('update', $v)
                                <a href="{{ route('venues.edit', $v) }}"
                                    class="p-1 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition"
                                    title="Edit Venue {{ $v->name }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @endcan

                                @can('delete', $v)
                                @if(($v->batches_count ?? 0) === 0)
                                <form action="{{ route('venues.destroy', $v) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete venue \'{{ addslashes($v->name) }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition"
                                        title="Delete Venue">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @else
                                <span class="p-1 text-slate-300 cursor-not-allowed" title="Cannot delete: linked to {{ $v->batches_count }} batch(es)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                @endif
                                @endcan

                                @cannot('update', $v)
                                @cannot('delete', $v)
                                <span class="text-slate-300 text-xs">—</span>
                                @endcannot
                                @endcannot
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                            No training venues registered yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
