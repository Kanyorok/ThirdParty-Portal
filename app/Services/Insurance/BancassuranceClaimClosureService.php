<?php

namespace App\Services\Insurance;

use App\Enums\Insurance\InsuranceClosureEnum;
use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassuranceClaimClosure;
use Illuminate\Support\Carbon;

class BancassuranceClaimClosureService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceClaimClosure $bancassuranceclaimclosure)
    {
        //
    }

    public static function create(
        BancassuranceClaim   $ClaimId,
        InsuranceClosureEnum $FinalStatus,
        string               $FinalRemarks,
        Carbon               $ClosureDate,
        User                 $user
    ): self
    {
        $closure = BancassuranceClaimClosure::create([
            'ClaimId' => $ClaimId->Id,
            'FinalStatus' => $FinalStatus->value,
            'FinalRemarks' => $FinalRemarks,
            'ClosureDate' => $ClosureDate,
            'ClosedBy' => $user->Id,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user->Id)
            ->performedOn($closure)
            ->event('create')
            ->log("Added Claim Closure {$closure->Id}.");

        return new self($closure);
    }
}
