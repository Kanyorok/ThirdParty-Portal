<?php

namespace App\Services\ThirdParties;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Finance\FinanceRole;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use Illuminate\Http\UploadedFile;

class TenantService extends ThirdPartiesService
{
    public function __construct(public PropertyNewTenant $tenant)
    {
        if (!$tenant->relationLoaded('thirdParty')) {
            $tenant->load('thirdParty');
        }

        parent::__construct($tenant->thirdParty ?? null);
    }

    public static function getType(): ThirdPartyType
    {
        return ThirdPartyType::query()->withTrashed()->where('Code', ThirdPartyService::TypeTenant)->firstOr(function () {
            $role = FinanceRole::query()->first();
            if ($role instanceof FinanceRole === false) {
                throw new \RuntimeException("No finance roles found " . __CLASS__);
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

    public static function create(
        string  $name,
        ?string $tradingName,
        CodeDetail $businessType,
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
        ?int $tenantType = null,
        ?string $remarks = null
    ): self {
        return self::createFromParty(
            party: parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor),
            actor: $actor,
            tenantType: $tenantType,
            remarks: $remarks
        );
    }

    public static function createFromParty(
        ThirdParties $party,
        User|ThirdPartyUser $actor,
        ?int $tenantType = null,
        ?string $remarks = null,
        UploadedFile $document = null
    ): self {
        // Default to Individual tenant type (ID: 80) if not specified
        if ($tenantType === null) {
            $tenantType = 80; // Individual
        }

        // Default remarks if not provided
        if ($remarks === null) {
            $remarks = 'Tenant profile created via portal';
        }

        $tenant = PropertyNewTenant::create([
            'ThirdPartyId' => $party->Id,
            'TenantType' => $tenantType,
            'Remarks' => $remarks,
            'IsActive' => true,
            'CreatedBy' => ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id,
            'ModifiedBy' => ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id,
        ]);

        activity()->causedBy($actor)->performedOn($tenant)->event('create')->log("Added Tenant to thirdparty {$party->ThirdPartyName}.");

        $service = new self($tenant);
        $service->addType(self::getType(), 'ThirdPartyId', $party->Id, $actor);

        return $service;
    }
}
