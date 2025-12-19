<?php

namespace App\Services\ThirdParties;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\ThirdParty\ThirdPartyType;
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
        string   $name, ?string $tradingName, CodeDetail $businessType, string $registrationNumber, string $taxPIN, ?string $vatNumber, Locality $locationID,
        ?string  $physicalAddress, ?string $email, ?string $phone, ?string $website, ?CodeDetail $status, ?array $extra, User $actor, array|string $types = null,
        DateTime $CustomerDateOfBirth = null, CodeDetail $CustomerGender = null, CodeDetail $CustomerMaritalStatus = null, CodeDetail $CustomerOccupation = null,
        string   $Tenant_Remarks = null
    ): self
    {
        $partyTypes = self::getTypes($types);
        $partyService = new self(parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor));

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

    /**
     * @throws ErroredException
     */
    public function addTenant(User $actor, ?string $Remarks): PropertyNewTenantService
    {
        return PropertyNewTenantService::createFromParty($this->party, $actor, Remarks: $Remarks);
    }

    public function addSupplier(User $actor): SupplierService
    {
        return SupplierService::createFromParty($this->party, $actor);
    }

    public function addCustomer(?BancAssuranceReferral $Referral, DateTime $DateOfBirth, CodeDetail $Gender, CodeDetail $MaritalStatus, CodeDetail $Occupation, User $actor): BancassuranceCustomersService
    {
        return BancassuranceCustomersService::createFromParty($this->party, Referral: $Referral, DateOfBirth: $DateOfBirth, Gender: $Gender, MaritalStatus: $MaritalStatus, Occupation: $Occupation, user: $actor);
    }

    public static function getType(): ThirdPartyType
    {
        throw new RuntimeException('Not implemented');
    }
}
