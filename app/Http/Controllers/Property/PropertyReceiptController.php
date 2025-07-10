<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Enums\Core\PermissionEnum;
use App\Http\Requests\Property\BillingAndReceipting\PropertyReceiptRequest;
use App\Services\Property\BillingAndReceipting\PropertyReceiptService;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyReceipt;
use App\Models\PropertyManagement\PropertyInvoice;

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
        } catch (\Exception $e) {
            // Redirect back with error message
            return redirect()->back()->with('error', $e->getMessage());
    }

    }
}


