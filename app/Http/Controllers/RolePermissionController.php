<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\PermissionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of all roles, their assigned permission coverage, and user counts.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::withCount(['users', 'permissions'])->get();
        $totalPermissions = Permission::count();
        $totalActiveUsers = User::where('is_active', true)->count();

        // Attach metadata to each role
        $roles->each(function (Role $role) {
            $info = PermissionRegistry::getRoleInfo($role->name);
            $role->display_name = $info['display_name'];
            $role->description = $info['description'];
            $role->badge_color = $info['badge_color'];
            $role->is_core = $info['is_core'];
        });

        // Sort: Core roles first in logical sequence, then custom roles
        $order = ['super-admin', 'ocac', 'pmu', 'district-coordinator', 'field-trainer', 'field-monitor'];
        $roles = $roles->sortBy(function ($role) use ($order) {
            $idx = array_search($role->name, $order, true);
            return $idx !== false ? $idx : 999;
        })->values();

        $stats = [
            'total_roles' => $roles->count(),
            'total_permissions' => $totalPermissions,
            'total_active_users' => $totalActiveUsers,
        ];

        return view('roles.index', compact('roles', 'stats'));
    }

    /**
     * Show form for creating a new role.
     */
    public function create(): View
    {
        $this->authorize('create', Role::class);

        $groupedPermissions = PermissionRegistry::getGroupedPermissions();
        $totalPermissions = Permission::count();

        return view('roles.create', compact('groupedPermissions', 'totalPermissions'));
    }

    /**
     * Store a newly created role with assigned initial permissions.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\-\_]+$/', 'unique:roles,name'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ], [
            'name.regex' => 'The role slug should contain only letters, numbers, hyphens, and underscores (e.g., district-analyst).',
            'name.unique' => 'A role with this slug already exists in the system.',
        ]);

        $roleSlug = Str::slug($validated['name']);

        $role = Role::create([
            'name' => $roleSlug,
            'guard_name' => 'web',
        ]);

        $submittedPermissions = $validated['permissions'] ?? [];
        if (!empty($submittedPermissions)) {
            $role->syncPermissions($submittedPermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLog::record('role_created', $role, null, [
            'name' => $role->name,
            'permissions' => $submittedPermissions,
        ]);

        return redirect()->route('roles.index')->with('status', "Role '{$role->name}' created successfully with " . count($submittedPermissions) . ' permissions.');
    }

    /**
     * Show the edit permission assignment screen for a specific role.
     */
    public function edit(string|int|Role $role): View
    {
        $role = $this->resolveRole($role);
        $this->authorize('update', $role);

        $role->load('permissions');
        $role->loadCount('users');

        $roleInfo = PermissionRegistry::getRoleInfo($role->name);
        $role->display_name = $roleInfo['display_name'];
        $role->description = $roleInfo['description'];
        $role->badge_color = $roleInfo['badge_color'];
        $role->is_core = $roleInfo['is_core'];

        $groupedPermissions = PermissionRegistry::getGroupedPermissions();
        $assignedPermissions = $role->permissions->pluck('name')->toArray();
        $totalPermissions = Permission::count();

        return view('roles.edit', compact(
            'role',
            'roleInfo',
            'groupedPermissions',
            'assignedPermissions',
            'totalPermissions'
        ));
    }

    /**
     * Update permissions assigned to a specific role.
     */
    public function update(Request $request, string|int|Role $role): RedirectResponse
    {
        $role = $this->resolveRole($role);
        $this->authorize('update', $role);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $submittedPermissions = $validated['permissions'] ?? [];

        // Protection check for super-admin: Ensure roles.manage is not inadvertently stripped
        if ($role->name === 'super-admin' && !in_array('roles.manage', $submittedPermissions, true)) {
            $submittedPermissions[] = 'roles.manage';
        }

        $oldPermissions = $role->permissions->pluck('name')->toArray();

        // Sync permissions
        $role->syncPermissions($submittedPermissions);

        // Invalidate Spatie cache immediately
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $newPermissions = $role->fresh()->permissions->pluck('name')->toArray();

        AuditLog::record('role_permissions_updated', $role, [
            'role' => $role->name,
            'permissions' => $oldPermissions,
            'count' => count($oldPermissions),
        ], [
            'role' => $role->name,
            'permissions' => $newPermissions,
            'count' => count($newPermissions),
        ]);

        $info = PermissionRegistry::getRoleInfo($role->name);

        return redirect()->route('roles.index')->with(
            'status',
            "Permissions for role '{$info['display_name']}' successfully updated (" . count($newPermissions) . ' permissions enabled).'
        );
    }

    /**
     * Display a cross-tabular matrix of all roles and permissions.
     */
    public function matrix(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')->get();
        $groupedPermissions = PermissionRegistry::getGroupedPermissions();

        // Attach display names
        $roles->each(function (Role $role) {
            $info = PermissionRegistry::getRoleInfo($role->name);
            $role->display_name = $info['display_name'];
            $role->badge_color = $info['badge_color'];
            $role->is_core = $info['is_core'];
        });

        // Sort roles
        $order = ['super-admin', 'ocac', 'pmu', 'district-coordinator', 'field-trainer', 'field-monitor'];
        $roles = $roles->sortBy(function ($role) use ($order) {
            $idx = array_search($role->name, $order, true);
            return $idx !== false ? $idx : 999;
        })->values();

        return view('roles.matrix', compact('roles', 'groupedPermissions'));
    }

    /**
     * Delete a custom role (only allowed if 0 users assigned and not a core role).
     */
    public function destroy(string|int|Role $role): RedirectResponse
    {
        $role = $this->resolveRole($role);

        $protectedRoles = ['super-admin', 'ocac', 'pmu', 'district-coordinator', 'field-trainer', 'field-monitor'];
        if (in_array($role->name, $protectedRoles, true)) {
            abort(403, "System core role '{$role->name}' is protected and cannot be deleted.");
        }

        $this->authorize('delete', $role);

        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return back()->with('error', "Cannot delete role '{$role->name}' because {$userCount} user(s) are currently assigned to it.");
        }

        $oldName = $role->name;
        $role->syncPermissions([]);
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        AuditLog::record('role_deleted', null, ['name' => $oldName], null);

        return redirect()->route('roles.index')->with('status', "Custom role '{$oldName}' was deleted successfully.");
    }

    /**
     * Helper to resolve role by ID or slug string.
     */
    protected function resolveRole(string|int|Role $role): Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        if (is_numeric($role)) {
            return Role::findOrFail((int) $role);
        }

        return Role::where('name', $role)->firstOrFail();
    }
}
