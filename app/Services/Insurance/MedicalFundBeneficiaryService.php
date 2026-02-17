<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundBeneficiary;
use Illuminate\Support\Carbon;

class MedicalFundBeneficiaryService
{
    /**
     * The underlying model instance.
     */
    public function __construct(public MedicalFundBeneficiary $medicalFundBeneficiary)
    {
    }

    /**
     * Create a new Medical Fund Beneficiary.
     */
    public static function create(
        MedicalFund $fund,
        string $fullName,
        ?CodeDetail $relationship = null,
        ?Carbon $dateOfBirth = null,
        ?string $nationalId = null,
        ?string $contact = null,
        bool $isActive = true,
        ?User $user = null
    ): self {
        $beneficiary = MedicalFundBeneficiary::create([
            'FundId' => $fund->Id,
            'FullName' => $fullName,
            'Relationship' => $relationship?->ID ?? null,
            'DateOfBirth' => $dateOfBirth,
            'NationalID' => $nationalId,
            'Contact' => $contact,
            'IsActive' => $isActive,
            'CreatedBy' => $user?->Id,
            'ModifiedBy' => $user?->Id,
        ]);

        if (function_exists('activity')) {
            activity()
                ->causedBy($user?->Id)
                ->performedOn($beneficiary)
                ->event('create')
                ->log("Added Medical Fund Beneficiary {$beneficiary->FullName} to Fund #{$fund->Id}.");
        }

        return new self($beneficiary);
    }

    /**
     * Update an existing Medical Fund Beneficiary.
     */
    public function update(
        string $fullName,
        ?CodeDetail $relationship = null,
        ?Carbon $dateOfBirth = null,
        ?string $nationalId = null,
        ?string $contact = null,
        bool $isActive = true,
        ?User $user = null
    ): self {
        $this->medicalFundBeneficiary->update([
            'FullName' => $fullName,
            'Relationship' => $relationship?->ID ?? null,
            'DateOfBirth' => $dateOfBirth,
            'NationalID' => $nationalId,
            'Contact' => $contact,
            'IsActive' => $isActive,
            'ModifiedBy' => $user?->Id,
        ]);

        if (function_exists('activity')) {
            activity()
                ->causedBy($user?->Id)
                ->performedOn($this->medicalFundBeneficiary)
                ->event('update')
                ->log("Updated Medical Fund Beneficiary {$this->medicalFundBeneficiary->FullName}.");
        }

        return $this;
    }

    /**
     * Delete the Medical Fund Beneficiary.
     */
    public function delete(?User $user = null): void
    {
        $name = $this->medicalFundBeneficiary->FullName;
        $this->medicalFundBeneficiary->delete();

        if (function_exists('activity')) {
            activity()
                ->causedBy($user?->Id)
                ->performedOn($this->medicalFundBeneficiary)
                ->event('delete')
                ->log("Deleted Medical Fund Beneficiary {$name}.");
        }
    }
}
