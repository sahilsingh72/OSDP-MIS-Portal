<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles', 'assignedDistricts']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show form for creating a new user.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = Role::all();
        $districts = District::where('is_active', true)->orderBy('name')->get();

        return view('users.create', compact('roles', 'districts'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'exists:roles,name'],
            'districts' => ['nullable', 'array'],
            'districts.*' => ['exists:districts,id'],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'department' => $validated['department'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        if (!empty($validated['districts'])) {
            $user->assignedDistricts()->sync($validated['districts']);
        }

        AuditLog::record('user_created', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('status', "User {$user->name} created successfully.");
    }

    /**
     * Show form for editing an existing user.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = Role::all();
        $districts = District::where('is_active', true)->orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'districts'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'exists:roles,name'],
            'districts' => ['nullable', 'array'],
            'districts.*' => ['exists:districts,id'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', Password::defaults()],
        ]);

        $oldData = $user->toArray();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'department' => $validated['department'] ?? null,
            'is_active' => $validated['is_active'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        if (isset($validated['districts'])) {
            $user->assignedDistricts()->sync($validated['districts']);
        }

        AuditLog::record('user_updated', $user, $oldData, $user->toArray());

        return redirect()->route('users.index')->with('status', "User {$user->name} updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot deactivate your own administrative account.']);
        }

        $newStatus = !$user->is_active;
        $user->update(['is_active' => $newStatus]);

        AuditLog::record($newStatus ? 'user_activated' : 'user_deactivated', $user);

        return back()->with('status', "User account for {$user->name} has been " . ($newStatus ? 'activated.' : 'deactivated.'));
    }
}
