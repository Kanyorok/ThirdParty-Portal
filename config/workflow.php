<?php
use App\Enums\WorkflowStatus;   
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
        'Draft' => \App\Enums\ProcurementPlanStatusEnum::Draft->value,  // 'Dr'
        'Approved' => \App\Enums\ProcurementPlanStatusEnum::Approved->value,  // 'Ap'
        'Rejected' => \App\Enums\ProcurementPlanStatusEnum::Rejected->value,  // 'Re'
        'Pending' => \App\Enums\ProcurementPlanStatusEnum::Pending->value,    // 'P'
        'Submitted for Approval' => \App\Enums\ProcurementPlanStatusEnum::Submitted->value,  // 'Su'
    ],

    'LeaseId' => [
        'Approved' => \App\Enums\Core\ApprovalEnum::Approved->value,  // 'a'
        'Rejected' => \App\Enums\Core\ApprovalEnum::Rejected->value,  // 'r'
        'Pending' => \App\Enums\Core\ApprovalEnum::Pending->value,    // 'p'
        'Submitted for Approval' => \App\Enums\Core\ApprovalEnum::Submitted->value,  // 's'
    ],

    'LeaseTerminationId' => [
        'Approved' => \App\Enums\Core\ApprovalEnum::Approved->value,  // 'a'
        'Rejected' => \App\Enums\Core\ApprovalEnum::Rejected->value,  // 'r'
        'Pending' => \App\Enums\Core\ApprovalEnum::Pending->value,    // 'p'
        'Submitted for Approval' => \App\Enums\Core\ApprovalEnum::Submitted->value,  // 's'
    ],

    'OrderID' => [
        'Approved' => \App\Enums\Core\ApprovalEnum::Approved->value,  // 'A'
        'Rejected' => \App\Enums\Core\ApprovalEnum::Rejected->value,  // 'R'
        'Pending' => \App\Enums\Core\ApprovalEnum::Pending->value,    // 'P'
        'Submitted for Approval' => \App\Enums\Core\ApprovalEnum::Submitted->value,  // 'S'
    ],

    'tender' => [
        'Approved' => \App\Enums\TenderApprovalStatusEnum::APPROVED->value, // Will now be 'Ap'
        'Rejected' => \App\Enums\TenderApprovalStatusEnum::REJECTED->value, // Will now be 'R'
        'Pending'  => \App\Enums\TenderApprovalStatusEnum::PENDING->value,  // Will now be 'P'
    ],
    'LeaseTerminationId' => [
        'Approved' => \App\Enums\Core\ApprovalEnum::Approved->value,  // 'a'
        'Rejected' => \App\Enums\Core\ApprovalEnum::Rejected->value,  // 'r'
        'Pending' => \App\Enums\Core\ApprovalEnum::Pending->value,    // 'p'
        'Submitted for Approval' => \App\Enums\Core\ApprovalEnum::Submitted->value,  // 's'
    ],

    'ScheduleRenewalId' => [
        'Approved' => \App\Enums\Core\ApprovalEnum::Approved->value,  // 'a'
        'Rejected' => \App\Enums\Core\ApprovalEnum::Rejected->value,  // 'r'
        'Pending' => \App\Enums\Core\ApprovalEnum::Pending->value,    // 'p'
        'Submitted for Approval' => \App\Enums\Core\ApprovalEnum::Submitted->value,  // 's'
    ],


     'RFQId' => [
        'Approved' => WorkflowStatus::Approved->value,  // 'A'
        'Rejected' => WorkflowStatus::Rejected->value,  // 'R'
        'Pending' => WorkflowStatus::Pending->value,    // 'P'
        'Submitted for Approval' => WorkflowStatus::Submitted->value,  // 'S'
    ],

    'TripId' => [
        'Scheduled' => \App\Enums\Core\ApprovalEnum::Scheduled->value,  // 'Sc'
        'Approved' => \App\Enums\Core\ApprovalEnum::Approved->value,  // 'A'
        'Rejected' => \App\Enums\Core\ApprovalEnum::Rejected->value,  // 'R'
        'Ongoing' => \App\Enums\Core\ApprovalEnum::Ongoing->value,  // 'Og'
        'Completed' => \App\Enums\Core\ApprovalEnum::Completed->value,  // 'Co'
        'Cancelled' => \App\Enums\Core\ApprovalEnum::Cancelled->value,  // 'Ca'
    ],



    /*
    |--------------------------------------------------------------------------
    | Status Column Mappings
    |--------------------------------------------------------------------------
    | Maps morph aliases to their status column names
    | This allows different modules to use different column names
    */

    'status_columns' => [
        'department_need' => 'Status',
        'property_new_lease' => 'ApprovalStatus',
        'lease_creation' => 'ApprovalStatus',
        'procurement_plan' => 'Status',
        'consolidated_procurement_plan' => 'Status',
        
        // Add more as needed
    ],

];
