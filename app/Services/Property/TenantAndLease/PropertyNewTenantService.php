<?php

namespace App\Services\Property\TenantAndLease;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyNewTenant;

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
        CodeDetail $TenantType,
        string $TenantName,
        string $IDRegistrationNo,
        string $PhoneNumber,
        string $EmailAddress,
        string $Nationality,
        string $PostalAddress,
        string $Remarks,
        User $user
    ): self {
        $newtenant = PropertyNewTenant::create([
            'TenantType' => $TenantType->ID,
            'TenantName' => $TenantName,
            'IDRegistrationNo' => $IDRegistrationNo,
            'PhoneNumber' => $PhoneNumber,
            'EmailAddress' => $EmailAddress,
            'Nationality' => $Nationality,
            'PostalAddress' => $PostalAddress,
            'Remarks' => $Remarks,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)
            ->performedOn($newtenant)
            ->event('create')
            ->log("Added New Tenant {$newtenant->Id}.");
        return new self($newtenant);
    }

    public static function update(
        PropertyNewTenant $propertyNewTenant,
        CodeDetail $TenantType,
        string $TenantName,
        string $IDRegistrationNo,
        string $PhoneNumber,
        string $EmailAddress,
        string $Nationality,
        string $PostalAddress,
        string $Remarks,
        User $user
    ): self {
        $propertyNewTenant->update([
            'TenantType' => $TenantType->ID,
            'TenantName' => $TenantName,
            'IDRegistrationNo' => $IDRegistrationNo,
            'PhoneNumber' => $PhoneNumber,
            'EmailAddress' => $EmailAddress,
            'Nationality' =>$Nationality,
            'PostalAddress' => $PostalAddress,
            'Remarks' => $Remarks,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)
            ->performedOn($propertyNewTenant)
            ->event('update')
            ->log("Updated Tenant {$propertyNewTenant->Id}.");
        return new self($propertyNewTenant);
    }
}