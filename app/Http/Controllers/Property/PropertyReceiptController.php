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
            $InvoiceID = (int)$validated['InvoiceID'];
            $BillingMonth = (int)$validated['BillingMonth'];
            $InvoiceDate = (int)$validated['InvoiceDate'];
            $RentAmount = (int)$validated['RentAmount'];
            $ServicesCharge = (int)$validated['ServicesCharge'];
            $OtherCharges = (int)$validated['OtherCharges'];
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
                $validated['AmountPaid'],
                $validated['Balance'],
                $validated['PaymentDate'],
                $validated ['Amount'],
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


