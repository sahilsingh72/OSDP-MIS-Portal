<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/90 flex flex-col transition-transform duration-300 ease-in-out shadow-sm">

    <!-- Brand Header -->
    <div class="h-16 flex items-center px-6 bg-white border-b border-slate-200/90">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/25 font-bold text-base group-hover:scale-105 transition-transform">
                O
            </div>
            <div>
                <span class="text-base font-extrabold tracking-tight text-slate-900 block leading-tight">OSDP MIS</span>
                <span class="text-[10px] font-bold text-orange-600 uppercase tracking-wider block">Policy 2.0 Portal</span>
            </div>
        </a>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto px-3.5 py-3 space-y-0.5 text-[13px] [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-1.5">Main</div>

        <!-- Dashboard -->
        @php $isDashboard = request()->routeIs('dashboard') || request()->routeIs('home'); @endphp
        <a href="{{ route('dashboard') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isDashboard ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isDashboard ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Dashboard</span>
        </a>

        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 pt-3 mb-1.5">Training Operations</div>

        <!-- Training Batches -->
        @php $isBatches = request()->routeIs('batches.*') && !request()->routeIs('batches.attendance.*') && !request()->routeIs('batches.evidence.*'); @endphp
        <a href="{{ route('batches.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isBatches ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isBatches ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span>Training Batches</span>
        </a>

        <!-- Participants Directory -->
        @can('participants.view')
        @php $isParticipants = request()->routeIs('participants.*'); @endphp
        <a href="{{ route('participants.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isParticipants ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isParticipants ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>Participants Roster</span>
        </a>
        @endcan

        <!-- Review Queue -->
        @if(auth()->user()->can('reviews.manage') || auth()->user()->can('evidence.review'))
        @php $isReviews = request()->routeIs('reviews.*'); @endphp
        <a href="{{ route('reviews.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isReviews ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isReviews ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Review Queue</span>
        </a>
        @endif

        <!-- Independent Field Verifications -->
        @can('field-verifications.view')
        @php $isVerifications = request()->routeIs('verifications.*'); @endphp
        <a href="{{ route('verifications.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isVerifications ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isVerifications ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Field Verifications</span>
        </a>
        @endcan

        <!-- Reports & Exports -->
        @can('reports.view')
        @php $isReports = request()->routeIs('reports.*'); @endphp
        <a href="{{ route('reports.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isReports ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isReports ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Reports & Analytics</span>
        </a>
        @endcan

        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 pt-3 mb-1.5">Administration</div>

        <!-- Master Data -->
        @can('master-data.view')
        @php $isMasterData = request()->routeIs('master-data.*', 'venues.*', 'modules.*'); @endphp
        <a href="{{ route('master-data.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isMasterData ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isMasterData ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
            <span>Master Data</span>
        </a>
        @endcan

        <!-- User Management -->
        @can('users.view')
        @php $isUsers = request()->routeIs('users.*'); @endphp
        <a href="{{ route('users.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isUsers ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isUsers ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>User Management</span>
        </a>
        @endcan

        <!-- Roles & Permissions Governance -->
        @can('roles.manage')
        @php $isRoles = request()->routeIs('roles.*'); @endphp
        <a href="{{ route('roles.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isRoles ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isRoles ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Roles & Permissions</span>
        </a>
        @endcan

        <!-- Audit Logs -->
        @can('audit-logs.view')
        @php $isAudit = request()->routeIs('audit-logs.*'); @endphp
        <a href="{{ route('audit-logs.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isAudit ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isAudit ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Audit Trail</span>
        </a>
        @endcan

        <!-- Settings -->
        @role('super-admin')
        @php $isSettings = request()->routeIs('settings.*'); @endphp
        <a href="{{ route('settings.index') }}"
            class="group flex items-center px-3 py-2 rounded-xl font-medium transition {{ $isSettings ? 'bg-blue-50 text-blue-700 font-semibold border border-blue-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
            <svg class="w-4 h-4 mr-3 flex-shrink-0 {{ $isSettings ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>System Settings</span>
        </a>
        @endrole
    </div>

    <!-- User Profile & Log Out Footer -->
    <div class="p-4 border-t border-slate-200/90 bg-slate-50/80">
        <div class="flex items-center space-x-3 mb-3">
            <div class="w-9 h-9 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs shadow-sm ring-2 ring-white">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-slate-500 truncate capitalize">{{ str_replace('-', ' ', auth()->user()->roles->first()?->name ?? 'User') }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('profile.edit') }}" class="flex-1 text-center py-1.5 px-2 rounded-xl bg-white hover:bg-slate-100 text-xs font-semibold text-slate-700 hover:text-slate-900 border border-slate-200/80 shadow-2xs transition">
                Profile
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="py-1.5 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 hover:text-rose-900 text-xs font-semibold border border-rose-200/80 transition shadow-2xs">
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>
