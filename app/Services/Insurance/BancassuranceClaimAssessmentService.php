<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassuranceClaimAssessment;

class BancassuranceClaimAssessmentService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceClaimAssessment $bancassuranceclaimassment)
    {
        //
    }

    public static function create(
        BancassuranceClaim $claim,
        string $AssessmentComments,
        float $AssessmentAmount,
        CodeDetail $Decision,
        User $user,
    ): self {
        $assessment = BancassuranceClaimAssessment::create([
            'ClaimId' => $claim->Id,
            'AssessmentComments' => $AssessmentComments,
            'AssessmentAmount' => $AssessmentAmount,
            'Decision' => $Decision->ID,
            'AssessmentDate' => now(),
            'AssessedBy' => $user->Id,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user->Id)
            ->performedOn($assessment)
            ->event('create')
            ->log("Added Claim Assessment {$assessment->Id}.");

        return new self($assessment);
    }
}
