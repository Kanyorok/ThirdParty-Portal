<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyNewLease;


class PropertyInvoiceController extends Controller
{
    //
    public function index()
    {
        $invoices = PropertyInvoice::all();
        return view('property.billingandreceipting.invoicing.index', compact('invoices'));
    }

    public function create(){
        $newleases = PropertyNewLease::all();
        return view('property.billingandreceipting.invoicing.create', compact('newleases'));
    }

    public function show($id)
    {
        $invoice = PropertyInvoice::find($id);
        return view('property.billingandreceipting.invoicing.show', compact('invoice'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'Lease' => 'required|string|max:50',
            'BillingMonth' => 'required|string|max:50',
            'InvoiceDate' => 'required|date',
            'RentAmount' => 'required|integer',
            'ServicesCharge' => 'required|integer',
            'OtherCharges' => 'required|integer',
            'InvoiceNotes' => 'nullable|string|max:100',
        ]);
        //dd('validation');
        $invoice = PropertyInvoice::create([
            'Lease' => $request->Lease,
            'BillingMonth' => $request->BillingMonth,
            'InvoiceDate' => $request->InvoiceDate,
            'RentAmount' => $request->RentAmount,
            'ServicesCharge' => $request->ServicesCharge,
            'OtherCharges' => $request->OtherCharges,
            'InvoiceNotes' => $request->InvoiceNotes,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        return redirect()->route('rentinvoice.index')->with('success', 'Invoice created successfully');
    }

}
