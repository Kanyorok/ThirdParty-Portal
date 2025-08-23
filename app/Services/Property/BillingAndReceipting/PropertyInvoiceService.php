<?php

namespace App\Services\Property\BillingAndReceipting;

use App\Enums\Property\PropertyInvoiceEnum;
use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyNewLease;
use Illuminate\Support\Facades\DB;


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
        float  $ServicesCharge = null,
        float  $OtherCharges = null,
        float  $ParkingFee = null,
        string $InvoiceNotes = null,
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
            DB::beginTransaction();
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

            //Posting to financee invoicee table
            $finance = app(\App\Services\Finance\InvoiceIntakeService::class);
            //Get the tenantID
            $tenantID =PropertyNewLease::find($Lease->Id)->Tenant;
            // Build Finance lines (include only non-zero lines)
            $lines = [];
            $addLine = function (string $name, float $amount) use (&$lines, $Lease) {
                $amt = (int) round($amount);
                if ($amt > 0) {
                    $lines[] = [
                        'InvoiceLineName' => $name,
                        'Description'     => "Lease #{$Lease->Id}",
                        'UnitCost'        => $amt,
                        'Quantity'        => 1,
                        'Tax'             => null,
                        'TaxID'           => null,
                        'TaxAmount'       => '0',
                        'Discount'        => 0,
                        'Total'           => $amt,
                    ];
                }
            };
            $addLine('Monthly Rent',  (float) $RentAmount);
            $addLine('Service Charge',(float) $ServicesCharge);
            $addLine('Parking Fee',   (float) $ParkingFee);
            $addLine('Other Charges', (float) $OtherCharges);

            if (!empty($lines)) {
                $total = array_sum(array_column($lines, 'Total'));

                // Prepare Finance payload (no IdempotencyKey here)
                $payload = [
                    'SourceTable' => 't_RentInvoice',

                    'ModuleID' => 500000,
                    'CurrencyID' => $Lease->CurrencyID ?? 1,
                    'CustomerID' => $tenantID  ?? null,

                    'InvoiceID' => $invoice->Id,
                    'InvoiceNumber' => $InvoiceNumber,

                    'InvoiceTitle' => "Rent & Charges {$BillingMonth}",
                    'InvoiceDate' => (string)$InvoiceDate,
                    'DueDate' => (string)$InvoiceDate,
                    'InvoiceRemarks' => $InvoiceNotes ?? 'Auto-generated from Property',

                    'TotalAmount' => (int)$total,
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                    'lines' => $lines, //This shoukd be an array of the items that will be disp in yuir module
                ];
                // Finance service will internally generate RequestID
                $result = $finance->intake($payload, true);

                // Store only the RequestID back into t_RentInvoice
                if (!empty($result['request_id'])) {
                    $invoice->RequestID = $result['request_id'];
                    $invoice->save();
                }
            }
            DB::commit();

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
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
