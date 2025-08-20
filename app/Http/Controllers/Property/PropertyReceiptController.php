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

        // Only get invoices that are not fully paid
        $invoices = PropertyInvoice::with('receipts')->get()->filter(function ($invoice) {
            $totalDue = ($invoice->RentAmount ?? 0) + ($invoice->ServicesCharge ?? 0) + ($invoice->ParkingFee ?? 0) + ($invoice->OtherCharges ?? 0);
            $paid = PropertyReceipt::getAmountPaidSoFar($invoice->Id);
            return $paid < $totalDue;
        });

        $codes = CodeDetail::where('CodeID', 'PaymentMethod')->get();

        // Build an array with amounts paid per invoice
        $amountsPaid = [];
        foreach ($invoices as $invoice) {
            $amountsPaid[$invoice->Id] = PropertyReceipt::getAmountPaidSoFar($invoice->Id);
        }

        return view('property.billingandreceipting.receipting.create', compact('invoices', 'codes', 'amountsPaid'));
    }

    public function show($Id)
    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyReceiptView, PropertyReceipt::class);
        $receipt = PropertyReceipt::with('code')->find($Id);
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
    public function edit($id)
    {
        $this->authorize(PermissionEnum::PropertyReceiptUpdate, PropertyReceipt::class);

        $receipts = PropertyReceipt::with('code')->findOrFail($id);
        $invoices = PropertyInvoice::all();
        $statuses = PropertyInvoiceEnum::cases(); // Pass enum cases to the view

        return view('property.billingandreceipting.receipting.edit', compact('receipts', 'invoices', 'statuses'));
    }
    public function update(PropertyReceiptRequest $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyReceiptUpdate, PropertyReceipt::class);

        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $receipts = PropertyReceipt::findOrFail($id);
            $invoice = PropertyInvoice::findOrFail($validated['InvoiceID']);

            $receipts->update([
                'InvoiceID'       => $validated['InvoiceID'],
                'BillingMonth'    => $validated['BillingMonth'],
                'InvoiceDate'     => $validated['InvoiceDate'],
                'RentAmount'      => $validated['RentAmount'],
                'ServicesCharge'  => $validated['ServicesCharge'],
                'ParkingFee'      => $validated['ParkingFee'],
                'OtherCharges'    => $validated['OtherCharges'],
                'TotalDue'        => $validated['TotalDue'],
                'AmountPaidSoFar'      => $validated['AmountPaidSoFar'],
                'Balance'         => $validated['Balance'],
                'PaymentDate'     => $validated['PaymentDate'],
                'AmountPaidNow'          => $validated['AmountPaidNow'],
                'PaymentMethod'   => $validated['PaymentMethod'],
                'ReferenceNo'     => $validated['ReferenceNo'],
                'Remarks'         => $validated['Remarks'],
                'ModifiedBy'      => Auth::id(),
            ]);

            // ✅ Update status on the invoice table
            $invoice->Status = $validated['Status'];
            $invoice->save();

            DB::commit();

            activity()
                ->performedOn($receipts)
                ->causedBy(Auth::user())
                ->withProperties(properties: ['action' => 'update'])
                ->log('Updated Rent Receipt');

            return redirect()->route('rentreceipt.index')->with('success', 'Rent Receipt updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Rent Receipt: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update Rent Receipt'])->withInput();
        }
    }

       public function destroy($id)
    {
        //Check if user has permission to delete property categories
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


