<?php

namespace App\Services\ThirdParties;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Finance\FinanceRole;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TenantService extends ThirdPartiesService
{
    public function __construct(public PropertyNewTenant $tenant)
    {
        if (! $tenant->relationLoaded('thirdParty')) {
            $tenant->load('thirdParty');
        }

        parent::__construct($tenant->thirdParty);
    }

    public static function getType(): ThirdPartyType
    {
        return ThirdPartyType::query()->withTrashed()->where('Code', ThirdPartyService::TypeTenant)->firstOr(function () {
            $role = FinanceRole::query()->first();
            if (! $role instanceof FinanceRole) {
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

            self::createFromParty(
                $party,
                $actor,
                $data['tenant_type'] ?? $data['tenantType'] ?? null,
                $data['user_Remarks'] ?? $data['tenant_remarks'] ?? $data['remarks'] ?? null
            );

            return $party;
        });
    }

    public static function createFromParty(
        ThirdParties $party,
        User|ThirdPartyUser $actor,
        ?int $tenantType = null,
        ?string $remarks = null,
        ?UploadedFile $document = null
    ): self {
        $tenant = PropertyNewTenant::updateOrCreate(
            ['ThirdPartyId' => $party->Id],
            [
                'TenantType' => $tenantType ?? 80,
                'Remarks' => $remarks,
                'IsActive' => true,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]
        );

        activity()->causedBy($actor)->performedOn($tenant)->event('create')->log("Added/Updated Tenant for thirdparty {$party->ThirdPartyName}.");

        $service = new self($tenant);
        $service->addType(
            type: self::getType(),
            partyType: PropertyNewTenant::class,
            partyId: $tenant->getKey(),
            actor: $actor
        );

        return $service;
    }

    public static function updateFromParty(ThirdParties $party, User|ThirdPartyUser $actor, array $data = []): void
    {
        $tenant = PropertyNewTenant::where('ThirdPartyId', $party->Id)->first();

        if ($tenant) {
            $auditId = ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id;

            $tenant->update([
                'TenantType' => $data['tenant_type'] ?? $data['tenantType'] ?? $tenant->TenantType,
                'Remarks' => $data['user_Remarks'] ?? $data['tenant_remarks'] ?? $data['remarks'] ?? $tenant->Remarks,
                'ModifiedBy' => $auditId,
            ]);

            activity()
                ->causedBy($actor)
                ->performedOn($tenant)
                ->event('update')
                ->log("Updated Tenant profile for {$party->ThirdPartyName}");
        }
    }
}
