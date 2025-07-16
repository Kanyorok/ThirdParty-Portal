<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\BillingAndReceipting\PropertyReceiptRequest;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use App\Services\Property\BillingAndReceipting\PropertyReceiptService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PropertyReceiptController extends Controller
{
    //
    public function index()
    {
        $receipts = PropertyReceipt::all();
        return view('property.billingandreceipting.receipting.index', compact('receipts'));
    }

    public function create(){
        $this->authorize(PermissionEnum::PropertyReceiptCreate, PropertyReceipt::class);
        $invoices = PropertyInvoice::all();
        return view('property.billingandreceipting.receipting.create', compact('invoices'));
    }

        public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyReceiptView, PropertyReceipt::class);
        $receipt = PropertyReceipt::find($Id);
        return view('property.billingandreceipting.receipting.show', compact('receipt'));
    }
    public function store(PropertyReceiptRequest $request)
    {
        //dd($request->all());
        $this->authorize(PermissionEnum::PropertyReceiptCreate, PropertyReceipt::class);
            try {
                $validated = $request->validated();
                $InvoiceID = (int) $validated['InvoiceID'];
                $BillingMonth = (int) $validated['BillingMonth'];
                $InvoiceDate = (int) $validated['InvoiceDate'];
                $RentAmount = (int) $validated['RentAmount'];
                $ServicesCharge = (int) $validated['ServicesCharge'];
                $OtherCharges = (int) $validated['OtherCharges'];
          //dd('Validation');      
    $receipt = PropertyInvoice::findOrFail($InvoiceID);
       PropertyReceiptService::create(
            $InvoiceID,
            $BillingMonth,
            $InvoiceDate,
            $RentAmount,
            $ServicesCharge,
            $OtherCharges,
            $validated ['TotalDue'], 
            $validated['AmountPaid' ],
            $validated['Balance'],
            $validated['PaymentDate'],
            $validated ['Amount' ],
            $validated['PaymentMethod'],
            $validated['ReferenceNo'],
            $validated['Remarks'],
             Auth::user()
        );
        //dd('Validation');
        return redirect()->route('rentreceipt.index')->with('success', 'Rent receipt created successfully');
        } catch (Exception $e) {
            // Redirect back with error message
            return redirect()->back()->with('error', $e->getMessage());
    }

    }
    public function print($Id)
{
    $receipt = PropertyReceipt::with(['invoice.lease.tenant'])->findOrFail($Id);
    $tenantName = optional(optional($receipt->invoice)->lease)->tenant->TenantName ?? 'N/A';

    $html = "
    <html>
    <head>
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
        <p><strong>Tenant:</strong> {$tenantName}</p>
        <p><strong>Receipt No:</strong> {$receipt->ReferenceNo}</p>
        <p><strong>Invoice No:</strong> " . ($receipt->invoice->InvoiceNumber ?? '-') . "</p>
        <p><strong>Payment Date:</strong> {$receipt->PaymentDate}</p>
        <p><strong>Payment Method:</strong> {$receipt->PaymentMethod}</p>
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
                <tr><td><strong>Total Due</strong></td><td><strong>{$receipt->TotalDue}</strong></td></tr>
                <tr><td>Paid</td><td>{$receipt->Amount}</td></tr>
                <tr><td>Balance</td><td>{$receipt->Balance}</td></tr>
            </tbody>
        </table>
        <p><strong>Remarks:</strong> {$receipt->Remarks}</p>
        <p class='center'>Thank you for your payment.</p>
    </body>
    </html>
    ";

    return response($html)->header('Content-Type', 'text/html');
}
    public function edit($id)
    {
        //Check if user has permission to edit tender categories
         $this->authorize(PermissionEnum::PropertyReceiptUpdate, PropertyReceipt::class);
        $receipts = PropertyReceipt::findOrFail($id);
        $invoices = PropertyInvoice::all();
        return view('property.billingandreceipting.receipting.edit',compact('receipts','invoices'));
    }
     public function update(PropertyReceiptRequest $request, $id){



        $this->authorize(PermissionEnum::PropertyReceiptUpdate, PropertyReceipt::class);
                $validated = $request->validated();
                $TenantId = (int) $validated['TenantId'];
                $InvoiceID = (int) $validated['InvoiceID'];
                $BillingMonth = (int) $validated['BillingMonth'];
                $InvoiceDate = (int) $validated['InvoiceDate'];
                $RentAmount = (int) $validated['RentAmount'];
                $ServicesCharge = (int) $validated['ServicesCharge'];
                $OtherCharges = (int) $validated['OtherCharges'];

        DB::beginTransaction();

        try {
            $receipts = PropertyReceipt::findOrFail($id);

            $receipts->update([
            'TenantId'=> $validated ['TenantId'],
            'InvoiceID' =>$validated ['InvoiceID'],
            'BillingMonth' =>$validated['BillingMonth'],
            'InvoiceDate'   => $validated ['InvoiceDate'],
            'RentAmount' => $validated ['RentAmount'],
            'ServicesCharge' => $validated ['ServicesCharge'],
            'OtherCharges'   => $validated ['OtherCharges'],
            'TotalDue' => $validated ['TotalDue'],
            'AmountPaid' => $validated ['AmountPaid'],
            'Balance' => $validated ['Balance'],
            'PaymentDate' => $validated ['PaymentDate'],
            'Amount' => $validated ['Amount'],
            'PaymentMethod' => $validated ['PaymentMethod'],
            'ReferenceNo' => $validated ['ReferenceNo'],
            'Remarks' => $validated ['Remarks'],
            'ModifiedBy' => Auth::Id(),
            ]);
 
        DB::commit();
        activity()
                ->performedOn($receipts)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Rent Receipt');

                return redirect()->route('rentreceipt.index')->with('success' , 'Rent Receipt updated successfully');
            }catch(\Throwable $th) {
                DB::rollBack();
                Log::error('Failed to Update Rent Receipt:' . $th->getMessage());

                return back()->withErrors(['error'=>'Failed to update Rent Receipts'])->withInput();
            }
       }
       public function destroy($id)
    {
        //Check if user has permission to delete property categories
        $this->authorize(PermissionEnum::PropertyReceiptDelete , PropertyReceipt::class);
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


