@extends('layouts.app', ['title' => 'Create User', 'headerTitle' => 'New User Account'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Create User Account</h1>
            <p class="text-xs text-slate-500 mt-0.5">Register staff member, assign RBAC role, and configure district boundary</p>
        </div>
        <a href="{{ route('users.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
            ← Cancel
        </a>
    </div>

    <form action="{{ route('users.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-5 text-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="Officer Name"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Official Email Address <span class="text-rose-500">*</span></label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="officer@osdp.gov.in"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Contact Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Mobile number"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Designation</label>
                <input type="text" name="designation" value="{{ old('designation') }}" placeholder="e.g. Master Trainer"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Department</label>
                <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. OCAC / IT Dept"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">System Role <span class="text-rose-500">*</span></label>
                <select name="role" required class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
                    <option value="">-- Assign Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Assigned District(s) <span class="text-slate-400 font-normal">(For District Coordinators & Monitors)</span></label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl max-h-36 overflow-y-auto">
                @foreach($districts as $d)
                <label class="flex items-center space-x-2 text-slate-700">
                    <input type="checkbox" name="districts[]" value="{{ $d->id }}" class="rounded text-blue-600">
                    <span>{{ $d->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Initial Password <span class="text-rose-500">*</span></label>
            <input type="password" name="password" required placeholder="Minimum 8 characters"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
        </div>

        <div class="pt-3 border-t border-slate-200 flex items-center justify-end space-x-3">
            <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition shadow-md shadow-blue-500/20">
                Create Account
            </button>
        </div>
    </form>

</div>
@endsection
