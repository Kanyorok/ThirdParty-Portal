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

    'stockadjustmentId' => [
        'Approved' => \App\Enums\Inventory\Transfers::Approved->value,  // 'Ap'
        'Rejected' => \App\Enums\Inventory\Transfers::Rejected->value,  // 'Re'
        'Pending' => \App\Enums\Inventory\Transfers::Pending->value,  // 'P'
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
        'Approved' => \App\Enums\TenderApprovalStatusEnum::APPROVED, // Will now be 'Ap'
        'Rejected' => \App\Enums\TenderApprovalStatusEnum::REJECTED, // Will now be 'R'
        'Pending'  => \App\Enums\TenderApprovalStatusEnum::PENDING,  // Will now be 'P'
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

    'SupplierID' => [
        'Approved' => \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Approved->value,  // 'A'
        'Rejected' => \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Rejected->value,  // 'R'
        'Pending' => \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Pending->value,    // 'P'
        'Submitted for Approval' => \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Submitted->value,  // 'U'
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
