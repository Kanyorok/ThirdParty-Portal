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
    ): self
    {
        $maintenancerequest = PropertyMaintenanceRequest::create([
            'Property' => $Property,
            'Block' => $Block,
            'Floor' => $Floor,
            'UnitCode' => $Unit,
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
