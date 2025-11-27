<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\InsuranceProvider;
use App\Models\Insurance\MedicalFund;

class MedicalFundService
{
    /**
     * Create a new class instance.
     */
    public function __construct( public MedicalFund $medicalfund)
    {
    }

    public static function create(
        string $FundName,
        InsuranceProvider $ProviderId,
        CodeDetail $CoverageType,
        float $CoverageLimit,
        ?string $Description = null,
        bool $IsActive,
        User $user
    ): self {
         
        $medicalfund = MedicalFund::create([
            'FundName'      => $FundName,
            'ProviderId'    => $ProviderId->Id,
            'CoverageType'  => $CoverageType->ID,
            'CoverageLimit' => $CoverageLimit,
            'Description'   => $Description,
            'IsActive'      => $IsActive ? 1 : 0,
            'CreatedBy'     => $user->Id,
            'ModifiedBy'    => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($medicalfund)->event('create')->log("Added Medical Fund {$medicalfund->Id}.");
        return new self($medicalfund);
    }

    public static function update(
        MedicalFund $medicalfund,
        string $FundName,
        InsuranceProvider $ProviderId,
        CodeDetail $CoverageType,
        float $CoverageLimit,
        ?string $Description = null,
        bool $IsActive,
        User $user
    ): self {
        // Update the provided model instance (do not call update statically)
        $medicalfund->update([
            'FundName'      => $FundName,
            'ProviderId'    => $ProviderId->Id,
            'CoverageType'  => $CoverageType->ID,
            'CoverageLimit' => $CoverageLimit,
            'Description'   => $Description,
            'IsActive'      => $IsActive ? 1 : 0,
            'ModifiedBy'    => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($medicalfund)->event('update')->log("Updated Medical Fund {$medicalfund->Id}.");
        return new self($medicalfund);
    }
}
