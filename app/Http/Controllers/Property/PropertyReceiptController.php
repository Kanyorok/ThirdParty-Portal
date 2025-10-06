<?php

namespace App\Http\Controllers\Property;


use App\Enums\Core\PermissionEnum;
use App\Enums\Property\PropertyInvoiceEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\BillingAndReceipting\PropertyReceiptRequest;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use App\Services\Property\BillingAndReceipting\PropertyReceiptService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyReceiptController extends Controller
{
    //
    public function index()
    {
        $receipts = PropertyReceipt::all();
        return view('property.billingandreceipting.receipting.index', compact('receipts'));
    }

    public function getAmountPaidSoFar($invoiceId)
    {
        $amountPaid = PropertyReceipt::where('InvoiceID', $invoiceId)->sum('AmountPaidNow');
        return response()->json(['amount_paid' => $amountPaid]);
    }


    public function create()
    {

        $this->authorize(PermissionEnum::PropertyReceiptCreate, PropertyReceipt::class);

        $invoices = PropertyInvoice::with('receipts')->get()->filter(function ($invoice) {
            $totalDue = ($invoice->RentAmount ?? 0) + ($invoice->ServicesCharge ?? 0) + ($invoice->ParkingFee ?? 0) + ($invoice->OtherCharges ?? 0);
            $paid = PropertyReceipt::getAmountPaidSoFar($invoice->Id);
            return $paid < $totalDue;
        });

        $codes = CodeDetail::where('CodeID', 'PaymentMethod')->get();

        $amountsPaid = [];
        foreach ($invoices as $invoice) {
            $amountsPaid[$invoice->Id] = PropertyReceipt::getAmountPaidSoFar($invoice->Id);
        }

        return view('property.billingandreceipting.receipting.create', compact('invoices', 'codes', 'amountsPaid'));
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyReceiptView, PropertyReceipt::class);
        $receipt = PropertyReceipt::all()->find($Id);
        return view('property.billingandreceipting.receipting.show', compact('receipt'));
    }
    public function store(PropertyReceiptRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyReceiptCreate, PropertyReceipt::class);
        try {
            $validated = $request->validated();
            $InvoiceID = $validated['InvoiceID'];
            $BillingMonth = $validated['BillingMonth'];
            $InvoiceDate = $validated['InvoiceDate'];
            $RentAmount = floatval($validated['RentAmount']);
            $ServicesCharge = floatval($validated['ServicesCharge']);
            $OtherCharges = floatval($validated['OtherCharges']);
            $ParkingFee = floatval($validated['ParkingFee']);
            $AmountPaidSoFar = floatval($validated['AmountPaidSoFar']);
            $AmountPaidNow = floatval($validated['AmountPaidNow']);
            $PaymentMethod = CodeDetail::findOrFail($validated['PaymentMethod']);
            $invoice = PropertyInvoice::findOrFail($InvoiceID);

            // Create the receipt
            PropertyReceiptService::create(
                $invoice,
                $BillingMonth,
                $InvoiceDate,
                $RentAmount,
                $ServicesCharge,
                $ParkingFee,
                $OtherCharges,
                $validated['TotalDue'],
                $AmountPaidSoFar,
                $validated['Balance'],
                $validated['PaymentDate'],
                $AmountPaidNow,
                $PaymentMethod,
                $validated['ReferenceNo'],
                $validated['Remarks'] ?? '',
                Auth::user()
            );

            // --- Update Invoice Status ---
            $totalDue = $RentAmount + $ServicesCharge + $ParkingFee + $OtherCharges;
            $totalPaid = PropertyReceipt::getAmountPaidSoFar($InvoiceID);

            if ($totalPaid >= $totalDue) {
                $invoice->Status = PropertyInvoiceEnum::FullyPaid->value;
            } elseif ($totalPaid > 0) {
                $invoice->Status = PropertyInvoiceEnum::PartialPaid->value;
            } else {
                $invoice->Status = PropertyInvoiceEnum::Pending->value;
            }
            $invoice->save();

            return redirect()->route('rentreceipt.index')->with('success', 'Rent receipt created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function print($Id)
    {
        $this->authorize(PermissionEnum::PropertyReceiptPrint, PropertyReceipt::class);
        $receipt = PropertyReceipt::with(['invoice.lease.tenant'])->findOrFail($Id);
        $tenantName = optional(optional($receipt->invoice)->lease)->tenant->ThirdPartyName ?? 'N/A';
 
                $html = "
        <html>
        <head>
        <h2>" . config('app.name') . "</h2>
        <title>Tenant Receipt</title>
        <style>
                        body { font-family: Arial, sans-serif; margin: 30px; }
                        h2 { text-align: center; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        td, th { padding: 8px; border: 1px solid #ccc; text-align: left; }
                        .center { text-align: center; }
        </style>
        </head>
        <body onload='window.print();'>
        <h2>Tenant Payment Receipt</h2>
        <p><strong>Tenant:</strong> {$receipt->invoice->lease->tenant->thirdParty->ThirdPartyName }</strong></p>
        <p><strong>Receipt No:</strong> {$receipt->ReferenceNo}</strong></p>
        <p><strong>Invoice No:</strong> " . ($receipt->invoice->InvoiceNumber ?? '-') . "</strong></p>
        <p><strong>Payment Date:</strong> {$receipt->PaymentDate}</p>
        <p><strong>Payment Method:</strong> {$receipt->paymentmethod->Description}</p>
        <table>
        <thead>
        <tr>
        <th>Description</th>
        <th>Amount (KES)</th>
        </tr>
        </thead>
        <tbody>
        <tr><td>Rent</td><td>{$receipt->RentAmount}</td></tr>
        <tr><td>Services Charge</td><td>{$receipt->ServicesCharge}</td></tr>
        <tr><td>Other Charges</td><td>{$receipt->OtherCharges}</td></tr>
        <tr><td>Total Due</strong></td><td>{$receipt->TotalDue}</td></tr>
        <tr><td><strong>Balance</td><td>{$receipt->Balance}</strong></td></tr>
        <tr><td><strong>Paid</td><td>{$receipt->AmountPaidNow}</strong></td></tr>
        <tr><td><strong>Status</td><td>{$receipt->invoice->Status->Label()}</strong></td></tr>
        </tbody>
        </table>
        <p><strong>Remarks:</strong> {$receipt->Remarks}</p>
        <p class='center'>Thank you for your payment.</p>
        </body>
        </html>
                ";
 
        return response($html)->header('Content-Type', 'text/html');
    }


    public function destroy($id)
    {

        $this->authorize(PermissionEnum::PropertyReceiptDelete, PropertyReceipt::class);
        try {
            $receipts = PropertyReceipt::findOrFail($id);
            $receipts->delete();

            return redirect()->route('rentreceipt.index')
                ->with('success', 'Rent Receipt Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Rent Receipt: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Rent  Receipt. Please try again.'])
                ->withInput();
        }
    }
}
