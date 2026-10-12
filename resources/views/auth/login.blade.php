@extends('layouts.auth', ['title' => 'Sign In'])

@section('content')
<form class="space-y-5" action="{{ route('login.submit') }}" method="POST">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
            Official Email Address
        </label>
        <div class="mt-1 relative">
            <input id="email" name="email" type="email" autocomplete="email" required
                value="{{ old('email') }}"
                placeholder="name@osdp.gov.in"
                class="block w-full rounded-xl bg-slate-900/80 border border-slate-700 px-4 py-3 text-sm text-white placeholder-slate-500 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                Password
            </label>
            <a href="{{ route('password.request') }}" class="text-xs font-medium text-orange-400 hover:text-orange-300 hover:underline">
                Forgot password?
            </a>
        </div>
        <div class="mt-1 relative">
            <input id="password" name="password" type="password" autocomplete="current-password" required
                placeholder="••••••••••••"
                class="block w-full rounded-xl bg-slate-900/80 border border-slate-700 px-4 py-3 text-sm text-white placeholder-slate-500 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center space-x-2 text-xs text-slate-400 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-blue-500/20">
            <span>Remember this device</span>
        </label>
    </div>

    <div>
        <button type="submit"
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-blue-500 shadow-lg shadow-blue-600/30 transition">
            Sign In to MIS Portal
        </button>
    </div>
</form>

<!-- Quick Test Login Presets -->
<div class="mt-6 pt-6 border-t border-slate-700/60">
    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2 text-center">
        Quick Role Demo Logins (Password: <code class="text-orange-400">Admin@12345</code>)
    </div>
    <div class="grid grid-cols-2 gap-2 text-xs">
        <button type="button" onclick="fillCredentials('admin@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-600/40 text-left transition truncate">
            👑 Super Admin
        </button>
        <button type="button" onclick="fillCredentials('ocac@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-600/40 text-left transition truncate">
            🏛️ OCAC Nodal
        </button>
        <button type="button" onclick="fillCredentials('pmu@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-600/40 text-left transition truncate">
            📊 PMU Manager
        </button>
        <button type="button" onclick="fillCredentials('dc.khordha@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-600/40 text-left transition truncate">
            📍 DC (Khordha)
        </button>
        <button type="button" onclick="fillCredentials('trainer.mohanty@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-600/40 text-left transition truncate">
            🎓 Field Trainer
        </button>
        <button type="button" onclick="fillCredentials('monitor.das@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-600/40 text-left transition truncate">
            🔍 Field Monitor
        </button>
    </div>
</div>

<script>
    function fillCredentials(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'Admin@12345';
    }
</script>
@endsection
