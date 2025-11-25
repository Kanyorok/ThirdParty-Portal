<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\BancassuranceCommissionPayout;
use App\Models\Insurance\BancassurancePolicy;
use DateTime;

class BancassuranceCommissionPayoutService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceCommissionPayout $payout)
    {
    }

    public static function create(
        BancassurancePolicy $PolicyId,
        string              $PayoutReference,
        float               $PaidAmount,
        DateTime            $PaymentDate,
        CodeDetail          $PaymentMode,
        string              $Remarks,
        User                $PaidBy,
        User                $user

    ): self
    {

        $payout = BancassuranceCommissionPayout::create([
            'PolicyId' => $PolicyId->Id,
            'PayoutReference' => $PayoutReference,
            'PaidAmount' => $PaidAmount,
            'PaymentDate' => $PaymentDate,
            'PaymentMode' => $PaymentMode->ID,
            'Remarks' => $Remarks,
            'PaidBy' => $PaidBy->Id,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($payout)->event('create')->log("Added Provider {$payout->Id}.");
        return new self($payout);
    }

}
