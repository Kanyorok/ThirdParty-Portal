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
        'Draft'=> \App\Enums\ProcurementPlanStatusEnum::Draft->value,  // 'Dr'
        'Approved' => \App\Enums\ProcurementPlanStatusEnum::Approved->value,  // 'Ap'
        'Rejected' => \App\Enums\ProcurementPlanStatusEnum::Rejected->value,  // 'Re'
        'Pending' => \App\Enums\ProcurementPlanStatusEnum::Pending->value,    // 'P'
        'Submitted for Approval' => \App\Enums\ProcurementPlanStatusEnum::Submitted->value,  // 'Su'
    ],

    'RequisitionId' => [
        'Approved' => \App\Enums\Inventory\InterBranchRequisitionEnum::Approved->value,  // 'Ap'
        'Rejected' => \App\Enums\Inventory\InterBranchRequisitionEnum::Rejected->value,  // 'Re'
        'Submitted for Approval' => \App\Enums\Inventory\InterBranchRequisitionEnum::Pending->value,  // 'P'
    ],

    'TransferId' => [
        'Approved' => \App\Enums\Inventory\Transfers::Approved->value,  // 'Ap'
        'Rejected' => \App\Enums\Inventory\Transfers::Rejected->value,  // 'Re'
        'Pending' => \App\Enums\Inventory\Transfers::Pending->value,  // 'P'
        'In Transit' => \App\Enums\Inventory\Transfers::InTransit->value,  // 'it'
        'Delivered'=> \App\Enums\Inventory\Transfers::Delivered->value,  // 'de'
        'Returned'=> \App\Enums\Inventory\Transfers::Returned->value,  // 'rt'


    ],
];