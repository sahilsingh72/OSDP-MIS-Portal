@extends('layouts.auth', ['title' => 'Sign In'])

@section('content')
<form class="space-y-4" action="{{ route('login.submit') }}" method="POST">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
            Official Email Address
        </label>
        <div class="mt-1 relative">
            <input id="email" name="email" type="email" autocomplete="email" required
                value="{{ old('email') }}"
                placeholder="name@osdp.gov.in"
                class="block w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none transition">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                Password
            </label>
            <a href="{{ route('password.request') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 hover:underline">
                Forgot password?
            </a>
        </div>
        <div class="mt-1 relative">
            <input id="password" name="password" type="password" autocomplete="current-password" required
                placeholder="••••••••••••"
                class="block w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none transition">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer font-medium select-none">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500/20">
            <span>Remember this device</span>
        </label>
    </div>

    <div>
        <button type="submit"
            class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition">
            Sign In to MIS Portal
        </button>
    </div>
</form>

<!-- Quick Test Login Presets -->
<div class="mt-5 pt-4 border-t border-slate-200">
    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2.5 text-center">
        Quick Role Demo Logins (Password: <code class="text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 font-mono">Admin@12345</code>)
    </div>
    <div class="grid grid-cols-2 gap-2 text-xs">
        <button type="button" onclick="fillCredentials('admin@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-900 border border-slate-200 hover:border-blue-300 text-left transition font-medium truncate shadow-sm">
            👑 Super Admin
        </button>
        <button type="button" onclick="fillCredentials('ocac@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-900 border border-slate-200 hover:border-blue-300 text-left transition font-medium truncate shadow-sm">
            🏛️ OCAC Nodal
        </button>
        <button type="button" onclick="fillCredentials('pmu@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-900 border border-slate-200 hover:border-blue-300 text-left transition font-medium truncate shadow-sm">
            📊 PMU Manager
        </button>
        <button type="button" onclick="fillCredentials('dc.khordha@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-900 border border-slate-200 hover:border-blue-300 text-left transition font-medium truncate shadow-sm">
            📍 DC (Khordha)
        </button>
        <button type="button" onclick="fillCredentials('trainer.mohanty@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-900 border border-slate-200 hover:border-blue-300 text-left transition font-medium truncate shadow-sm">
            🎓 Field Trainer
        </button>
        <button type="button" onclick="fillCredentials('monitor.das@osdp.gov.in')"
            class="px-2.5 py-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-900 border border-slate-200 hover:border-blue-300 text-left transition font-medium truncate shadow-sm">
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
