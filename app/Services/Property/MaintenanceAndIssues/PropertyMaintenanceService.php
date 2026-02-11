<?php

namespace App\Services\Property\MaintenanceAndIssues;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Http\UploadedFile;

class PropertyMaintenanceService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public PropertyMaintenanceRequest $maintenancerequest)
    {
    }

    public static function create(
        PropertyRegistry $Property,
        ?PropertyBlock $Block = null,
        ?PropertyFloor $Floor = null,
        ?PropertyUnit $Unit = null,
        ThirdParties $ReportedBy,
        CodeDetail $IssueType,
        CodeDetail $Priority,
        string $IssueDescription,
        User $user,
        UploadedFile $document = null
    ): self {

        $lastRequestNumber = PropertyMaintenanceRequest::withTrashed()
            ->selectRaw("CAST(SUBSTRING(RequestNumber, 9, 5) AS INT) as num")
            ->orderByDesc('num')
            ->value('num');

        $nextNumber = $lastRequestNumber ? $lastRequestNumber + 1 : 1;
        $RequestNumber = 'REQUEST-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        {
            $maintenancerequest = PropertyMaintenanceRequest::create([
                'RequestNumber' => $RequestNumber,
                'Property' => $Property->Id,
                'Block' => $Block->Id ?? null,
                'Floor' => $Floor->Id ?? null,
                'Unit' => $Unit->Id ?? null,
                'ReportedBy' => $ReportedBy->Id,
                'IssueType' => $IssueType->ID,
                'Priority' => $Priority->ID,
                'IssueDescription' => $IssueDescription,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

        if ($document) {
            $maintenancerequest->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyMaintenanceRequestView->value],
                $user
            );
        }

            activity()->causedBy($user->Id)->performedOn($maintenancerequest)->event('create')->log("Added Property Unit {$maintenancerequest->Id}.");

            return new self($maintenancerequest);
        }
    }

    public static function update(
        PropertyMaintenanceRequest $maintenancerequest,
        PropertyRegistry $Property = null,
        PropertyBlock $Block = null,
        PropertyFloor $Floor = null,
        PropertyUnit $Unit = null,
        ThirdParties $ReportedBy,
        CodeDetail $IssueType,
        CodeDetail $Priority,
        string $IssueDescription,
        User $user,
        UploadedFile $document = null
    ): self {
        $maintenancerequest->update([
            'Property' => $Property->Id,
            'Block' => $Block->Id ?? null,
            'Floor' => $Floor->Id ?? null,
            'Unit' => $Unit->Id ?? null,
            'ReportedBy' => $ReportedBy->Id,
            'IssueType' => $IssueType->ID,
            'Priority' => $Priority->ID,
            'IssueDescription' => $IssueDescription,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document) {
            $maintenancerequest->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyMaintenanceRequestView->value],
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
