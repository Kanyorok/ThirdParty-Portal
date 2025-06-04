<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
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
        $invoices = PropertyInvoice::all();
        return view('property.billingandreceipting.receipting.create', compact('invoices'));
    }
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'InvoiceID'=>'required|string|max:50',
            'BillingMonth'=>'required|string|max:50',
            'InvoiceDate'=>'required|date',
            'RentAmount'=>'required|integer',
            'TotalDue'=>'required|integer',
            'AmountPaid'=>'required|integer',
            'Balance'=>'required|integer',
            'PaymentDate'=>'required|date', 
            'Amount'=>'required|integer',
            'PaymentMethod'=>'required|string|max:100',
            'ReferenceNo'=>'required|string|max:100',
            'Remarks'=>'required|string|max:100',
        ]);
           //dd('validation');
         $receipt = PropertyReceipt::create([
            'InvoiceID'=> $request->InvoiceID,
            'BillingMonth'=> $request->BillingMonth,
            'InvoiceDate'=> $request->InvoiceDate,
            'RentAmount'=> $request->RentAmount,
            'TotalDue'=> $request->TotalDue,
            'AmountPaid'=> $request->AmountPaid,
            'Balance'=> $request->Balance,
            'PaymentDate'=> $request->PaymentDate,
            'Amount'=> $request->Amount,
            'PaymentMethod'=> $request->PaymentMethod,
            'ReferenceNo'=> $request->ReferenceNo,
            'Remarks'=> $request->Remarks,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        //dd('Validation');
           return redirect()->route('rentreceipt.index')->with('success','Rent receipt created successfully');
    }

}
