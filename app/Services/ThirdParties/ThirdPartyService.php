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
        string   $name,
        ?string $tradingName,
        CodeDetail $businessType,
        string $registrationNumber,
        string $taxPIN,
        ?string $vatNumber,
        Locality $locationID,
        ?string  $physicalAddress,
        ?string $email,
        ?string $phone,
        ?string $website,
        ?CodeDetail $status,
        ?array $extra,
        User|ThirdPartyUser $actor,
        array|string $types = null,
        DateTime $CustomerDateOfBirth = null,
        CodeDetail $CustomerGender = null,
        CodeDetail $CustomerMaritalStatus = null,
        CodeDetail $CustomerOccupation = null,
        string   $Tenant_Remarks = null
    ): self {
        $partyTypes = self::getTypes($types);
        
        // Create the parent ThirdParty and check if it was created successfully
        $parentParty = parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor);
        
        if (!$parentParty) {
            throw new ErroredException('Failed to create ThirdParty record');
        }
        
        $partyService = new self($parentParty);

        foreach ($partyTypes as $type) {
            if ($type->Code === 'CU' && ($CustomerDateOfBirth === null || $CustomerGender === null || $CustomerMaritalStatus === null || $CustomerOccupation === null)) {
                throw new ErroredException('DateOfBirth, Gender, MaritalStatus and Occupation are required for Customer');
            }
            
            match ($type->Code) {
                self::TypeTenant => $partyService->addTenant($actor, $Tenant_Remarks),
                self::TypeSupplier => $partyService->addSupplier($actor),
                self::TypeCustomer => $partyService->addCustomer(Referral: null, DateOfBirth: $CustomerDateOfBirth, Gender: $CustomerGender, MaritalStatus: $CustomerMaritalStatus, Occupation: $CustomerOccupation, actor: $actor),
                default => throw new ErroredException('Invalid party type'),
            };
        }
        
        return $partyService;
    }

    public static function update(
        ThirdParties $party,
        string   $name,
        ?string $tradingName,
        CodeDetail $businessType,
        string $registrationNumber,
        string $taxPIN,
        ?string $vatNumber,
        Locality $locationID,
        ?string  $physicalAddress,
        ?string $email,
        ?string $phone,
        ?string $website,
        ?CodeDetail $status,
        ?array $extra,
        User|ThirdPartyUser $actor,
        array|string $types = null,
        DateTime $CustomerDateOfBirth = null,
        CodeDetail $CustomerGender = null,
        CodeDetail $CustomerMaritalStatus = null,
        CodeDetail $CustomerOccupation = null,
        string   $Tenant_Remarks = null
    ): self {
        // Check if the party exists before proceeding
        if (!$party) {
            throw new ErroredException('ThirdParty not found');
        }
        
        $party->update([
            'ThirdPartyName' => $name,
            'TradingName' => $tradingName,
            'BusinessType' => $businessType->ID,
            'RegistrationNumber' => $registrationNumber,
            'TaxPIN' => $taxPIN,
            'VATNumber' => $vatNumber,
            'CountryId' => $locationID->CountryId,
            'LocationId' => $locationID->ID,
            'PhysicalAddress' => $physicalAddress,
            'Email' => $email,
            'Phone' => $phone,
            'Website' => $website,
            'Status' => $status?->ID ?? $party->Status,
            'Extra' => $extra,
            'ModifiedBy' => $actor->Id,
        ]);

        $partyService = new self($party);

        if ($types) {
            $partyTypes = self::getTypes($types);
            foreach ($partyTypes as $type) {
                if ($type->Code === 'CU' && ($CustomerDateOfBirth === null || $CustomerGender === null || $CustomerMaritalStatus === null || $CustomerOccupation === null)) {
                    throw new ErroredException('DateOfBirth, Gender, MaritalStatus and Occupation are required for Customer');
                }

                // Check if type already exists to avoid duplication
                $exists = $party->types()->where('TypeId', $type->TypeId)->exists();

                if (!$exists) {
                    match ($type->Code) {
                        self::TypeTenant => $partyService->addTenant($actor, $Tenant_Remarks),
                        self::TypeSupplier => $partyService->addSupplier($actor),
                        self::TypeCustomer => $partyService->addCustomer(Referral: null, DateOfBirth: $CustomerDateOfBirth, Gender: $CustomerGender, MaritalStatus: $CustomerMaritalStatus, Occupation: $CustomerOccupation, actor: $actor),
                        default => throw new ErroredException('Invalid party type'),
                    };
                }
            }
        }

        return $partyService;
    }

    /**
     * @throws ErroredException
     */
    public function addTenant(User|ThirdPartyUser $actor, ?string $Remarks): PropertyNewTenantService
    {
        return PropertyNewTenantService::createFromParty($this->party, $actor, Remarks: $Remarks);
    }

    public function addSupplier(User|ThirdPartyUser $actor): SupplierService
    {
        return SupplierService::createFromParty($this->party, $actor);
    }

    public function addCustomer(?BancAssuranceReferral $Referral, DateTime $DateOfBirth, CodeDetail $Gender, CodeDetail $MaritalStatus, CodeDetail $Occupation, User|ThirdPartyUser $actor): BancassuranceCustomersService
    {
        return BancassuranceCustomersService::createFromParty($this->party, Referral: $Referral, DateOfBirth: $DateOfBirth, Gender: $Gender, MaritalStatus: $MaritalStatus, Occupation: $Occupation, user: $actor);
    }

    public static function getType(): ThirdPartyType
    {
        throw new RuntimeException('Not implemented');
    }
}