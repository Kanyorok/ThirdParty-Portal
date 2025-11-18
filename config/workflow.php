<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Workflow Status Mappings
    |--------------------------------------------------------------------------
    |
    | Define status value mappings for each module (based on morph alias).
    | Each module maps CodeDetail descriptions to the desired status value
    | (e.g., enum values or strings) for the source table.
    |
    | Format: 'morph_alias' => ['Description' => 'status_value']
    |
    */
    'department_needs' => [
        'Approved' => \App\Enums\Procurement\DepartmentNeedsEnum::Approved->value,  // 'a'
        // 'Approval' => \App\Enums\Procurement\DepartmentNeedsEnum::Approved->value,  // 'a'
        'Rejected' => \App\Enums\Procurement\DepartmentNeedsEnum::Rejected->value,  // 'r'
        'Pending' => \App\Enums\Procurement\DepartmentNeedsEnum::Pending->value,    // 'p'
        'Submitted for Approval' => \App\Enums\Procurement\DepartmentNeedsEnum::Submitted->value,  // 's'
    ],

    'PlanID' => [
        'Approved' => \App\Enums\ProcurementPlanStatusEnum::Approved->value,  // 'Ap'
        'Rejected' => \App\Enums\ProcurementPlanStatusEnum::Rejected->value,  // 'Re'
        'Pending' => \App\Enums\ProcurementPlanStatusEnum::Pending->value,    // 'P'
        'Submitted for Approval' => \App\Enums\ProcurementPlanStatusEnum::Submitted->value,  // 'Su'
    ],
];