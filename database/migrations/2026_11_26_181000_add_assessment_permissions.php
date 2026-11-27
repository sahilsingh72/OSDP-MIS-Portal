<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $viewPerm = Permission::findOrCreate('assessments.view');
        $managePerm = Permission::findOrCreate('assessments.manage');

        // Grant to roles
        $rolesWithBoth = ['super-admin', 'pmu', 'district-coordinator', 'field-trainer'];
        foreach ($rolesWithBoth as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo([$viewPerm, $managePerm]);
            }
        }

        $rolesWithView = ['ocac', 'field-monitor'];
        foreach ($rolesWithView as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($viewPerm);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('name', ['assessments.view', 'assessments.manage'])->delete();
    }
};
