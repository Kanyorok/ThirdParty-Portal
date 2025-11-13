<?php

namespace App\Services\Property\BillingAndReceipting;

use App\Enums\Property\PropertyInvoiceEnum;
use App\Models\Auth\User;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxType;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Services\Finance\InvoiceIntakeService;
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
        string $Description = null, // invoice-level description
        ?string $DescriptionRent = null,
        ?string $DescriptionService = null,
        ?string $DescriptionParking = null,
        ?string $DescriptionOther = null,
        ?Currency $Currency = null,
        ?FinanceTaxType $TaxRent = null,
        ?FinanceTaxType $TaxService = null,
        ?FinanceTaxType $TaxParking = null,
        ?FinanceTaxType $TaxOther = null,
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
                'ParkingFee'   =>  $ParkingFee,
                'Description'  =>  $Description,
                'Currency' =>  $Currency->Id ?? null,
                'Tax' =>  $TaxRent->Id ?? null,
                'Status' => PropertyInvoiceEnum::Pending->value,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            //dd($invoice);

            //Posting to financee invoicee table
            $finance = app(InvoiceIntakeService::class);
            //Get the tenantID
            $tenantID =PropertyNewLease::find($Lease->Id)->Tenant;
            $thirdPartyID = PropertyNewTenant::find($tenantID)->ThirdPartyId;
            // Build Finance lines (include only non-zero lines)
            $lines = [];
            $addLine = function (string $name, float $amount, ?string $lineDescription, ?FinanceTaxType $LineTax) use (&$lines, $Lease) {
                $amt = (int) round($amount);
                if ($amt > 0) {
                    $lines[] = [
                        'InvoiceLineName' => $name,
                        'Description'     => trim("Lease #{$Lease->Id} $lineDescription" ?: "Lease #{$Lease->Id} {$name}"),
                        'UnitCost'        => $amt,
                        'Quantity'        => 1,
                        'Tax'             => $LineTax->Id,
                        'TaxID'           => null,
                        'TaxAmount'       => '0',
                        'Discount'        => 0,
                        'Total'           => $amt,
                    ];
                }
            };
            $addLine('Monthly Rent',  (float) $RentAmount, $DescriptionRent, $TaxRent);
            $addLine('Service Charge',(float) $ServicesCharge, $DescriptionService, $TaxService);
            $addLine('Parking Fee',   (float) $ParkingFee, $DescriptionParking, $TaxParking);
            $addLine('Other Charges', (float) $OtherCharges, $DescriptionOther, $TaxOther);

            //dd($lines);
            if (!empty($lines)) {
                $total = array_sum(array_column($lines, 'Total'));

                // Prepare Finance payload (no IdempotencyKey here)
                $payload = [
                    'SourceTable' => 't_RentInvoice',

                    'ModuleID' => 500000,
                    'CurrencyID' =>58,
                    'CustomerID' => $thirdPartyID ?? null,

                    'InvoiceID' => $invoice->Id,
                    'InvoiceNumber' => $InvoiceNumber,

                    'InvoiceTitle' => "Rent & Charges {$BillingMonth}",
                    'InvoiceDate' => (string)$InvoiceDate,
                    'DueDate' => (string)$InvoiceDate,
                    'InvoiceRemarks' => $InvoiceNotes ?? 'Auto-generated from Property',

                    'TotalAmount' => (int)$total,
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                    'lines' => $lines, //This should be an array of the items that will be disp in your module
                ];

                //dd($payload);
                // Finance service will internally generate RequestID
                //$result = $finance->intake($payload, true);
                try {
                    $result = $finance->intake($payload, true);
                    dd($result);
                } catch (\Throwable $e) {
                    dd('Finance error', $e->getMessage(), $e->getTraceAsString());
                }


                
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
