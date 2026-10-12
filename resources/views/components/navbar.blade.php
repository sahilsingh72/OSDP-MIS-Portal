<header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-30 sticky top-0">
    <div class="flex items-center space-x-3">
        <!-- Mobile Sidebar Toggle -->
        <button type="button" class="lg:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none"
            @click="sidebarOpen = !sidebarOpen">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Current Breadcrumb / Section -->
        <div class="hidden sm:flex items-center text-xs space-x-2 text-slate-500 font-medium">
            <span class="text-slate-700 font-semibold uppercase tracking-wider">OSDP 2.0 MIS</span>
            <span>/</span>
            <span class="text-blue-600">{{ $headerTitle ?? 'Portal' }}</span>
        </div>
    </div>

    <div class="flex items-center space-x-4">
        <!-- Role Badge -->
        <div class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
            <span class="uppercase tracking-wider">{{ str_replace('-', ' ', auth()->user()->roles->first()?->name ?? 'User') }}</span>
        </div>

        <!-- Geographic Scope Badge for DC -->
        @if(auth()->user()->hasRole('district-coordinator'))
            <div class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-orange-50 text-orange-700 border border-orange-200">
                📍 {{ auth()->user()->assignedDistricts->pluck('name')->implode(', ') ?: 'District Scope' }}
            </div>
        @endif

        <!-- Quick Profile Link -->
        <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 text-slate-700 hover:text-blue-600 transition">
            <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <span class="hidden md:inline text-xs font-semibold">{{ auth()->user()->name }}</span>
        </a>
    </div>
</header>
