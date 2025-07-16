<?php

namespace App\Services\Property\BillingAndReceipting;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use Exception;

class PropertyReceiptService
{

    public static function create(
        PropertyInvoice $InvoiceID,
        string $BillingMonth,
        string $InvoiceDate,
        int  $RentAmount,
        int  $ServicesCharge,
        int  $OtherCharges,
        int  $TotalDue,
        int  $AmountPaid,
        int  $Balance,
        string $PaymentDate,
        int  $Amount,
        string $PaymentMethod,
        string $ReferenceNo,
        string $Remarks,
        User $user
    ): PropertyReceipt
    {

        // Check if a schedule already exists for the lease
        $exists = PropertyReceipt::where('InvoiceID', $InvoiceID)->exists();

        if ($exists) {
            throw new Exception('This Invoice is already Receipted.');
        }
        $receipt = PropertyReceipt::create([
            'InvoiceID' => $InvoiceID,
            'BillingMonth' => $BillingMonth,
            'InvoiceDate' => $InvoiceDate,
            'RentAmount' => $RentAmount,
            'ServicesCharge' => $ServicesCharge,
            'OtherCharges' => $OtherCharges,
            'TotalDue' => $TotalDue,
            'AmountPaid' => $AmountPaid,
            'Balance' => $Balance,
            'PaymentDate' => $PaymentDate,
            'Amount' => $Amount,
            'PaymentMethod' => $PaymentMethod,
            'ReferenceNo' => $ReferenceNo,
            'Remarks' => $Remarks,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($receipt)
            ->withProperties(['InvoiceID' => $InvoiceID])
            ->log("Added Lease Schedule for Lease ID {$InvoiceID}.");

        return $receipt;
    }
}
