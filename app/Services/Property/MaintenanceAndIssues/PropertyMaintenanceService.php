<?php

namespace App\Services\Property\MaintenanceAndIssues;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use Illuminate\Http\UploadedFile;


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
        CodeDetail   $IssueType,
        CodeDetail   $Priority,
        string   $IssueDescription,
        User    $user,
        UploadedFile $document = null
    ):self {
        
            $lastRequestNumber = PropertyMaintenanceRequest::withTrashed()
                ->where('RequestNumber', 'LIKE', 'REQUEST-%')
                ->selectRaw("MAX(CAST(SUBSTRING(RequestNumber, 8, LEN(RequestNumber)) AS INT)) as max_number")
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
                'IssueType'  => $IssueType->ID,
                'Priority'    => $Priority->ID,
                'IssueDescription' => $IssueDescription,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            if ($document) {
            $maintenancerequest->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyNewLeaseView->value],
                $user
                );
            }

            activity()->causedBy($user->Id)->performedOn($maintenancerequest)->event('create')->log("Added Property Unit {$maintenancerequest->Id}.");
            return new self($maintenancerequest);
        }
    }
    public static function update(
        PropertyMaintenanceRequest $maintenancerequest,
        PropertyRegistry $Property,
        PropertyBlock $Block,
        PropertyFloor $Floor,
        PropertyUnit $Unit,
        string $ReportedBy,
        CodeDetail $IssueType,
        CodeDetail $Priority,
        string $IssueDescription,
        User $user,
        UploadedFile $document = null
    ): self {
        $maintenancerequest->update([
            'Property' => $Property->Id,
            'Block' => $Block->Id,
            'Floor' => $Floor->Id,
            'Unit' => $Unit->Id,
            'ReportedBy' => $ReportedBy,
            'IssueType' => $IssueType->ID,
            'Priority' => $Priority->ID,
            'IssueDescription' => $IssueDescription,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document) {
            $maintenancerequest->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyNewLeaseView->value],
                $user
            );
        }

        activity()
            ->causedBy($user->Id)
            ->performedOn($maintenancerequest)
            ->event('update')
            ->log("Updated Property Unit {$maintenancerequest->Id}.");

        return new self($maintenancerequest);
    }
}
