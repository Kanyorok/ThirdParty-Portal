<?php

namespace App\Services\Property\BillingAndReceipting;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyNewLease;


class PropertyInvoiceService
{
    protected $invoice;

    public function __construct(PropertyInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public static function create(
        PropertyNewLease $Lease,
        string           $BillingMonth,
        string           $InvoiceDate,
        float            $RentAmount,
        float            $ServicesCharge,
        float            $OtherCharges,
        string           $InvoiceNotes,
        User             $user
    ): self
    {


        $lastInvoice = PropertyInvoice::orderByDesc('Id')->first();
        $nextNumber = $lastInvoice ? ((int)filter_var($lastInvoice->InvoiceNumber, FILTER_SANITIZE_NUMBER_INT)) + 1 : 1;
        $InvoiceNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $invoice = PropertyInvoice::create([
            'InvoiceNumber' => $InvoiceNumber,
            'Lease' => $Lease->Id,
            'BillingMonth' => $BillingMonth,
            'InvoiceDate' => $InvoiceDate,
            'RentAmount' => $RentAmount,
            'ServicesCharge' => $ServicesCharge,
            'OtherCharges' => $OtherCharges,
            'InvoiceNotes' => $InvoiceNotes,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($invoice)->event('create')->log("Added Property Invoice {$invoice->Id}.");

        return new self($invoice);
    }
}
