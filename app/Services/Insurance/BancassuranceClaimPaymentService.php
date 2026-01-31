<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassuranceClaimPayment;
use Illuminate\Support\Carbon;

class BancassuranceClaimPaymentService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceClaimPayment $bancassuranceclaimpayment)
    {
    }

    public static function create(
        BancassuranceClaim $ClaimId,
        Carbon $PaymentDate,
        float $PaymentAmount,
        string $PaymentReference,
        ?string $Note = null,
        User $PaidTo,
        CodeDetail $PaymentMethod,
        User $user
    ): self {
        $payment = BancassuranceClaimPayment::create([
            'ClaimId' => $ClaimId->Id,
            'PaymentDate' => $PaymentDate,
            'PaymentAmount' => $PaymentAmount,
            'PaymentReference' => $PaymentReference,
            'Note' => $Note,
            'PaidTo' => $PaidTo->Id,
            'PaymentMethod' => $PaymentMethod->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user->Id)
            ->performedOn($payment)
            ->event('create')
            ->log("Added Claim Assessment {$payment->Id}.");

        return new self($payment);
    }
}
