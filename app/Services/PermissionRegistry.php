<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;

class PermissionRegistry
{
    /**
     * Predefined permission metadata for clean presentation.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $definitions = [
        // User Management
        'users.view' => [
            'label' => 'View User Accounts',
            'description' => 'Browse system users, profile details, designations, and assigned district scopes.',
            'category' => 'User Management',
        ],
        'users.create' => [
            'label' => 'Create New Users',
            'description' => 'Provision new administrative accounts, allocate roles, and assign initial jurisdictions.',
            'category' => 'User Management',
        ],
        'users.update' => [
            'label' => 'Edit User Information',
            'description' => 'Update user profiles, change contact details, passwords, and reconfigure district jurisdictions.',
            'category' => 'User Management',
        ],
        'users.deactivate' => [
            'label' => 'Activate / Deactivate Users',
            'description' => 'Suspend active accounts or reactivate previously deactivated users from accessing the system.',
            'category' => 'User Management',
        ],

        // Roles & Governance
        'roles.manage' => [
            'label' => 'Manage Roles & Permissions',
            'description' => 'Configure system roles, assign or revoke module permissions, and manage access governance.',
            'category' => 'Roles & Access Governance',
        ],

        // Master Data
        'master-data.view' => [
            'label' => 'View Master Data',
            'description' => 'Browse districts, blocks, gram panchayats, certified venues, and curriculum training modules.',
            'category' => 'Master Data & Geo-Registry',
        ],
        'master-data.manage' => [
            'label' => 'Manage Venues & Modules',
            'description' => 'Create, update, and manage certified training venues and official training curriculum modules.',
            'category' => 'Master Data & Geo-Registry',
        ],

        // Training Programmes
        'training-plans.view' => [
            'label' => 'View Training Programmes',
            'description' => 'Access state training programme tiers, syllabus details, and scheduled batch plans.',
            'category' => 'Curriculum & Programmes',
        ],
        'training-plans.create' => [
            'label' => 'Create Training Programmes',
            'description' => 'Design new training frameworks, define required modules, and set target candidate counts.',
            'category' => 'Curriculum & Programmes',
        ],
        'training-plans.update' => [
            'label' => 'Update Training Programmes',
            'description' => 'Revise training programme guidelines, syllabi, and administrative requirements.',
            'category' => 'Curriculum & Programmes',
        ],

        // Training Batches
        'batches.view' => [
            'label' => 'View Training Batches',
            'description' => 'View scheduled, in-progress, completed, and verified training batches within authorized district scope.',
            'category' => 'Training Batches & Operations',
        ],
        'batches.create' => [
            'label' => 'Schedule Training Batches',
            'description' => 'Create and schedule new training batches, select certified venues, and assign training dates.',
            'category' => 'Training Batches & Operations',
        ],
        'batches.update' => [
            'label' => 'Modify Training Batches',
            'description' => 'Edit batch logistics, update dates, change venues, and adjust curriculum modules.',
            'category' => 'Training Batches & Operations',
        ],
        'batches.assign' => [
            'label' => 'Assign Trainers & Personnel',
            'description' => 'Allocate certified master trainers and field teams to scheduled training batches.',
            'category' => 'Training Batches & Operations',
        ],

        // Participants
        'participants.view' => [
            'label' => 'View Enrolled Participants',
            'description' => 'Inspect participant enrollment records, contact numbers, and Aadhaar/identification status.',
            'category' => 'Participants & Trainees (Form 2)',
        ],
        'participants.manage' => [
            'label' => 'Manage Participant Enrollment',
            'description' => 'Add new participants to batches, import candidate rosters, and update participant records.',
            'category' => 'Participants & Trainees (Form 2)',
        ],

        // Attendance
        'attendance.view' => [
            'label' => 'View Attendance Records',
            'description' => 'Inspect digital and transcribed Form 2 attendance logs, daily headcounts, and completion records.',
            'category' => 'Attendance & Transcriptions',
        ],
        'attendance.manage' => [
            'label' => 'Transcribe & Finalize Attendance',
            'description' => 'Record daily attendance check-ins, transcribe physical Form 2 sign-in sheets, and lock attendance.',
            'category' => 'Attendance & Transcriptions',
        ],

        // Evidence Vault
        'evidence.view' => [
            'label' => 'View Photographic Evidence',
            'description' => 'Access uploaded batch evidence files, geotagged classroom photos, banners, and Form 2 scans.',
            'category' => 'Evidence & Document Vault',
        ],
        'evidence.upload' => [
            'label' => 'Upload Batch Evidence',
            'description' => 'Upload mandatory classroom photographs, high-resolution attendance scans, and training documents.',
            'category' => 'Evidence & Document Vault',
        ],
        'evidence.download' => [
            'label' => 'Download Private Evidence',
            'description' => 'Securely download original high-resolution evidence artifacts, zip bundles, and signed forms.',
            'category' => 'Evidence & Document Vault',
        ],
        'evidence.review' => [
            'label' => 'Review & Audit Evidence',
            'description' => 'Scrutinize uploaded evidence artifacts against quality standards and compliance checklists.',
            'category' => 'Evidence & Document Vault',
        ],

        // Reviews
        'reviews.manage' => [
            'label' => 'Approve or Reject Batches',
            'description' => 'Process the review queue, approve batch evidence for completion, or reject with corrective feedback.',
            'category' => 'Review & Approval Queue',
        ],

        // Field Verifications
        'field-verifications.view' => [
            'label' => 'View Field Inspection Reports',
            'description' => 'Access independent field inspection reports, monitoring scores, and on-site visit notes.',
            'category' => 'Independent Field Monitoring',
        ],
        'field-verifications.create' => [
            'label' => 'Conduct Field Inspections',
            'description' => 'Perform surprise inspection visits, capture GPS locations, and submit formal verification reports.',
            'category' => 'Independent Field Monitoring',
        ],

        // Reports
        'reports.view' => [
            'label' => 'View Analytics & MIS Reports',
            'description' => 'Inspect real-time analytics, district training progress, target vs achievement graphs, and completion metrics.',
            'category' => 'Reports & MIS Analytics',
        ],
        'reports.export' => [
            'label' => 'Export Official Reports (CSV)',
            'description' => 'Export raw transaction records, participant directories, and compliance reports to CSV files.',
            'category' => 'Reports & MIS Analytics',
        ],

        // Audit Logs
        'audit-logs.view' => [
            'label' => 'View Security Audit Trail',
            'description' => 'Examine immutable event logs, user IP addresses, timestamps, and data change histories.',
            'category' => 'Audit Trail & Security Logs',
        ],

        // System Settings
        'settings.manage' => [
            'label' => 'Configure System Settings',
            'description' => 'Modify operational parameters, 48-hour submission SLA deadlines, and file upload size constraints.',
            'category' => 'System Configuration & SLAs',
        ],
    ];

    /**
     * Category order and metadata.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $categoryMeta = [
        'User Management' => [
            'icon' => 'user-group',
            'description' => 'User accounts provisioning, identity management, and district scoping.',
        ],
        'Roles & Access Governance' => [
            'icon' => 'shield',
            'description' => 'Role creation, permission matrix mapping, and administrative privileges.',
        ],
        'Master Data & Geo-Registry' => [
            'icon' => 'database',
            'description' => 'Administrative boundaries, certified venue directory, and curriculum modules.',
        ],
        'Curriculum & Programmes' => [
            'icon' => 'academic-cap',
            'description' => 'Training framework syllabi, tier allocations, and curriculum schedules.',
        ],
        'Training Batches & Operations' => [
            'icon' => 'calendar',
            'description' => 'Scheduling batches, venues allocation, date ranges, and trainer assignment.',
        ],
        'Participants & Trainees (Form 2)' => [
            'icon' => 'identification',
            'description' => 'Candidate rosters, trainee demographics, and enrollment verification.',
        ],
        'Attendance & Transcriptions' => [
            'icon' => 'clipboard-check',
            'description' => 'Form 2 physical transcription, daily check-in verification, and locking.',
        ],
        'Evidence & Document Vault' => [
            'icon' => 'photograph',
            'description' => 'Geo-tagged photos, banner proof, Form 2 high-res uploads, and downloads.',
        ],
        'Review & Approval Queue' => [
            'icon' => 'badge-check',
            'description' => 'Multi-tier verification, batch approvals, rejections, and corrective notes.',
        ],
        'Independent Field Monitoring' => [
            'icon' => 'clipboard-list',
            'description' => 'On-site unannounced audits, monitoring scores, and GPS verification.',
        ],
        'Reports & MIS Analytics' => [
            'icon' => 'chart-bar',
            'description' => 'Executive analytics, district KPIs, and CSV data extraction.',
        ],
        'Audit Trail & Security Logs' => [
            'icon' => 'history',
            'description' => 'Immutable audit history, authentication records, and system changes.',
        ],
        'System Configuration & SLAs' => [
            'icon' => 'cog',
            'description' => 'Operational deadlines, SLA timers, and global portal variables.',
        ],
    ];

    /**
     * Get metadata for a specific permission.
     */
    public static function getMeta(string $permissionName): array
    {
        if (isset(self::$definitions[$permissionName])) {
            return self::$definitions[$permissionName];
        }

        // Fallback for dynamically added permissions
        $parts = explode('.', $permissionName, 2);
        $prefix = $parts[0] ?? 'General';
        $category = ucfirst(str_replace('-', ' ', $prefix)) . ' Management';

        return [
            'label' => ucwords(str_replace(['.', '-', '_'], ' ', $permissionName)),
            'description' => 'Permission to perform ' . str_replace('.', ' ', $permissionName) . ' operations in the system.',
            'category' => $category,
        ];
    }

