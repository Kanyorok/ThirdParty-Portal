<?php

namespace App\Services\Insurance;

use App\Enums\Insurance\InsuranceReferralStatus;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancAssuranceReferral;

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
        User $ReferredBy,
        string $ReferralDate,
        ?CodeDetail $InsuranceProductId,
        CodeDetail $PreferredInsurerId,
        ?string $Remarks,
        InsuranceReferralStatus $Status,
        ?User $AssignedTo,
        User $user
    ): self {
            $referral = BancAssuranceReferral::create([
                'ClientName' => $ClientName,
                'ClientIDNumber' => $ClientIDNumber,
                'ClientPhone' => $ClientPhone,
                'ClientEmail' => $ClientEmail,
                'ReferredBy' => $ReferredBy->Id,
                'ReferralDate' => $ReferralDate,
                'InsuranceProductId' => $InsuranceProductId->ID ?? null,
                'PreferredInsurerId' => $PreferredInsurerId->ID,
                'Remarks' => $Remarks,
                'Status' => $Status->value,
                'AssignedTo' => $AssignedTo,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            activity()
                ->causedBy($user->Id)
                ->performedOn($referral)
                ->event('create')
                ->log("Added Bank Assurance Referral {$referral->Id}.");

            return new self($referral);
    }
}