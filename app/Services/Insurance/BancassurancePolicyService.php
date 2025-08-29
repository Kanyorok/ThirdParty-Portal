<?php

namespace App\Services\Insurance;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Insurance\InsurancePolicyStatus;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancassurancePolicy;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class BancassurancePolicyService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassurancePolicy $bancassurancePolicy)
    {
        //
    }

    public static function create(
        BancassuranceCustomer $CustomerID,
        InsuranceProduct $ProductID,
        ?InsuranceProvider $InsurerID,
        float $SumAssured,
        float $PremiumAmount,
        Carbon $PolicyStartDate,
        Carbon $PolicyEndDate,
        CodeDetail $PaymentFrequency,
        ?BancAssuranceReferral $ReferralID = null,
        ?Carbon $IssuedDate = null,
        ?Carbon $ExpiryDate = null,
        bool    $IsActive = true,
        InsurancePolicyStatus  $Status,
        User                   $user
    ): self
    {

        $lastPolicyNumber = BancassurancePolicy::withTrashed() // in case you're using soft deletes
        ->selectRaw("MAX(CAST(SUBSTRING(PolicyNumber, 8, LEN(PolicyNumber)) AS INT)) as max_number")
            ->value('max_number');
        $nextNumber = $lastPolicyNumber ? $lastPolicyNumber + 1 : 1;
        $PolicyNumber = 'POLICY-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);


        $policy = BancassurancePolicy::create([
            'CustomerID' => $CustomerID -> Id,
            'ProductID' => $ProductID -> Id,
            'InsurerID' => $InsurerID -> Id ?? null,
            'PolicyNumber' => $PolicyNumber,
            'SumAssured' => $SumAssured,
            'PremiumAmount' => $PremiumAmount,
            'PolicyStartDate' => $PolicyStartDate,
            'PolicyEndDate' => $PolicyEndDate,
            'PaymentFrequency' => $PaymentFrequency->ID,
            'ReferralID' => $ReferralID->Id ?? null,
            'IssuedDate' => $IssuedDate,
            'ExpiryDate' => $ExpiryDate,
            'IsActive' => $IsActive,
            'Status' => $Status->value,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);


        activity()->causedBy($user->Id)->performedOn($policy)->event('create')->log("Added Policy {$policy->Id}.");
        return new self($policy);
    }
    


    public static function uploadpolicy(
        BancassurancePolicy $policy,
        User $user,
        UploadedFile $document = null
    ): self {
        if ($document) {
        $policy->newDocument(
            ModulesEnum::Property,
            $document,
            [PermissionEnum::BancassurancePolicyView->value],
            $user
            );
        }

        activity()
            ->causedBy($user->Id)
            ->performedOn($policy)
            ->event('update')
            ->log("Document uploaded on Policy {$policy->Id}.");

        return new self($policy);
    }
}
