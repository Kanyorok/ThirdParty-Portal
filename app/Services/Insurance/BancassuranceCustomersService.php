<?php

namespace App\Services\Insurance;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Locality;
use App\Models\Finance\FinanceRole;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\ThirdParties\ThirdPartiesService;
use App\Services\ThirdParties\ThirdPartyService;
use DateTime;

class BancassuranceCustomersService extends ThirdPartiesService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceCustomer $customer)
    {
        parent::__construct($customer->thirdParty);
    }

    public static function createFromParty(ThirdParties $party, ?BancAssuranceReferral $Referral, DateTime $DateOfBirth, CodeDetail $Gender, CodeDetail $MaritalStatus, CodeDetail $Occupation, User|ThirdPartyUser $user): self
    {
        $customer = BancassuranceCustomer::create([
            'ThirdPartyId' => $party->Id,
            'ReferralID' => $Referral->Id ?? null,
            'DateOfBirth' => $DateOfBirth,
            'Gender' => $Gender->ID,
            'MaritalStatus' => $MaritalStatus->ID,
            'Occupation' => $Occupation->ID,
            'Occupation' => $Occupation->ID,
            'CreatedBy' => ($user instanceof User) ? $user->Id : SystemHelper::user()->Id,
            'ModifiedBy' => ($user instanceof User) ? $user->Id : SystemHelper::user()->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($customer)->event('create')->log("Added Customer {$customer->Id}.");
        $service = new self($customer);
        $service->addType(self::getType(), BancassuranceCustomer::getPrimaryKey(), $customer->Id, $user);

        return $service;
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
        ?BancAssuranceReferral $Referral = null,
        DateTime $DateOfBirth = null,
        CodeDetail $Gender = null,
        CodeDetail $MaritalStatus = null,
        CodeDetail $Occupation = null
    ): self {
        if ($DateOfBirth === null) {
            throw new \InvalidArgumentException('DateOfBirth is required parameter.');
        }
        if ($Gender === null) {
            throw new \InvalidArgumentException('Gender is required parameter.');
        }
        if ($MaritalStatus === null) {
            throw new \InvalidArgumentException('MaritalStatus is required parameter.');
        }
        if ($Occupation === null) {
            throw new \InvalidArgumentException('Occupation is required parameter.');
        }

        return self::createFromParty(
            party: parent::create($name, $tradingName, $businessType, $registrationNumber, $taxPIN, $vatNumber, $locationID, $physicalAddress, $email, $phone, $website, $status, $extra, $actor),
            Referral: $Referral,
            DateOfBirth: $DateOfBirth,
            Gender: $Gender,
            MaritalStatus: $MaritalStatus,
            Occupation: $Occupation,
            user: $actor
        );
    }

    /*  public static function create(
          ThirdParties $ThirdPartyId,
          ?BancAssuranceReferral $ReferralID = null,
          DateTime              $DateOfBirth,
          CodeDetail            $Gender,
          CodeDetail            $MaritalStatus,
          CodeDetail            $Occupation,
          User                  $user
      ): self
      {
          $customer = BancassuranceCustomer::create([
              'ThirdPartyId' => $ThirdPartyId->Id,
              'ReferralID' => $ReferralID->Id ?? null,
              'DateOfBirth' => $DateOfBirth,
              'Gender' => $Gender->ID,
              'MaritalStatus' => $MaritalStatus->ID,
              'Occupation' => $Occupation->ID,
              'CreatedBy' => $user->Id,
              'ModifiedBy' => $user->Id,
          ]);

          activity()->causedBy($user->Id)->performedOn($customer)->event('create')->log("Added Customer {$customer->Id}.");
          return new self($customer);
      }*/
    public static function getType(): ThirdPartyType
    {
        return ThirdPartyType::query()->withTrashed()->where('Code', ThirdPartyService::TypeCustomer)->firstOr(function () {
            $role = FinanceRole::query()->first(); // todo fix your Finance role
            if ($role instanceof FinanceRole === false) {
                throw new \RuntimeException("No finance roles found " . __CLASS__);
            }
            $actor = SystemHelper::user();

            return ThirdPartyType::create([
                'FinanceRole' => $role->FinanceRoleID,
                'Code' => ThirdPartyService::TypeCustomer,
                'Description' => 'Tenant',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
            ]);
        });
    }
}
