<?php

namespace App\Services\Insurance;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassuranceClaimAssessment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

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
        string             $AssessmentComments,
        float              $AssessmentAmount,
        CodeDetail         $Decision,
        User               $user,
        UploadedFile        $document = null
    ): self
    {
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

        if ($document) {
            $assessment->newDocument(
                ModulesEnum::Insurance,
                $document,
                [PermissionEnum::BancassuranceClaimAssessmentView->value],
                $user
            );
        }

        activity()
            ->causedBy($user->Id)
            ->performedOn($assessment)
            ->event('create')
            ->log("Added Claim Assessment {$assessment->Id}.");

        return new self($assessment);
    }


    public static function update(
        BancassuranceClaimAssessment $assessments,
        string                       $AssessmentComments,
        float                        $AssessmentAmount,
        CodeDetail                   $Decision,
        User                         $user,
        UploadedFile                 $document = null
    ): self
    {
        $assessments->update([
            'AssessmentComments' => $AssessmentComments,
            'AssessmentAmount' => $AssessmentAmount,
            'Decision' => $Decision->ID,
            'AssessmentDate' => now(),
            'AssessedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document) {
            $assessments->newDocument(
                ModulesEnum::Insurance,
                $document,
                [PermissionEnum::BancassuranceClaimAssessmentView->value],
                $user
            );
        }
        

        activity()
            ->causedBy($user->Id)
            ->performedOn($assessments)
            ->event('update')
            ->log("Updated assessment {$assessments->Id}");

        return new self($assessments);
    }

}
