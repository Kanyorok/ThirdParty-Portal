<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundContribution;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Carbon;

class MedicalFundContributionService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public MedicalFundContribution $medicalfundcontribution)
    {
        //
    }

    Public static function create(
        MedicalFund $FundId,
        CodeDetail $ContributorType,
        ?ThirdParties $ContributorId = null,
        float $Amount,
        Carbon $ContributionDate,
        ?string $Notes = null,
        User $user
    ): self
    {
        $medicalfundcontribution = MedicalFundContribution::create([
            'FundId'           => $FundId->Id,
            'ContributorType'  => $ContributorType->ID,
            'ContributorId'    => $ContributorId->Id ?? null,
            'Amount'           => $Amount,
            'ContributionDate' => $ContributionDate,
            'Notes'            => $Notes,
            'CreatedBy'        => $user->Id,
            'ModifiedBy'       => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($medicalfundcontribution)->event('create')->log("Added Medical Fund Contribution {$medicalfundcontribution->Id}.");
        return new self($medicalfundcontribution);
    }
}
