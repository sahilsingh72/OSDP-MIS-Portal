@extends('layouts.app', ['title' => 'My Profile', 'headerTitle' => 'Account Settings'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">User Profile & Security</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage your personal credentials, contact details, and password</p>
    </div>

    <!-- Personal Info Card -->
    <form action="{{ route('profile.update') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
        @csrf
        @method('PUT')

        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
            Personal Information
        </h2>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Full Name</label>
            <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Official Email Address</label>
            <input type="email" value="{{ $user->email }}" disabled
                class="w-full rounded-xl border border-slate-200 bg-slate-100 p-2.5 text-xs text-slate-500 cursor-not-allowed">
            <span class="text-[10px] text-slate-400 mt-0.5 block">Email address changes must be requested through OCAC administrators.</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Designation</label>
                <input type="text" name="designation" value="{{ old('designation', $user->designation) }}"
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Department</label>
            <input type="text" name="department" value="{{ old('department', $user->department) }}"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
        </div>

        <div class="pt-3 border-t border-slate-200 flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition shadow-md shadow-blue-500/20">
                Update Profile
            </button>
        </div>
    </form>

    <!-- Change Password Card -->
    <form action="{{ route('profile.password') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4 text-xs">
        @csrf
        @method('PUT')

        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider pb-2 border-b border-slate-200">
            Change Password
        </h2>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Current Password <span class="text-rose-500">*</span></label>
            <input type="password" name="current_password" required placeholder="••••••••••••"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">New Password <span class="text-rose-500">*</span></label>
            <input type="password" name="password" required placeholder="Minimum 8 characters"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1">Confirm New Password <span class="text-rose-500">*</span></label>
            <input type="password" name="password_confirmation" required placeholder="••••••••••••"
                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
        </div>

        <div class="pt-3 border-t border-slate-200 flex justify-end">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition shadow-md shadow-slate-900/20">
                Update Password
            </button>
        </div>
    </form>

</div>
@endsection
