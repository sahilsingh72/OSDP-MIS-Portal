<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-300 ease-in-out">

    <!-- Brand Header -->
    <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800/80">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20 font-bold text-base">
                O
            </div>
            <div>
                <span class="text-base font-bold tracking-tight text-white block leading-tight">OSDP MIS</span>
                <span class="text-[10px] font-semibold text-orange-400 uppercase tracking-wider block">Policy 2.0 Portal</span>
            </div>
        </a>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto px-4 py-5 space-y-1 text-sm">
        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 mb-2">Main</div>

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 pt-4 mb-2">Training Operations</div>

        <!-- Training Batches -->
        <a href="{{ route('batches.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('batches.*') && !request()->routeIs('batches.attendance.*') && !request()->routeIs('batches.evidence.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Training Batches
        </a>

        <!-- Participants Directory -->
        @can('participants.view')
        <a href="{{ route('participants.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('participants.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Participants Roster
        </a>
        @endcan

        <!-- Review Queue -->
        @if(auth()->user()->can('reviews.manage') || auth()->user()->can('evidence.review'))
        <a href="{{ route('reviews.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('reviews.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Review Queue
        </a>
        @endif

        <!-- Independent Field Verifications -->
        @can('field-verifications.view')
        <a href="{{ route('verifications.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('verifications.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Field Verifications
        </a>
        @endcan

        <!-- Reports & Exports -->
        @can('reports.view')
        <a href="{{ route('reports.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('reports.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Reports & Analytics
        </a>
        @endcan

        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 pt-4 mb-2">Administration</div>

        <!-- Master Data -->
        @can('master-data.view')
        <a href="{{ route('master-data.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('master-data.*', 'venues.*', 'modules.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
            Master Data
        </a>
        @endcan

        <!-- User Management -->
        @can('users.view')
        <a href="{{ route('users.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('users.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            User Management
        </a>
        @endcan

        <!-- Roles & Permissions Governance -->
        @can('roles.manage')
        <a href="{{ route('roles.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('roles.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Roles & Permissions
        </a>
        @endcan

        <!-- Audit Logs -->
        @can('audit-logs.view')
        <a href="{{ route('audit-logs.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('audit-logs.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Audit Trail
        </a>
        @endcan

        <!-- Settings -->
        @role('super-admin')
        <a href="{{ route('settings.index') }}"
            class="flex items-center px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('settings.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            System Settings
        </a>
        @endrole
    </div>

    <!-- User Profile & Log Out Footer -->
    <div class="p-4 border-t border-slate-800/80 bg-slate-950/60">
        <div class="flex items-center space-x-3 mb-3">
            <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center font-bold text-white text-xs border border-slate-600">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->roles->first()?->name ?? 'User' }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('profile.edit') }}" class="flex-1 text-center py-1.5 px-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs text-slate-300 hover:text-white transition">
                Profile
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="py-1.5 px-3 rounded-lg bg-rose-900/30 hover:bg-rose-900/50 text-rose-300 hover:text-rose-100 text-xs border border-rose-800/50 transition">
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>
