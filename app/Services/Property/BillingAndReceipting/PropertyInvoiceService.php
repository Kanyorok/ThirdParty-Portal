<?php

namespace App\Services\Property\BillingAndReceipting;

use App\Enums\Property\PropertyInvoiceEnum;
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
        string $BillingMonth,
        string $InvoiceDate,
        float  $RentAmount,
        float  $ServicesCharge,
        float  $OtherCharges,
        float  $ParkingFee,
        string $InvoiceNotes,
        PropertyInvoiceEnum $Status,
        User   $user
    ): self
    {
        // Get the latest invoice number
        $lastInvoice = PropertyInvoice::withTrashed()
            ->selectRaw("CAST(SUBSTRING(InvoiceNumber, 5, 5) AS INT) as num")
            ->orderByDesc('num')
            ->value('num');

        $nextNumber = $lastInvoice ? $lastInvoice + 1 : 1;
        $InvoiceNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        try {
            $invoice = PropertyInvoice::create([
                'InvoiceNumber' => $InvoiceNumber,
                'Lease' => $Lease->Id,
                'BillingMonth' => $BillingMonth,
                'InvoiceDate' => $InvoiceDate,
                'RentAmount' => $RentAmount,
                'ServicesCharge' => $ServicesCharge,
                'OtherCharges' => $OtherCharges,
                'InvoiceNotes' => $InvoiceNotes,
                'ParkingFee'    =>  $ParkingFee,
                'Status' => PropertyInvoiceEnum::Pending->value,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 't_rentinvoice_invoicenumber_unique')) {
                // Optional: retry with a new number (careful with recursion/loops)
                // or throw a custom exception or return a useful response
                throw new \Exception("Duplicate invoice number detected. Please try again.");
            } else {
                throw $e;
            }
        }

        activity()->causedBy($user->Id)->performedOn($invoice)->event('create')->log("Added Property Invoice {$invoice->Id}.");

        return new self($invoice);
    }

}
