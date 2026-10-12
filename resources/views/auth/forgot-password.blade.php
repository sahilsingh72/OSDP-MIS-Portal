@extends('layouts.auth', ['title' => 'Forgot Password'])

@section('content')
<div class="mb-5 text-xs text-slate-600">
    Enter your registered official email address. A secure password reset link will be dispatched to your account.
</div>

<form class="space-y-4" action="{{ route('password.email') }}" method="POST">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
            Registered Email Address
        </label>
        <div class="mt-1">
            <input id="email" name="email" type="email" required value="{{ old('email') }}"
                placeholder="name@osdp.gov.in"
                class="block w-full rounded-xl bg-slate-50 border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none transition">
        </div>
    </div>

    <div>
        <button type="submit"
            class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition">
            Send Reset Instructions
        </button>
    </div>

    <div class="text-center pt-2">
        <a href="{{ route('login') }}" class="text-xs font-medium text-slate-600 hover:text-blue-600 transition">
            ← Back to Sign In
        </a>
    </div>
</form>
@endsection
