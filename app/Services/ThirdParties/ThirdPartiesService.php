<?php

namespace App\Services\ThirdParties;

use App\Enums\ThirdParty\ThirdPartyStatusEnum;
use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Core\Locality;
use App\Models\Finance\BankBranch;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartiesBankDetails;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\ThirdParty\ThirdPartyTypeTypes;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Support\Collection;


abstract class ThirdPartiesService
{
    public function __construct(public ThirdParties $party) {}

    abstract public static function getType(): ThirdPartyType;


    /**
     * @throws ErroredException
     */
    public static function getTypes(array|null|string $types): Collection
    {
        if ($types === null) {
            throw new ErroredException('Invalid | no types provided');
        }
        $types = is_array($types) ? $types : explode(',', $types);
        $partyTypes = ThirdPartyType::whereIn('Code', $types)->get();
        if ($partyTypes->isEmpty()) {
            throw new ErroredException('Invalid | no types provided');
        }
        return $partyTypes;
    }

    public function addUser(string $firstName, string $lastName, string $email, string $phone, CodeDetail $gender, User $actor): static
    {
        $user = ThirdPartyUser::create([
            'FirstName' => $firstName,
            'LastName' => $lastName,
            'Email' => $email,
            'Phone' => $phone,
            'Gender' => $gender->ID,
            'ThirdPartyId' => $this->party->Id,
            'Password' => 'NON SET',
            'IsActive' => true,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($user)->event('create')->log("Created user {$user->FirstName} {$user->LastName} to thirdparty {$this->party->ThirdPartyName}");
        return $this;
    }

    /**
     * @throws ErroredException
     */
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
        User $actor
    ): mixed {
        $party = ThirdParties::create([
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
            'Status' => $status?->getKey() ?? self::codeDetail(ThirdPartyStatusEnum::Active)->getKey(),
            'Extra' => $extra,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->on($party)->withProperties(['thirdParty' => $party])->log('Created thirdparty ' . $name);

        return $party;
    }

    /**
     * @throws ErroredException
     */
    public static function codeDetail(ThirdPartyStatusEnum $status, bool $create = false): CodeDetail
    {
        $code = CodeDetail::query()->where('CodeID', 'ThirdPartyStatus')->where('Value', $status->value)->first();
        if ($code instanceof CodeDetail) {
            return $code;
        }
        if ($create) {
            $actor = SystemHelper::user();
            return CodeDetail::create([
                'CodeID' => 'ThirdPartyStatus',
                'Value' => $status->value,
                'Description' => $status->label(),
                'DisplayOrder' => CodeDetail::query()->where('CodeID', 'ThirdPartyStatus')->count() + 1,
                'IsActive' => false,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);
        }
        throw new ErroredException('Invalid Status, not set and could not create');
    }

    public function setLogo(\Illuminate\Http\UploadedFile $image, User $actor): self
    {
        $this->party->setImage($image, $actor, 'ImageId');
        return $this;
    }

    public function addBank(Currency $currency, string $accountNumber, BankBranch $branch, User $actor, ?array $extra = null): static
    {
        $bank = ThirdPartiesBankDetails::create([
            'ThirdPartyId' => $this->party->Id,
            'CurrencyId' => $currency->Id,
            'AccountNumber' => $accountNumber,
            'BranchID' => $branch->BranchID,
            'Extra' => $extra,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($bank)->event('create')->log("Created bank {$bank->AccountNumber} to thirdparty {$this->party->ThirdPartyName}");

        return $this;
    }

    final protected function addType(ThirdPartyType $type, string $partyType, string|int $partyId, User $actor): static
    {
        ThirdPartyTypeTypes::create([
            'TypeId' => $type->TypeId,
            'ThirdPartyId' => $this->party->Id,
            'PartyType' => $partyType,
            'PartyID' => $partyId,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);
        return $this;
    }
}
