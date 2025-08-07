<?php

namespace App\Services\Insurance\PremiumManagement;

use App\Models\Insurance\BancassurancePolicies;
use App\Models\Core\CodeDetail;
use App\Models\Auth\User;
use App\Models\Insurance\BancassurancePremiumPayments;
use DateTime;

class BancassurancePremiumPaymentsService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassurancePremiumPayments $payment)
    {
    }
     public static function create(
        BancassurancePolicies $PolicyID,
        DateTime $PaymentDate,
        string $Amount,
        CodeDetail $PaymentMode,
        string $ReferenceNumber,
        string $Notes,
        User $user
    ): self
    {
        $payment = BancassurancePremiumPayments::create([
            'PolicyID' => $PolicyID->Id,
            'PaymentDate' => $PaymentDate,
            'Amount' => $Amount,
            'PaymentMode' => $PaymentMode->ID,
            'ReferenceNumber' => $ReferenceNumber,
            'Notes' => $Notes,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,

        ]);

        activity()->causedBy($user->Id)->performedOn($payment)->event('create')->log("Added Premium Payments {$payment->Id}.");
        return new self($payment);
        

    }
}