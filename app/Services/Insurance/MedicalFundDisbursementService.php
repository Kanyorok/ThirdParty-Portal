<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundBeneficiary;
use App\Models\Insurance\MedicalFundContributor;
use App\Models\Insurance\MedicalFundDisbursement;
use Illuminate\Support\Carbon;

class MedicalFundDisbursementService
{
    /**
     * The underlying model instance.
     */
    public function __construct(public MedicalFundDisbursement $medicalFundDisbursement)
    {
    }

    /**
     * Create a new Medical Fund Disbursement.
     */
    public static function create(
        MedicalFund $fund,
        MedicalFundContributor $contributor,
        MedicalFundBeneficiary $beneficiary,
        ?int $coverageID,
        ?int $packageID,
        Carbon $disbursementDate,
        float $amount,
        ?string $purpose = null,
        ?User $user = null
    ): self {
        $disbursement = MedicalFundDisbursement::create([
            'FundId' => $fund->Id,
            'ContributorId' => $contributor->Id,
            'BeneficiaryId' => $beneficiary->Id,
            'CoverageID' => $coverageID,
            'PackageID' => $packageID,
            'DisbursementDate' => $disbursementDate,
            'Amount' => $amount,
            'Purpose' => $purpose,
            'CreatedBy' => $user?->Id,
            'ModifiedBy' => $user?->Id,
        ]);

        if (function_exists('activity')) {
            activity()
                ->causedBy($user?->Id)
                ->performedOn($disbursement)
                ->event('create')
                ->log("Created disbursement of {$amount} for Contributor #{$contributor->Id}, Beneficiary #{$beneficiary->Id} in Fund #{$fund->Id}.");
        }

        return new self($disbursement);
    }

    /**
     * Update an existing Medical Fund Disbursement.
     */
    public function update(
        Carbon $disbursementDate,
        float $amount,
        ?string $purpose = null,
        ?User $user = null
    ): self {
        $this->medicalFundDisbursement->update([
            'DisbursementDate' => $disbursementDate,
            'Amount' => $amount,
            'Purpose' => $purpose,
            'ModifiedBy' => $user?->Id,
        ]);

        if (function_exists('activity')) {
            activity()
                ->causedBy($user?->Id)
                ->performedOn($this->medicalFundDisbursement)
                ->event('update')
                ->log("Updated Medical Fund Disbursement #{$this->medicalFundDisbursement->Id}.");
        }

        return $this;
    }

    /**
     * Delete the Medical Fund Disbursement.
     */
    public function delete(?User $user = null): void
    {
        $id = $this->medicalFundDisbursement->Id;
        $this->medicalFundDisbursement->delete();

        if (function_exists('activity')) {
            activity()
                ->causedBy($user?->Id)
                ->performedOn($this->medicalFundDisbursement)
                ->event('delete')
                ->log("Deleted Medical Fund Disbursement #{$id}.");
        }
    }
}
