<?php

namespace App\Services\ThirdParties;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\ThirdParty\ThirdParties;
use App\Services\Insurance\BancassuranceCustomersService;
use App\Services\Property\TenantAndLease\PropertyNewTenantService;
use DateTime;
use RuntimeException;

class ThirdPartyService extends ThirdPartiesService
{
    public const string TypeTenant = 'TN';
    public const string TypeSupplier = 'SU';
    public const string TypeCustomer = 'CU';

    public static function create(
        string $name,
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
        array $data = []
    ): ThirdParties {
        $types = $data['types'] ?? null;
        $partyTypes = self::getTypes($types);

        $parentParty = parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor);

        if (!$parentParty) {
            throw new ErroredException('Failed to create ThirdParty record');
        }

        $partyService = new self($parentParty);

        foreach ($partyTypes as $type) {
            match ($type->Code) {
                self::TypeTenant => $partyService->addTenant($actor, $data['tenant_Remarks'] ?? null),

                self::TypeSupplier => $partyService->addSupplier($actor, $data),

                self::TypeCustomer => $partyService->addCustomer(
                    Referral: $data['Referral'] ?? null,
                    DateOfBirth: $data['customer_DateOfBirth'] ?? null,
                    Gender: $data['customer_Gender_model'] ?? null,
                    MaritalStatus: $data['customer_MaritalStatus_model'] ?? null,
                    Occupation: $data['customer_Occupation_model'] ?? null,
                    actor: $actor
                ),

                default => throw new ErroredException("Handler for type {$type->Code} not implemented"),
            };
        }

        return $parentParty;
    }

    public static function update(
        ThirdParties $party,
        string $name,
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
        array $data = []
    ): ThirdParties {
        $party->update([
            'ThirdPartyName' => $name,
            'TradingName' => $tradingName,
            'BusinessType' => $businessType->getKey(),
            'RegistrationNumber' => $registrationNumber,
            'TaxPIN' => $taxPIN,
            'VATNumber' => $vatNumber,
            'CountryId' => $locationID->CountryId,
            'LocationId' => $locationID->getKey(),
            'PhysicalAddress' => $physicalAddress,
            'Email' => $email,
            'Phone' => $phone,
            'Website' => $website,
            'Status' => $status?->getKey() ?? $party->Status,
            'Extra' => $extra,
            'ModifiedBy' => $actor->Id,
        ]);

        $partyService = new self($party);
        $types = $data['types'] ?? null;

        if ($types) {
            $partyTypes = self::getTypes($types);
            foreach ($partyTypes as $type) {
                if (!$party->types()->where('TypeId', $type->TypeId)->exists()) {
                    match ($type->Code) {
                        self::TypeTenant => $partyService->addTenant($actor, $data['tenant_Remarks'] ?? null),
                        self::TypeSupplier => $partyService->addSupplier($actor, $data),
                        self::TypeCustomer => $partyService->addCustomer(
                            Referral: null,
                            DateOfBirth: $data['customer_DateOfBirth'],
                            Gender: $data['customer_Gender_model'],
                            MaritalStatus: $data['customer_MaritalStatus_model'],
                            Occupation: $data['customer_Occupation_model'],
                            actor: $actor
                        ),
                        default => null
                    };
                }
            }
        }

        return $party;
    }

    public function addTenant(User|ThirdPartyUser $actor, ?string $Remarks): PropertyNewTenantService
    {
        return PropertyNewTenantService::createFromParty($this->party, $actor, Remarks: $Remarks);
    }

    public function addSupplier(User|ThirdPartyUser $actor, array $data = []): SupplierService
    {
        return SupplierService::createFromParty($this->party, $actor, $data);
    }

    public function addCustomer(
        ?BancAssuranceReferral $Referral,
        ?DateTime $DateOfBirth,
        ?CodeDetail $Gender,
        ?CodeDetail $MaritalStatus,
        ?CodeDetail $Occupation,
        User|ThirdPartyUser $actor
    ): BancassuranceCustomersService {
        if (!$DateOfBirth || !$Gender || !$MaritalStatus || !$Occupation) {
            throw new ErroredException('Missing required details for Customer registration');
        }
        return BancassuranceCustomersService::createFromParty(
            $this->party,
            Referral: $Referral,
            DateOfBirth: $DateOfBirth,
            Gender: $Gender,
            MaritalStatus: $MaritalStatus,
            Occupation: $Occupation,
            user: $actor
        );
    }

    public static function getType(): ThirdPartyType
    {
        throw new RuntimeException('Generic ThirdPartyService does not have a single type. Use addType() on instances.');
    }
}
