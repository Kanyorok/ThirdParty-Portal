<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassurancePolicy;
use Illuminate\Support\Carbon;

class BancassuranceClaimService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceClaim $bancassuranceClaim)
    {
        //
    }

    public static function create(
        BancassurancePolicy $PolicyId,
        CodeDetail          $ClaimType,
        string              $ClaimReason,
        float               $ClaimAmount,
        Carbon              $ClaimDate,
        CodeDetail          $Status,
        User                $user
    ): self
    {
        $claim = BancassuranceClaim::create([
            'PolicyId' => $PolicyId->Id,
            'ClaimType' => $ClaimType->ID,
            'ClaimReason' => $ClaimReason,
            'ClaimAmount' => $ClaimAmount,
            'ClaimDate' => $ClaimDate,
            'Status' => $Status->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($claim)->event('create')->log("Added Policy claim {$claim->Id}.");
        return new self($claim);
    }
}
