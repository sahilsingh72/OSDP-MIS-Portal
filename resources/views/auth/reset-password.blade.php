@extends('layouts.auth', ['title' => 'Reset Password'])

@section('content')
<div class="mb-5 text-xs text-slate-400">
    Please enter your new password below. Passwords must be at least 8 characters long.
</div>

<form class="space-y-5" action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
            Email Address
        </label>
        <div class="mt-1">
            <input id="email" name="email" type="email" required value="{{ $email ?? old('email') }}" readonly
                class="block w-full rounded-xl bg-slate-900/50 border border-slate-700/60 px-4 py-3 text-sm text-slate-300 shadow-sm focus:outline-none">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
            New Password
        </label>
        <div class="mt-1">
            <input id="password" name="password" type="password" required
                placeholder="••••••••••••"
                class="block w-full rounded-xl bg-slate-900/80 border border-slate-700 px-4 py-3 text-sm text-white placeholder-slate-500 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
            Confirm New Password
        </label>
        <div class="mt-1">
            <input id="password_confirmation" name="password_confirmation" type="password" required
                placeholder="••••••••••••"
                class="block w-full rounded-xl bg-slate-900/80 border border-slate-700 px-4 py-3 text-sm text-white placeholder-slate-500 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
        </div>
    </div>

    <div>
        <button type="submit"
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-blue-500 shadow-lg shadow-blue-600/30 transition">
            Reset Password
        </button>
    </div>
</form>
@endsection
