<?php

namespace App\Services\Insurance\PremiumManagement;

use App\Models\Insurance\BancassurancePolicy;
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
        BancassurancePolicy $PolicyID,
        string              $CustomerID,
        string              $PaymentFrequency,
        DateTime            $PaymentDate,
        DateTime            $NextPaymentDate,
        string              $Amount,
        CodeDetail          $PaymentMode,
        string              $ReferenceNumber,
        ?string              $Notes = null,
        User                $user
    ): self
    {
        $payment = BancassurancePremiumPayments::create([
            'PolicyID' => $PolicyID->Id,
            'CustomerID' => $CustomerID,
            'PaymentFrequency' => $PaymentFrequency,
            'PaymentDate' => $PaymentDate,
            'NextPaymentDate' => $NextPaymentDate,
            'Amount' => $Amount,
            'PaymentMode' => $PaymentMode->ID,
            'ReferenceNumber' => $ReferenceNumber,
            'Notes' => $Notes ?? null,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,

        ]);

        activity()->causedBy($user->Id)->performedOn($payment)->event('create')->log("Added Premium Payments {$payment->Id}.");
        return new self($payment);


    }
}
