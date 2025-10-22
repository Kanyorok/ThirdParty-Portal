<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vendor Public Key
    |--------------------------------------------------------------------------
    |
    | The Ed25519 public key used to verify license signatures.
    | This should be a base64-encoded public key provided by the vendor.
    | Keep this secure and never expose the corresponding private key.
    |
    */
    'vendor_public_key' => env('LICENSING_VENDOR_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Grace Period Days
    |--------------------------------------------------------------------------
    |
    | Number of days after license expiry that the system will continue
    | to function with warning messages before completely blocking access.
    |
    */
    'grace_period_days' => env('LICENSING_GRACE_PERIOD_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) to cache license validation results.
    | Shorter durations provide more security but may impact performance.
    |
    */
    'cache_duration' => env('LICENSING_CACHE_DURATION', 300), // 5 minutes

    /*
    |--------------------------------------------------------------------------
    | Module Key Mappings
    |--------------------------------------------------------------------------
    |
    | Map internal module IDs to license module keys.
    | This allows for cleaner license keys while maintaining compatibility
    | with existing module numbering system.
    |
    */
    'module_mappings' => [
        // Main Modules (based on ModulesEnum)
        100000 => 'THIRDPARTY',
        200000 => 'CRM',
        300000 => 'PROCUREMENT',
        400000 => 'INVENTORY',
        500000 => 'PROPERTY',
        600000 => 'FLEET',
        700000 => 'DMS',
        800000 => 'LEGAL',
        900000 => 'INSURANCE',
        1000000 => 'HRM',
        1100000 => 'FINANCE',
        1200000 => 'BUDGET',
        9800000 => 'SETTINGS',
        9900000 => 'ACCOUNT',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Display Names
    |--------------------------------------------------------------------------
    |
    | Human-readable names for modules to display in UI
    |
    */
    'module_names' => [
        'THIRDPARTY' => 'Third Party Management',
        'CRM' => 'Customer Management',
        'PROCUREMENT' => 'Procurement',
        'INVENTORY' => 'Inventory Management',
        'PROPERTY' => 'Property Management',
        'FLEET' => 'Fleet Management',
        'DMS' => 'Document Management',
        'LEGAL' => 'Legal & Compliance',
        'INSURANCE' => 'Insurance & Bancassurance',
        'HRM' => 'Human Resources',
        'FINANCE' => 'Financial Management',
        'BUDGET' => 'Budget & Analytics',
        'SETTINGS' => 'System Settings',
        'ACCOUNT' => 'My Account',
    ],

    /*
    |--------------------------------------------------------------------------
    | Edition Definitions
    |--------------------------------------------------------------------------
    |
    | Define what modules are included in each edition
    |
    */
    'editions' => [
        'Community' => ['THIRDPARTY', 'SETTINGS', 'ACCOUNT'],
        'Professional' => ['THIRDPARTY', 'CRM', 'PROCUREMENT', 'INVENTORY', 'FINANCE', 'SETTINGS', 'ACCOUNT'],
        'Enterprise' => ['THIRDPARTY', 'CRM', 'PROCUREMENT', 'INVENTORY', 'PROPERTY', 'FLEET', 'DMS', 'LEGAL', 'INSURANCE', 'HRM', 'FINANCE', 'BUDGET', 'SETTINGS', 'ACCOUNT'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Premium Features
    |--------------------------------------------------------------------------
    |
    | Define premium features that can be enabled/disabled via licensing
    |
    */
    'premium_features' => [
        'drivers_based_budgeting' => 'Drivers-Based Budgeting',
        'bancassurance' => 'Bancassurance Products',
        'advanced_reporting' => 'Advanced Reporting & Analytics',
        'api_access' => 'External API Access',
        'mobile_app' => 'Mobile Application',
        'integration_hub' => 'Third-Party Integrations',
        'audit_trail' => 'Advanced Audit Trail',
        'multi_tenant' => 'Multi-Tenant Architecture',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Limits
    |--------------------------------------------------------------------------
    |
    | Default system limits that can be overridden by license
    |
    */
    'default_limits' => [
        'max_users' => 5,
        'max_branches' => 1,
        'max_storage_gb' => 1,
        'api_requests_per_hour' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | License Validation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how license validation behaves
    |
    */
    'validation' => [
        'clock_skew_tolerance' => 30, // seconds
        'instance_fingerprint_tolerance' => true, // allow some variance in fingerprint
        'require_online_validation' => false, // require periodic online validation
        'online_validation_interval' => 7, // days
    ],
];
