<?php

namespace App\Services\ThirdParties;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\Insurance\BancassuranceCustomersService;
use DateTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ThirdPartyService extends ThirdPartiesService
{
    public const TypeTenant = 'TN';
    public const TypeSupplier = 'SU';
    public const TypeCustomer = 'CU';

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
            $types = $data['types'] ?? [];
            $partyTypes = self::getTypes($types);

            $parentParty = parent::create(
                $name,
                $tradingName,
                $businessType,
                $registrationNumber,
                $taxPIN,
                $vatNumber,
                $locationID,
                $physicalAddress,
                $email,
                $phone,
                $website,
                $status,
                $extra,
                $actor
            );

            if (! $parentParty) {
                throw new ErroredException('Failed to create ThirdParty record');
            }

            $partyService = new self($parentParty);

            foreach ($partyTypes as $type) {
                match ($type->Code) {
                    self::TypeTenant => $partyService->addTenant(
                        $actor,
                        $data['user_Remarks'] ?? null,
                        $data['document'] ?? null
                    ),
                    self::TypeSupplier => $partyService->addSupplier($actor, $data),
                    self::TypeCustomer => $partyService->addCustomer(
                        $data['Referral'] ?? null,
                        $data['user_DateOfBirth'] ?? null,
                        $data['user_Gender'] ?? null,
                        $data['user_MaritalStatus'] ?? null,
                        $data['user_Occupation'] ?? null,
                        $actor
                    ),
                    default => throw new ErroredException("Handler for type {$type->Code} not implemented"),
                };
            }

            return $parentParty;
        });
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
        return DB::transaction(function () use ($party, $name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor, $data) {
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
            $types = $data['types'] ?? null;

            if ($types) {
                $partyTypes = self::getTypes($types);
                foreach ($partyTypes as $type) {
                    if (! $party->types()->where('t_ThirdPartyTypes.TypeId', $type->TypeId)->exists()) {
                        match ($type->Code) {
                            self::TypeTenant => $partyService->addTenant($actor, $data['user_Remarks'] ?? null, $data['document'] ?? null),
                            self::TypeSupplier => $partyService->addSupplier($actor, $data),
                            self::TypeCustomer => $partyService->addCustomer(null, $data['user_DateOfBirth'] ?? null, $data['user_Gender'] ?? null, $data['user_MaritalStatus'] ?? null, $data['user_Occupation'] ?? null, $actor),
                            default => null
                        };
                    } else {
                        $partyService->syncTypeDetails($type->Code, $data, $actor);
                    }
                }
            }

            return $party;
        });
    }

    protected function syncTypeDetails(string $code, array $data, User|ThirdPartyUser $actor): void
    {
        match ($code) {
            self::TypeSupplier => SupplierService::updateFromParty($this->party, $actor, $data),
            self::TypeCustomer => BancassuranceCustomersService::updateFromParty($this->party, $actor, $data),
            self::TypeTenant => TenantService::updateFromParty($this->party, $actor, $data),
            default => null,
        };
    }

    public static function updateFromParty(ThirdParties $party, User|ThirdPartyUser $actor, array $data): void
    {
        $instance = new self($party);
        $types = $data['types'] ?? [];

        foreach ($types as $code) {
            $instance->syncTypeDetails($code, $data, $actor);
        }
    }

    public function addTenant(User|ThirdPartyUser $actor, ?string $Remarks, ?UploadedFile $document = null): TenantService
    {
        return TenantService::createFromParty(
            party: $this->party,
            actor: $actor,
            document: $document,
            tenantType: $data['tenant_type'] ?? 80,
            remarks: $data['remarks'] ?? 'Portal registration'
        );
    }

    public function addSupplier(User|ThirdPartyUser $actor, array $data = []): SupplierService
    {
        return SupplierService::createFromParty($this->party, $actor, $data);
    }

    public function addCustomer(
        ?BancAssuranceReferral $Referral,
        mixed $DateOfBirth,
        mixed $Gender,
        mixed $MaritalStatus,
        mixed $Occupation,
        User|ThirdPartyUser $actor
    ): BancassuranceCustomersService {
        if (! $DateOfBirth || ! $Gender || ! $MaritalStatus || ! $Occupation) {
            throw new ErroredException('Missing required details for Customer registration');
        }

        $dob = $DateOfBirth instanceof DateTime ? $DateOfBirth : new DateTime($DateOfBirth);

        return BancassuranceCustomersService::createFromParty(
            $this->party,
            $Referral,
            $dob,
            $Gender,
            $MaritalStatus,
            $Occupation,
            $actor
        );
    }

    public static function getType(): ThirdPartyType
    {
        throw new RuntimeException('Generic ThirdPartyService does not have a single type.');
    }
}
