<?php

namespace App\Constants;

class Permissions
{
    // Section: System
    public const SECTION_SYSTEM = 'system_section';

    public const DASHBOARD_VIEW = 'dashboard_view';

    public const MY_TASKS = 'my_tasks';

    public const ACTIVITY_LOG = 'activity_log';

    // Section: Account
    public const SECTION_ACCOUNT = 'account_section';

    public const MEMBER_DIRECTORY = 'member_directory';

    public const ACCESS_ROLES = 'access_roles';

    // Section: Business
    public const SECTION_BUSINESS = 'business_settings';

    public const BUSINESS_BRANDING = 'business_branding';

    public const EMAIL_SETUP = 'email_setup';

    public const LEGAL_MANAGEMENT = 'legal_management';

    public const BUSINESS_LOGIC = 'business_logic';

    // Section: Hiring
    public const SECTION_HIRING = 'hiring_section';

    public const DEPARTMENTS_MANAGE = 'departments_manage';

    public const TAGS_MANAGE = 'tags_manage';

    public const JOBS_MANAGE = 'jobs_manage';

    public const EVALUATIONS_MANAGE = 'evaluations_manage';

    public const CANDIDATES_VIEW = 'candidates_view';

    public static function getAll(): array
    {
        return [
            self::SECTION_SYSTEM => [
                'label' => 'system_section',
                'icon' => 'LayoutDashboard',
                'sub_modules' => [
                    self::DASHBOARD_VIEW => [
                        'label' => 'dashboard',
                        'description' => 'View system health and metrics',
                        'route' => 'admin.dashboard',
                        'icon' => 'Activity',
                    ],
                    self::MY_TASKS => [
                        'label' => 'My Tasks',
                        'description' => 'View and manage your assigned tasks and evaluations',
                        'route' => 'admin.my_tasks.index',
                        'icon' => 'CheckSquare',
                    ],
                    self::ACTIVITY_LOG => [
                        'label' => 'activity_log',
                        'description' => 'View system activity logs and audit trail',
                        'route' => 'admin.activity_logs.index',
                        'icon' => 'History',
                    ],
                ],
            ],
            self::SECTION_ACCOUNT => [
                'label' => 'account', // Using 'account' as section label key, or management_section? Sidebar uses management_section for title, but account for rail tooltip.
                // Sidebar uses 'account' for rail tooltip, 'management_section' for Tier 2 title.
                // Permissions structure has label 'account'. I'll stick to 'account' and handle Tier 2 title logic in frontend or mapping.
                'icon' => 'Users',
                'sub_modules' => [
                    self::MEMBER_DIRECTORY => [
                        'label' => 'member_directory',
                        'description' => 'Manage member identities and access levels',
                        'route' => 'admin.users.index',
                        'icon' => 'Users',
                    ],
                    self::ACCESS_ROLES => [
                        'label' => 'access_roles',
                        'description' => 'Configure roles and security boundaries',
                        'route' => 'admin.roles.index',
                        'icon' => 'ShieldCheck',
                    ],
                ],
            ],
            self::SECTION_BUSINESS => [
                'label' => 'business_settings',
                'icon' => 'Briefcase',
                'sub_modules' => [
                    self::BUSINESS_BRANDING => [
                        'label' => 'business_branding',
                        'description' => 'Manage organization visual identity',
                        'route' => 'admin.business.branding', // Check route name
                        'icon' => 'Palette',
                    ],
                    self::EMAIL_SETUP => [
                        'label' => 'Email Setup',
                        'description' => 'Configure SMTP email credentials',
                        'route' => 'admin.business.email.index',
                        'icon' => 'Mail',
                    ],
                    self::LEGAL_MANAGEMENT => [
                        'label' => 'legal_management',
                        'description' => 'Manage legal documents (Privacy, Terms, etc.)',
                        'route' => 'admin.legal.index',
                        'icon' => 'Scale',
                    ],
                    self::BUSINESS_LOGIC => [
                        'label' => 'business_logic',
                        'description' => 'Manage business settings (Currency, Timezone, Country)',
                        'route' => 'admin.business.settings.index',
                        'icon' => 'Settings',
                    ],

                ],
            ],
            self::SECTION_HIRING => [
                'label' => 'hiring_management',
                'icon' => 'UserPlus',
                'sub_modules' => [
                    self::JOBS_MANAGE => [
                        'label' => 'jobs',
                        'description' => 'Manage job postings and recruitment',
                        'route' => 'admin.jobs.index',
                        'icon' => 'Briefcase',
                    ],
                    self::EVALUATIONS_MANAGE => [
                        'label' => 'Evaluation',
                        'description' => 'Evaluate candidates and progress pipelines',
                        'route' => 'admin.evaluations.index',
                        'icon' => 'CheckCircle',
                    ],
                    self::CANDIDATES_VIEW => [
                        'label' => 'candidate',
                        'description' => 'View and manage all job applications',
                        'route' => 'admin.candidates.index',
                        'icon' => 'Users',
                    ],
                    self::DEPARTMENTS_MANAGE => [
                        'label' => 'departments',
                        'description' => 'Manage company departments',
                        'route' => 'admin.departments.index',
                        'icon' => 'Building2',
                    ],
                    self::TAGS_MANAGE => [
                        'label' => 'tags',
                        'description' => 'Manage skills and tags',
                        'route' => 'admin.tags.index',
                        'icon' => 'Hash',
                    ],
                ],
            ],
        ];
    }
}