    /**
     * Get metadata for a category.
     */
    public static function getCategoryMeta(string $category): array
    {
        return self::$categoryMeta[$category] ?? [
            'icon' => 'folder',
            'description' => 'Permissions related to ' . $category . '.',
        ];
    }

    /**
     * Get all permissions from the database grouped by category, with metadata.
     *
     * @return array<string, array{meta: array, permissions: \Illuminate\Support\Collection}>
     */
    public static function getGroupedPermissions(): array
    {
        $allPermissions = Permission::orderBy('name')->get();

        $grouped = [];

        // Pre-initialize in preferred category order
        foreach (array_keys(self::$categoryMeta) as $category) {
            $grouped[$category] = [
                'meta' => self::getCategoryMeta($category),
                'permissions' => collect(),
            ];
        }

        foreach ($allPermissions as $permission) {
            $meta = self::getMeta($permission->name);
            $category = $meta['category'];

            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'meta' => self::getCategoryMeta($category),
                    'permissions' => collect(),
                ];
            }

            $permission->label = $meta['label'];
            $permission->description = $meta['description'];
            $permission->category = $category;

            $grouped[$category]['permissions']->push($permission);
        }

        // Filter out empty categories
        return array_filter($grouped, fn($group) => $group['permissions']->isNotEmpty());
    }

    /**
     * Get role summary description and display name.
     */
    public static function getRoleInfo(string $roleName): array
    {
        $roles = [
            'super-admin' => [
                'display_name' => 'Super Administrator',
                'description' => 'State technical administration with unrestricted access across all portal modules and governance.',
                'badge_color' => 'rose',
                'is_core' => true,
            ],
            'ocac' => [
                'display_name' => 'OCAC Nodal Authority',
                'description' => 'Apex project monitoring, overall policy supervision, compliance verification, and audit access.',
                'badge_color' => 'indigo',
                'is_core' => true,
            ],
            'pmu' => [
                'display_name' => 'Project Management Unit (PMU)',
                'description' => 'Operational oversight, training batch scheduling, master data control, and state verification.',
                'badge_color' => 'blue',
                'is_core' => true,
            ],
            'district-coordinator' => [
                'display_name' => 'District Coordinator (DC)',
                'description' => 'District-level batch execution, venue scheduling, trainer allocations, and Form 2 review sign-off.',
                'badge_color' => 'amber',
                'is_core' => true,
            ],
            'field-trainer' => [
                'display_name' => 'Field Master Trainer',
                'description' => 'Classroom training delivery, daily Form 2 transcription, and mandatory evidence upload.',
                'badge_color' => 'emerald',
                'is_core' => true,
            ],
            'field-monitor' => [
                'display_name' => 'Independent Field Monitor',
                'description' => 'Third-party surprise field inspections, geo-location checks, and quality compliance scores.',
                'badge_color' => 'purple',
                'is_core' => true,
            ],
        ];

        return $roles[$roleName] ?? [
            'display_name' => ucwords(str_replace(['-', '_'], ' ', $roleName)),
            'description' => 'Custom administrative role configured for operational responsibilities in the portal.',
            'badge_color' => 'slate',
            'is_core' => false,
        ];
    }
}
