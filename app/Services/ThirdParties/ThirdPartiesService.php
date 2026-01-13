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
use Illuminate\Support\Facades\Hash;

abstract class ThirdPartiesService
{
    public function __construct(public ThirdParties $party) {}

    abstract public static function getType(): ThirdPartyType;

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

    public function addUser(string $firstName, string $lastName, string $email, string $phone, CodeDetail $gender, User|ThirdPartyUser $actor, ?string $password = null, bool $sendVerification = true): static
    {
        $auditId = ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id;

        $user = ThirdPartyUser::create([
            'FirstName' => $firstName,
            'LastName' => $lastName,
            'Email' => $email,
            'Phone' => $phone,
            'Gender' => $gender->getAttribute('ID') ?? $gender->ID,
            'ThirdPartyId' => $this->party->Id,
            'Password' => $password ? \Illuminate\Support\Facades\Hash::make($password) : 'NON SET',
            'IsActive' => $password ? true : false,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        if ($password && $sendVerification) {
            $user->sendEmailVerificationNotification();
        }

        activity()
            ->causedBy($actor)
            ->performedOn($user)
            ->event('create')
            ->log("Created user {$user->FirstName} {$user->LastName} for thirdparty {$this->party->ThirdPartyName}");

        return $this;
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
        array $data = []
    ): ThirdParties {
        $auditId = ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id;

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
            'CreatedBy' => $auditId,
            'ModifiedBy' => $auditId,
        ]);

        activity()
            ->causedBy($actor)
            ->performedOn($party)
            ->withProperties(['thirdParty' => $party])
            ->log('Created thirdparty ' . $name);

        return $party;
    }

    public static function codeDetail(ThirdPartyStatusEnum $status, bool $create = false): CodeDetail
    {
        $code = CodeDetail::query()
            ->where('CodeID', 'ThirdPartyStatus')
            ->where('Value', $status->value)
            ->first();

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
                'IsActive' => true,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);
        }
        throw new ErroredException('Invalid Status, not set and could not create');
    }

    public function setLogo(\Illuminate\Http\UploadedFile $image, User|ThirdPartyUser $actor): self
    {
        $this->party->setImage($image, $actor, 'ImageId');
        return $this;
    }

    public function addBank(Currency $currency, string $accountNumber, BankBranch $branch, User|ThirdPartyUser $actor, ?array $extra = null): static
    {
        $auditId = ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id;

        $bank = ThirdPartiesBankDetails::create([
            'ThirdPartyId' => $this->party->Id,
            'CurrencyId' => $currency->Id,
            'AccountNumber' => $accountNumber,
            'BranchID' => $branch->BranchID,
            'Extra' => $extra,
            'CreatedBy' => $auditId,
            'ModifiedBy' => $auditId,
        ]);

        activity()
            ->causedBy($actor)
            ->performedOn($bank)
            ->event('create')
            ->log("Created bank {$bank->AccountNumber} for thirdparty {$this->party->ThirdPartyName}");

        return $this;
    }

    final protected function addType(ThirdPartyType $type, string $partyType, string|int $partyId, User|ThirdPartyUser $actor): static
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