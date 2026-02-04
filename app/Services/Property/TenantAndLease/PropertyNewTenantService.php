<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Finance\FinanceRole;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\ThirdParties\ThirdPartiesService;
use App\Services\ThirdParties\ThirdPartyService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PropertyNewTenantService extends ThirdPartiesService
{
    public function __construct(public PropertyNewTenant $propertyNewTenant)
    {
        parent::__construct($propertyNewTenant->thirdParty);
    }

    public static function getType(): ThirdPartyType
    {
        return ThirdPartyType::query()->withTrashed()->where('Code', ThirdPartyService::TypeTenant)->firstOr(function () {
            $role = FinanceRole::query()->first();
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

    public static function createFromParty(ThirdParties $party, User|ThirdPartyUser $actor, ?UploadedFile $document = null, ?string $remarks = null, ?int $tenantType = null): self
    {
        $tenant = PropertyNewTenant::create([
            'ThirdPartyId' => $party->Id,
            'TenantType' => $tenantType ?? $party->BusinessType,
            'Remarks' => $remarks,
            'IsActive' => true,
            'CreatedBy' => ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id,
            'ModifiedBy' => ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id,
        ]);

        if ($document instanceof UploadedFile) {
            $tenant->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::TenantMaintenanceView->value],
                $actor
            );
        }

        activity()->causedBy($actor)->performedOn($tenant)->event('create')->log("Added New Tenant {$tenant->Id}.");

        $service = new self($tenant);
        $service->addType(self::getType(), PropertyNewTenant::getPrimaryKey(), $tenant->Id, $actor);

        return $service;
    }

    public static function create(
        string $name,
        ?string $tradingName,
        ?CodeDetail $businessType,
        string $registrationNumber,
        string $taxPIN,
        ?string $vatNumber,
        Locality $locationID,
        ?string $physicalAddress,
        ?string $email,
        ?string $phone,
        ?string $website,
        ?CodeDetail $status,
        ?array $extra,
        User|ThirdPartyUser $actor,
        array $data = []
    ): ThirdParties {
        return DB::transaction(function () use ($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor, $data) {
            $party = parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor, $data);

            $file = (isset($data['document']) && $data['document'] instanceof UploadedFile) ? $data['document'] : null;

            self::createFromParty(
                party: $party,
                actor: $actor,
                document: $file,
                Remarks: $data['tenant_Remarks'] ?? $data['remarks'] ?? $data['Remarks'] ?? null,
                tenantType: $data['tenant_type'] ?? null
            );

            return $party;
        });
    }

    public static function update(
        PropertyNewTenant $propertyNewTenant,
        CodeDetail $TenantType,
        ?string $Remarks = null,
        bool $IsActive,
        User $user,
        UploadedFile $document = null
    ): self {
        $propertyNewTenant->update([
            'TenantType' => $TenantType->ID,
            'Remarks' => $Remarks,
            'IsActive' => $IsActive,
            'ModifiedBy' => $user->Id,
        ]);

        if (! empty($document)) {
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
            ->causedBy($user)
            ->performedOn($propertyNewTenant)
            ->event('update')
            ->log("Updated Tenant {$propertyNewTenant->Id}.");

        return new self($propertyNewTenant);
    }
}
