<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Finance\FinanceRole;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Services\ThirdParties\ThirdPartiesService;
use App\Services\ThirdParties\ThirdPartyService;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class PropertyNewTenantService extends ThirdPartiesService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public PropertyNewTenant $propertyNewTenant)
    {
        parent::__construct($propertyNewTenant->thirdParty);
    }

    public static function getType(): ThirdPartyType
    {
        return ThirdPartyType::query()->withTrashed()->where('Code', ThirdPartyService::TypeTenant)->firstOr(function () {
            $role = FinanceRole::query()->first();// todo fix your Finance role
            if ($role instanceof FinanceRole === false) {
                throw new RuntimeException("No finance roles found " . __CLASS__);
            }
            $actor = SystemHelper::user();
            return ThirdPartyType::create([
                'FinanceRole' => $role->FinanceRoleID,
                'Code' => ThirdPartyService::TypeTenant,
                'Description' => 'Tenant',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);
        });
    }

    /**
     * @throws ErroredException
     */
    public static function createFromParty(ThirdParties $party, User $user, UploadedFile $document = null, string $Remarks = null): self
    {
        $tenant = PropertyNewTenant::create([
            'ThirdPartyId' => $party->Id,
            'TenantType' => $party->BusinessType,
            'Remarks' => $Remarks,
            'IsActive' => true,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document instanceof UploadedFile) {
            $tenant->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::TenantMaintenanceView->value],
                $user
            );
        }

        activity()->causedBy($user->Id)->performedOn($tenant)->event('create')->log("Added New Tenant {$tenant->Id}.");
        $service = new self($tenant);
        $service->addType(self::getType(), PropertyNewTenant::getPrimaryKey(), $tenant->Id, $user);
        return $service;
    }

    public static function create(
        string  $name, ?string $tradingName, CodeDetail $businessType, string $registrationNumber, string $taxPIN, ?string $vatNumber, Locality $locationID, ?string $physicalAddress,
        ?string $email, ?string $phone, ?string $website, ?CodeDetail $status, ?array $extra, User $actor, UploadedFile $document = null, string $Remarks = null): self
    {
        return self::createFromParty(
            party: parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor),
            user: $actor, document: $document, Remarks: $Remarks
        );
    }

    /*   public static function create(
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
       }*/

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
