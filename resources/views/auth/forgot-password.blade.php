@extends('layouts.auth', ['title' => 'Forgot Password'])

@section('content')
<div class="mb-5 text-xs text-slate-400">
    Enter your registered official email address. A secure password reset link will be dispatched to your account.
</div>

<form class="space-y-5" action="{{ route('password.email') }}" method="POST">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
            Registered Email Address
        </label>
        <div class="mt-1">
            <input id="email" name="email" type="email" required value="{{ old('email') }}"
                placeholder="name@osdp.gov.in"
                class="block w-full rounded-xl bg-slate-900/80 border border-slate-700 px-4 py-3 text-sm text-white placeholder-slate-500 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
        </div>
    </div>

    <div>
        <button type="submit"
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-blue-500 shadow-lg shadow-blue-600/30 transition">
            Send Reset Instructions
        </button>
    </div>

    <div class="text-center pt-2">
        <a href="{{ route('login') }}" class="text-xs font-medium text-slate-400 hover:text-white transition">
            ← Back to Sign In
        </a>
    </div>
</form>
@endsection
