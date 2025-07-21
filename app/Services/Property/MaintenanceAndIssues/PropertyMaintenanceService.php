<?php

namespace App\Services\Property\MaintenanceAndIssues;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;


class PropertyMaintenanceService
{
    /**
     * Create a new class instance.
     */
        public function __construct(public PropertyMaintenanceRequest $maintenancerequest)
    {
        //
    }
   public static function create(
        PropertyRegistry $Property,
        PropertyBlock $Block,
        PropertyFloor $Floor,
        PropertyUnit  $Unit,
        string   $ReportedBy,
        string   $IssueType,
        string   $Priority,
        string   $IssueDescription,
        User    $user
    ):self {

            $lastRequestNumber = PropertyMaintenanceRequest::withTrashed() // in case you're using soft deletes
                ->selectRaw("MAX(CAST(SUBSTRING(RequestNumber, 7, LEN(RequestNumber)) AS INT)) as max_number")
                ->value('max_number');

            $nextNumber = $lastRequestNumber ? $lastRequestNumber + 1 : 1;
            $RequestNumber = 'REQUEST-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

    {
        $maintenancerequest = PropertyMaintenanceRequest::create([
            'RequestNumber' => $RequestNumber,
            'Property' => $Property->Id,
            'Block' => $Block->Id,
            'Floor' => $Floor->Id,
            'Unit' => $Unit->Id,
            'ReportedBy' =>$ReportedBy,
            'IssueType'  => $IssueType,
            'Priority'    => $Priority,
            'IssueDescription' => $IssueDescription,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($maintenancerequest)->event('create')->log("Added Property Unit {$maintenancerequest->Id}.");
        return new self($maintenancerequest);
    }
}
}
