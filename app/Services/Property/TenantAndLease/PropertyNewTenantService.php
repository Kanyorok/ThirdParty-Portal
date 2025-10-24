<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Http\UploadedFile;

class PropertyNewTenantService
{
    /**
     * Create a new class instance.
     */
    public function __construct(PropertyNewTenant $propertyNewTenant)
    {
        // Initialize the service with the PropertyNewTenant model instance
    }

    public static function create(
        ThirdParties $ThirdPartyId,
        CodeDetail $TenantType,
        string $Remarks = null,
        bool   $IsActive,
        User   $user,
        UploadedFile $document = null
    ): self
    {
        $newtenant = PropertyNewTenant::create([
            'ThirdPartyId' => $ThirdPartyId->Id,
            'TenantType' => $TenantType->ID,
            'Remarks' => $Remarks,
            'IsActive' => $IsActive,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document) {
        $newtenant->newDocument(
            ModulesEnum::Property,
            $document,
            [PermissionEnum::TenantMaintenanceView->value],
            $user
            );
        }

        activity()->causedBy($user->Id)
            ->performedOn($newtenant)
            ->event('create')
            ->log("Added New Tenant {$newtenant->Id}.");
        return new self($newtenant);
    }

    public static function update(
        PropertyNewTenant $propertyNewTenant,
        CodeDetail $TenantType,
        ?string      $Remarks = null,
        bool         $IsActive,
        User         $user,
        UploadedFile $document = null
    ): self
    {
        // Update tenant details
        $propertyNewTenant->update([
            'TenantType' => $TenantType->ID,
            'Remarks' => $Remarks,
            'IsActive' => $IsActive,
            'ModifiedBy' => $user->Id,
        ]);

        if (!empty($document)) {
            foreach ($document as $doc) {
                $propertyNewTenant->newDocument(
                    ModulesEnum::Property,
                    $doc,
                    [PermissionEnum::TenantMaintenanceView->value],
                    $user
                );
            }
        }

        activity()
            ->causedBy($user->Id)
            ->performedOn($propertyNewTenant)
            ->event('update')
            ->log("Updated Tenant {$propertyNewTenant->Id}.");

        return new self($propertyNewTenant);
    }
}
