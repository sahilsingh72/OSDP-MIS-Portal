@extends('layouts.auth', ['title' => 'Reset Password'])

@section('content')
<div class="mb-5 text-xs text-slate-600">
    Please enter your new password below. Passwords must be at least 8 characters long.
</div>

<form class="space-y-4" action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
            Email Address
        </label>
        <div class="mt-1">
            <input id="email" name="email" type="email" required value="{{ $email ?? old('email') }}" readonly
                class="block w-full rounded-xl bg-slate-100 border border-slate-300 px-4 py-2.5 text-sm text-slate-600 shadow-sm focus:outline-none cursor-not-allowed">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
            New Password
        </label>
        <div class="mt-1">
            <input id="password" name="password" type="password" required
                placeholder="••••••••••••"
                class="block w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none transition">
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
            Confirm New Password
        </label>
        <div class="mt-1">
            <input id="password_confirmation" name="password_confirmation" type="password" required
                placeholder="••••••••••••"
                class="block w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none transition">
        </div>
    </div>

    <div>
        <button type="submit"
            class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition">
            Reset Password
        </button>
    </div>
</form>
@endsection
