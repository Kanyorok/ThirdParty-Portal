<?php

namespace App\Services\Insurance;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Finance\FinanceRole;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Services\ThirdParties\ThirdPartiesService;
use App\Services\ThirdParties\ThirdPartyService;
use DateTime;
use Illuminate\Support\Facades\DB;

class BancassuranceCustomersService extends ThirdPartiesService
{
    public function __construct(public BancassuranceCustomer $customer)
    {
        parent::__construct($customer->thirdParty);
    }

    public static function createFromParty(ThirdParties $party, ?BancAssuranceReferral $Referral, DateTime $DateOfBirth, CodeDetail $Gender, CodeDetail $MaritalStatus, CodeDetail $Occupation, User|ThirdPartyUser $user): self
    {
        $auditId = ($user instanceof User) ? $user->Id : SystemHelper::user()->Id;

        $customer = BancassuranceCustomer::create([
            'ThirdPartyId' => $party->Id,
            'ReferralID' => $Referral->Id ?? null,
            'DateOfBirth' => $DateOfBirth,
            'Gender' => $Gender->ID,
            'MaritalStatus' => $MaritalStatus->ID,
            'Occupation' => $Occupation->ID,
            'CreatedBy' => $auditId,
            'ModifiedBy' => $auditId,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($customer)
            ->event('create')
            ->log("Added Customer profile for {$party->ThirdPartyName}");

        $service = new self($customer);
        $service->addType(
            type: self::getType(),
            partyType: BancassuranceCustomer::class,
            partyId: $customer->Id,
            actor: $user
        );

        return $service;
    }

    public static function updateFromParty(ThirdParties $party, User|ThirdPartyUser $actor, array $data = []): void
    {
        $customer = BancassuranceCustomer::where('ThirdPartyId', $party->Id)->first();

        if ($customer) {
            $auditId = ($actor instanceof User) ? $actor->Id : SystemHelper::user()->Id;

            $dob = $data['user_DateOfBirth'] ?? $customer->DateOfBirth;
            if ($dob && !($dob instanceof DateTime)) {
                $dob = new DateTime($dob);
            }

            $customer->update([
                'DateOfBirth' => $dob,
                'Gender' => $data['user_Gender_model']?->ID ?? $data['user_Gender'] ?? $customer->Gender,
                'MaritalStatus' => $data['user_MaritalStatus_model']?->ID ?? $data['user_MaritalStatus'] ?? $customer->MaritalStatus,
                'Occupation' => $data['user_Occupation_model']?->ID ?? $data['user_Occupation'] ?? $customer->Occupation,
                'ModifiedBy' => $auditId,
            ]);

            activity()
                ->causedBy($actor)
                ->performedOn($customer)
                ->event('update')
                ->log("Updated Customer profile for {$party->ThirdPartyName}");
        }
    }

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
        return DB::transaction(function () use ($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor, $data) {

            $party = parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor, $data);

            $dob = $data['user_DateOfBirth'] ?? null;
            $gender = $data['user_Gender_model'] ?? null;
            $marital = $data['user_MaritalStatus_model'] ?? null;
            $occ = $data['user_Occupation_model'] ?? null;

            if (!$dob || !$gender || !$marital || !$occ) {
                throw new \InvalidArgumentException('Missing required customer profile data in $data array.');
            }

            self::createFromParty(
                $party,
                $data['Referral'] ?? null,
                $dob instanceof DateTime ? $dob : new DateTime($dob),
                $gender,
                $marital,
                $occ,
                $actor
            );

            return $party;
        });
    }

    public static function getType(): ThirdPartyType
    {
        return ThirdPartyType::query()->withTrashed()->where('Code', ThirdPartyService::TypeCustomer)->firstOr(function () {
            $role = FinanceRole::query()->first();
            if (!$role instanceof FinanceRole) {
                throw new \RuntimeException("No finance roles found " . __CLASS__);
            }
            $actor = SystemHelper::user();
            return ThirdPartyType::create([
                'FinanceRole' => $role->FinanceRoleID,
                'Code' => ThirdPartyService::TypeCustomer,
                'Description' => 'Customer',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);
        });
    }
}
