<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions
        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.deactivate',
            'roles.manage',
            'master-data.view',
            'master-data.manage',
            'training-plans.view',
            'training-plans.create',
            'training-plans.update',
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
            'reviews.manage',
            'field-verifications.view',
            'field-verifications.create',
            'reports.view',
            'reports.export',
            'audit-logs.view',
            'settings.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        // 2. Define Roles
        $superAdmin = Role::findOrCreate('super-admin');
        $superAdmin->givePermissionTo(Permission::all());

        $ocac = Role::findOrCreate('ocac');
        $ocac->givePermissionTo([
            'users.view',
            'master-data.view',
            'training-plans.view',
            'batches.view',
            'participants.view',
            'attendance.view',
            'evidence.view',
            'evidence.download',
            'field-verifications.view',
            'reports.view',
            'reports.export',
            'audit-logs.view',
        ]);

        $pmu = Role::findOrCreate('pmu');
        $pmu->givePermissionTo([
            'users.view',
            'users.create',
            'users.update',
            'users.deactivate',
            'master-data.view',
            'master-data.manage',
            'training-plans.view',
            'training-plans.create',
            'training-plans.update',
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
            'reviews.manage',
            'field-verifications.view',
            'reports.view',
            'reports.export',
            'audit-logs.view',
        ]);

        $dc = Role::findOrCreate('district-coordinator');
        $dc->givePermissionTo([
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

        $trainer = Role::findOrCreate('field-trainer');
        $trainer->givePermissionTo([
            'master-data.view',
            'training-plans.view',
            'batches.view',
            'participants.view',
            'participants.manage',
            'attendance.view',
            'attendance.manage',
            'evidence.view',
            'evidence.upload',
            'evidence.download',
            'reports.view',
        ]);

        $monitor = Role::findOrCreate('field-monitor');
        $monitor->givePermissionTo([
            'master-data.view',
            'training-plans.view',
            'batches.view',
            'participants.view',
            'participants.manage',
            'attendance.view',
            'evidence.view',
            'evidence.download',
            'field-verifications.view',
            'field-verifications.create',
            'reports.view',
        ]);

        // 3. Create Seed Users
        $defaultPassword = Hash::make('Admin@12345');

        // Super Admin
        $uAdmin = User::updateOrCreate(
            ['email' => 'admin@osdp.gov.in'],
            [
                'name' => 'System Administrator',
                'phone' => '9437011111',
                'designation' => 'Technical Director',
                'department' => 'OCAC / IT Dept',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $uAdmin->syncRoles(['super-admin']);

        // OCAC
        $uOcac = User::updateOrCreate(
            ['email' => 'ocac@osdp.gov.in'],
            [
                'name' => 'OCAC Nodal Officer',
                'phone' => '9437022222',
                'designation' => 'General Manager (Projects)',
                'department' => 'Odisha Computer Application Centre',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $uOcac->syncRoles(['ocac']);

        // PMU
        $uPmu = User::updateOrCreate(
            ['email' => 'pmu@osdp.gov.in'],
            [
                'name' => 'PMU Lead Manager',
                'phone' => '9437033333',
                'designation' => 'State Project Manager',
                'department' => 'Central PMU - OSDP 2.0',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $uPmu->syncRoles(['pmu']);

        // District Coordinator (Khordha)
        $uDcKhd = User::updateOrCreate(
            ['email' => 'dc.khordha@osdp.gov.in'],
            [
                'name' => 'Bijay Kumar Nayak',
                'phone' => '9437044444',
                'designation' => 'District Coordinator - Khordha',
                'department' => 'District Administration Khordha',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $uDcKhd->syncRoles(['district-coordinator']);

        // District Coordinator (Cuttack)
        $uDcCtk = User::updateOrCreate(
            ['email' => 'dc.cuttack@osdp.gov.in'],
            [
                'name' => 'Smita Dash',
                'phone' => '9437055555',
                'designation' => 'District Coordinator - Cuttack',
                'department' => 'District Administration Cuttack',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $uDcCtk->syncRoles(['district-coordinator']);

        // Field Trainers
        $uTrainer1 = User::updateOrCreate(
            ['email' => 'trainer.mohanty@osdp.gov.in'],
            [
                'name' => 'Subrat Mohanty',
                'phone' => '9437066666',
                'designation' => 'Senior Master Trainer',
                'department' => 'Training Division',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $uTrainer1->syncRoles(['field-trainer']);

        $uTrainer2 = User::updateOrCreate(
            ['email' => 'trainer.patnaik@osdp.gov.in'],
            [
                'name' => 'Ananya Patnaik',
                'phone' => '9437077777',
                'designation' => 'Master Trainer (Cyber & Privacy)',
                'department' => 'Training Division',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $uTrainer2->syncRoles(['field-trainer']);

        // Independent Field Monitor
        $uMonitor = User::updateOrCreate(
            ['email' => 'monitor.das@osdp.gov.in'],
            [
                'name' => 'Prakash Chandra Das',
                'phone' => '9437088888',
                'designation' => 'Independent Field Quality Auditor',
                'department' => 'Third-Party Monitoring Agency',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $uMonitor->syncRoles(['field-monitor']);
    }
}
