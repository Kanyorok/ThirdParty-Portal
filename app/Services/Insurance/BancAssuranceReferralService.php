<?php

namespace App\Services\Insurance;

use App\Enums\Insurance\InsuranceReferralStatus;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;
use Carbon\Carbon;

class BancAssuranceReferralService
{
    public BancAssuranceReferral $bancAssuranceReferral;

    public function __construct(BancAssuranceReferral $bancAssuranceReferral)
    {
        $this->bancAssuranceReferral = $bancAssuranceReferral;
    }

    public static function create(
        string $ClientName,
        string $ClientIDNumber,
        string $ClientPhone,
        string $ClientEmail,
        ?User $ReferredBy = null,
        ?Carbon $ReferralDate = null,
        ?InsuranceProduct $InsuranceProductId = null,
        InsuranceProvider $PreferredInsurerId,
        ?string $Remarks = null,
        InsuranceReferralStatus $Status,
        ?User $AssignedTo = null,
        Branch $BranchId,
        User $user
    ): self {
            $referral = BancAssuranceReferral::create([
                'ClientName' => $ClientName,
                'ClientIDNumber' => $ClientIDNumber,
                'ClientPhone' => $ClientPhone,
                'ClientEmail' => $ClientEmail,
                'ReferredBy' => $ReferredBy->Id ?? null,
                'ReferralDate' => $ReferralDate ?? null,
                'InsuranceProductId' => $InsuranceProductId->Id ?? null,
                'PreferredInsurerId' => $PreferredInsurerId->Id,
                'Remarks' => $Remarks,
                'Status' => $Status->value,
                'AssignedTo' => $AssignedTo->Id ?? null,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
                'BranchId' => $BranchId -> Id,
            ]);

            activity()
                ->causedBy($user->Id)
                ->performedOn($referral)
                ->event('create')
                ->log("Added Bank Assurance Referral {$referral->Id}.");

            return new self($referral);
    }

    public static function update(
        BancAssuranceReferral $referralupdate,
        string $ClientName,
        string $ClientIDNumber,
        string $ClientPhone,
        string $ClientEmail,
        ?User $ReferredBy = null,
        ?Carbon $ReferralDate = null,
        ?InsuranceProduct $InsuranceProductId = null,
        InsuranceProvider $PreferredInsurerId,
        ?string $Remarks = null,
        InsuranceReferralStatus $Status,
        ?User $AssignedTo = null,
        Branch $BranchId,
        User $user
    ): self {
            $referralupdate -> update([
                'ClientName' => $ClientName,
                'ClientIDNumber' => $ClientIDNumber,
                'ClientPhone' => $ClientPhone,
                'ClientEmail' => $ClientEmail,
                'ReferredBy' => $ReferredBy->Id ?? null,
                'ReferralDate' => $ReferralDate ?? null,
                'InsuranceProductId' => $InsuranceProductId->Id ?? null,
                'PreferredInsurerId' => $PreferredInsurerId->Id,
                'Remarks' => $Remarks,
                'Status' => $Status->value,
                'AssignedTo' => $AssignedTo->Id ?? null,
                'ModifiedBy' => $user->Id,
                'BranchId' => $BranchId->Id,
            ]);

            activity()
                ->causedBy($user->Id)
                ->performedOn($referralupdate)
                ->event('update')
                ->log("Updated Bank Assurance Referral {$referralupdate->Id}.");

            return new self($referralupdate);
    }
}