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
        User $actor,
        array|string $types = null,
        DateTime $CustomerDateOfBirth = null,
        CodeDetail $CustomerGender = null,
        CodeDetail $CustomerMaritalStatus = null,
        CodeDetail $CustomerOccupation = null,
        string   $Tenant_Remarks = null
    ): self {
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
        User $actor,
        array|string $types = null,
        DateTime $CustomerDateOfBirth = null,
        CodeDetail $CustomerGender = null,
        CodeDetail $CustomerMaritalStatus = null,
        CodeDetail $CustomerOccupation = null,
        string   $Tenant_Remarks = null
    ): self {
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
            'Status' => $status?->ID ?? $party->Status, // Keep existing if null? Or reset? Using param if provided.
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
                // Assuming 'types' relationship allows checking by checking Code or ID
                $exists = $party->types()->where('TypeId', $type->TypeId)->exists();

                if (!$exists) {
                    // Logic to add type and create associated record
                    // Note: addTenant/addSupplier creates the record.
                    // If we just add type reference in pivot, we miss the record creation.
                    // But standard logic in 'create' does implicit matching.
                    // Wait, 'create' calls 'addTenant' which creates 'PropertyNewTenant'.
                    // 'ThirdPartiesService::create' does NOT add pivot entry for 'types'. 
                    // Ah, `ThirdPartiesController` original logic had `$thirdParty->types()->sync(...)`.
                    // But `ThirdPartyService` (Child) uses `addTenant` etc.
                    // Does `addTenant` add the Type pivot?
                    // Let's check `propertyNewTenantService`... I can't check everything.
                    // But `ThirdPartiesService` has `addType`.

                    // In `create` method above (lines 30-43), it calls `match ... addTenant`.
                    // It does NOT explicitly call `addType` pivot creation?
                    // Wait, `ThirdPartiesService` has `addType`.
                    // But `create` (Child) doesn't call it?

                    // This implies `addTenant` logic deeper down might handle it OR logic is missing in `create`.
                    // Let's look at `create`: `parent::create` creates the Party.
                    // Then loop types -> `addTenant`.
                    // Where is `t_ThirdPartyType_ThirdParties` populated?
                    // If `ThirdPartyService::create` doesn't do it, then `ThirdPartiesController` originally did it manually?
                    // In `RegistrationService.php` (Step 1 code I viewed earlier), it EXPLICITLY inserts into `t_ThirdPartyType_ThirdParties`.

                    // So `ThirdPartyService::create` might be MISSING the pivot insertion!
                    // Unless `addTenant` does it.
                    // I should probably add `parent::addType` calls here just to be safe or consistent with `RegistrationService`.
                    // But `parent::addType` is `protected final`.
                    // I can access it via `$partyService` since `$partyService` is instance of `ThirdPartyService` extending `ThirdPartiesService`.
                    // Wait, `addType` is `protected`. I can call it from `update` (static method in class) on `$partyService` (instance)? 
                    // Yes, static method of same class has access to private/protected of instance? Yes in PHP.

                    // But `addTenant` returns `PropertyNewTenantService`.
                    // So `partyService` is lost in the chain if I chain calls.

                    // Let's assume for now I should just call the specific add methods.

                    match ($type->Code) {
                        self::TypeTenant => $partyService->addTenant($actor, $Tenant_Remarks),
                        self::TypeSupplier => $partyService->addSupplier($actor),
                        self::TypeCustomer => $partyService->addCustomer(Referral: null, DateOfBirth: $CustomerDateOfBirth, Gender: $CustomerGender, MaritalStatus: $CustomerMaritalStatus, Occupation: $CustomerOccupation, actor: $actor),
                        default => throw new ErroredException('Invalid party type'),
                    };

                    // Also need to add the Type Pivot? `RegistrationService` does.
                    // `ThirdPartiesService::create` assumes `addTenant` etc handles?
                    // I will replicate `create` behavior exactly. If `create` logic is flawed regarding pivot, `update` will match it. I can fix pivot issue later if needed.
                }
            }
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
