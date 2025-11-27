<?php

namespace App\Services\Property\BillingAndReceipting;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use Exception;

class PropertyReceiptService
{

    public static function create(
        PropertyInvoice $InvoiceID,
        string $BillingMonth,
        string $InvoiceDate,
        float  $RentAmount,
        float  $ServicesCharge,
        float  $ParkingFee,
        float  $OtherCharges,
        float  $TotalDue,
        float  $AmountPaidSoFar,
        float  $Balance,
        string $PaymentDate,
        int  $AmountPaidNow,
        CodeDetail $PaymentMethod,
        string $ReferenceNo,
        string $Remarks = null,
        User $user
    ): PropertyReceipt
    {

        $receipt = PropertyReceipt::create([
            'InvoiceID' => $InvoiceID->Id,
            'BillingMonth' => $BillingMonth,
            'InvoiceDate' => $InvoiceDate,
            'RentAmount' => $RentAmount,
            'ServicesCharge' => $ServicesCharge,
            'ParkingFee'    =>  $ParkingFee,
            'OtherCharges' => $OtherCharges,
            'TotalDue' => $TotalDue,
            'AmountPaidSoFar' => $AmountPaidSoFar,
            'Balance' => $Balance,
            'PaymentDate' => $PaymentDate,
            'AmountPaidNow' => $AmountPaidNow,
            'PaymentMethod' => $PaymentMethod->ID,
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

    public static function update(
        PropertyReceipt $receipt,
        PropertyInvoice $InvoiceID,
        string          $BillingMonth,
        string          $InvoiceDate,
        float           $RentAmount,
        float           $ServicesCharge,
        float           $ParkingFee,
        float           $OtherCharges,
        float           $TotalDue,
        float           $AmountPaidSoFar,
        float           $Balance,
        string          $PaymentDate,
        int             $AmountPaidNow,
        CodeDetail      $PaymentMethod,
        string          $ReferenceNo,
        ?string         $Remarks,
        User            $user
    ): PropertyReceipt
    {
        $receipt->update([
            'InvoiceID' => $InvoiceID->Id,
            'BillingMonth' => $BillingMonth,
            'InvoiceDate' => $InvoiceDate,
            'RentAmount' => $RentAmount,
            'ServicesCharge' => $ServicesCharge,
            'ParkingFee' => $ParkingFee,
            'OtherCharges' => $OtherCharges,
            'TotalDue' => $TotalDue,
            'AmountPaidSoFar' => $AmountPaidSoFar,
            'Balance' => $Balance,
            'PaymentDate' => $PaymentDate,
            'AmountPaidNow' => $AmountPaidNow,
            'PaymentMethod' => $PaymentMethod->ID,
            'ReferenceNo' => $ReferenceNo,
            'Remarks' => $Remarks,
            'ModifiedBy' => $user->Id,
            'ModifiedOn' => now(),
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($receipt)
            ->withProperties(['InvoiceID' => $InvoiceID->Id])
            ->log("Updated Property Receipt {$receipt->Id} for Invoice {$InvoiceID->Id}.");

        return $receipt;
    }

}
