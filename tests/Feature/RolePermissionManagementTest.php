<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    /**
     * Test guest cannot access role management.
     */
    public function test_guest_cannot_access_roles(): void
    {
        $response = $this->get('/roles');
        $response->assertRedirect('/login');

        $response = $this->get('/roles/district-coordinator/permissions');
        $response->assertRedirect('/login');
    }

    /**
     * Test unauthorized users cannot view or manage roles.
     */
    public function test_unauthorized_user_cannot_access_roles(): void
    {
        $trainer = User::where('email', 'trainer.mohanty@osdp.gov.in')->first();
        $this->assertNotNull($trainer);

        $response = $this->actingAs($trainer)->get('/roles');
        $response->assertStatus(403);

        $response = $this->actingAs($trainer)->get('/roles/field-trainer/permissions');
        $response->assertStatus(403);

        $response = $this->actingAs($trainer)->put('/roles/field-trainer/permissions', [
            'permissions' => ['users.view'],
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test super admin can view roles directory.
     */
    public function test_super_admin_can_view_roles_directory(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/roles');
        $response->assertStatus(200);
        $response->assertSee('Role Permissions');
        $response->assertSee('Access Governance');
        $response->assertSee('district-coordinator');
        $response->assertSee('field-trainer');
    }

    /**
     * Test super admin can view edit permissions screen for a specific role.
     */
    public function test_super_admin_can_view_edit_permissions_for_specific_role(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $dcRole = Role::where('name', 'district-coordinator')->first();
        $this->assertNotNull($dcRole);

        // Access via slug
        $response = $this->actingAs($admin)->get('/roles/district-coordinator/permissions');
        $response->assertStatus(200);
        $response->assertSee('Assign Permissions: District Coordinator (DC)');
        $response->assertSee('batches.create');
        $response->assertSee('attendance.manage');

        // Access via ID resource route
        $responseById = $this->actingAs($admin)->get("/roles/{$dcRole->id}/edit");
        $responseById->assertStatus(200);
        $responseById->assertSee('Assign Permissions: District Coordinator (DC)');
    }

    /**
     * Test super admin can assign and revoke permissions on a specific role.
     */
    public function test_super_admin_can_assign_permissions_to_specific_role(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $dcRole = Role::where('name', 'district-coordinator')->first();

        // New set of permissions to assign
        $targetPermissions = [
            'batches.view',
            'batches.create',
            'attendance.view',
            'reports.view',
        ];

        $response = $this->actingAs($admin)->put('/roles/district-coordinator/permissions', [
            'permissions' => $targetPermissions,
        ]);

        $response->assertRedirect('/roles');
        $response->assertSessionHas('status');

        // Verify role has the assigned permissions
        $dcRole->refresh();
        $this->assertTrue($dcRole->hasPermissionTo('batches.view'));
        $this->assertTrue($dcRole->hasPermissionTo('batches.create'));
        $this->assertTrue($dcRole->hasPermissionTo('attendance.view'));
        $this->assertTrue($dcRole->hasPermissionTo('reports.view'));

        // Verify unassigned permissions are revoked
        $this->assertFalse($dcRole->hasPermissionTo('users.view'));
        $this->assertFalse($dcRole->hasPermissionTo('master-data.manage'));

        // Verify AuditLog recorded
        $auditLog = AuditLog::where('event', 'role_permissions_updated')
            ->where('auditable_id', $dcRole->id)
            ->latest()
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($admin->id, $auditLog->user_id);

        // Restore original permissions for DC
        $dcRole->syncPermissions([
            'users.view',
            'master-data.view',
            'training-plans.view',
            'batches.view',
            'batches.create',
            'batches.update',
            'batches.assign',
            'participants.view',
            'participants.manage',
            'attendance.view',
            'attendance.manage',
            'evidence.view',
            'evidence.upload',
            'evidence.download',
            'evidence.review',
            'field-verifications.view',
            'reports.view',
            'reports.export',
        ]);
    }

    /**
     * Test super admin can create a custom role with permissions.
     */
    public function test_super_admin_can_create_custom_role(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        // Cleanup if previously created
        Role::where('name', 'test-quality-auditor')->delete();

        $response = $this->actingAs($admin)->post('/roles', [
            'name' => 'test-quality-auditor',
            'display_name' => 'Test Quality Auditor',
            'permissions' => [
                'field-verifications.view',
                'field-verifications.create',
                'reports.view',
            ],
        ]);

        $response->assertRedirect('/roles');

        $createdRole = Role::where('name', 'test-quality-auditor')->first();
        $this->assertNotNull($createdRole);
        $this->assertTrue($createdRole->hasPermissionTo('field-verifications.create'));
        $this->assertTrue($createdRole->hasPermissionTo('reports.view'));
        $this->assertFalse($createdRole->hasPermissionTo('users.create'));

        // Clean up
        $createdRole->delete();
    }

    /**
     * Test core system roles cannot be deleted.
     */
    public function test_core_system_roles_cannot_be_deleted(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();
        $dcRole = Role::where('name', 'district-coordinator')->first();

        $response = $this->actingAs($admin)->delete("/roles/{$dcRole->id}");
        $response->assertStatus(403);
    }

    /**
     * Test permissions clearance matrix view.
     */
    public function test_super_admin_can_view_permissions_matrix(): void
    {
        $admin = User::where('email', 'admin@osdp.gov.in')->first();

        $response = $this->actingAs($admin)->get('/roles/matrix');
        $response->assertStatus(200);
        $response->assertSee('Permissions Clearance Matrix');
        $response->assertSee('District Coordinator');
        $response->assertSee('Field Master Trainer');
    }
}
