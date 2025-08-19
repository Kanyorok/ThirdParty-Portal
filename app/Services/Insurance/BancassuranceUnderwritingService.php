<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassurancePolicy;
use App\Models\Insurance\BancassuranceUnderwriting;
use Illuminate\Support\Carbon;

class BancassuranceUnderwritingService
{
    /**
     * Create a new class instance.
     */
    public function __construct(BancassuranceUnderwriting $bancassuranceUnderwriting)
    {
        $this->BancassuranceUnderwriting = $bancassuranceUnderwriting;
    }

    public static function create(
        BancassurancePolicy $PolicyId,
        Carbon $FeedbackDate,
        Int $RiskScore,
        CodeDetail $Decision,
        string $Comments,
        User $user
    ):self{
        $underwriting = BancassuranceUnderwriting::create([
            'PolicyId' => $PolicyId -> Id,
            'FeedbackDate' => $FeedbackDate,
            'RiskScore' => $RiskScore,
            'Decision' => $Decision -> ID,
            'Comments' => $Comments,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user->Id)
            ->performedOn($underwriting)
            ->event('create')
            ->log("Added Bank Assurance Underwiting {$underwriting->Id}.");

        return new self($underwriting);
    }
}
