<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\BillingAndReceipting\PropertyInvoiceRequest;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Services\Property\BillingAndReceipting\PropertyInvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PropertyInvoiceController extends Controller
{
    //
    public function index()
    {
        $invoices = PropertyInvoice::all();
        return view('property.billingandreceipting.invoicing.index', compact('invoices'));
    }

    public function create(){
        $this->authorize(PermissionEnum::PropertyInvoiceCreate, PropertyInvoice::class);
        $newleases = PropertyNewLease::all();
        $newtenants = PropertyNewLease::all();
        return view('property.billingandreceipting.invoicing.create', compact('newleases', 'newtenants'));
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::PropertyInvoiceView, PropertyInvoice::class);
        $invoice = PropertyInvoice::find($id);
        return view('property.billingandreceipting.invoicing.show', compact('invoice'));
    }

    public function store(PropertyInvoiceRequest $request)
    {
        //dd($request->all());
        $this->authorize(PermissionEnum::PropertyInvoiceCreate, PropertyInvoice::class);
        $validated = $request->validated();

        $Lease = PropertyNewLease::findOrFail($validated['Lease']);
        //dd('validation');
        PropertyInvoiceService::create(
            $Lease,
            $validated['BillingMonth'],
            $validated['InvoiceDate'],
            $validated['RentAmount'],
            $validated['ServicesCharge'],
            $validated['OtherCharges'],
            $validated['InvoiceNotes'],
            Auth::user()
        );

        return redirect()->route('rentinvoice.index')->with('success', 'Invoice created successfully');
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::PropertyInvoiceUpdate, PropertyInvoice::class);
        $invoices = PropertyInvoice::findOrFail($id);
        $newtenants = PropertyNewLease::all();

        return view('property.billingandreceipting.invoicing.edit', compact('invoices', 'newtenants'));
    }

    public function update(PropertyInvoiceRequest $request, $id)
    {
        $this->authorize(PermissionEnum::PropertyInvoiceUpdate, PropertyInvoice::class);
        $validated = $request->validated();

        $Lease = PropertyNewLease::findOrFail($validated['Lease']);

        DB::beginTransaction();

        try {
            $invoice = PropertyInvoice::findOrFail($id);

            $invoice->update([
                'Lease' => $validated['Lease'],
                'BillingMonth' => $validated['BillingMonth'],
                'InvoiceDate' => $validated['InvoiceDate'],
                'RentAmount' => $validated['RentAmount'],
                'ServicesCharge' => $validated['ServicesCharge'],
                'OtherCharges' => $validated['OtherCharges'],
                'InvoiceNotes' => $validated['InvoiceNotes'],
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($invoice)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Invoice  Details');

            return redirect()->route('rentinvoice.index')->with('success', 'Invoice updated successfully');
        } catch (Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['error' => $th->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyInvoiceDelete, PropertyInvoice::class);
        try {
            $invoice = PropertyInvoice::findOrFail($id);
            $invoice->delete();

            return redirect()->route('rentinvoice.index')
                ->with('success', 'Invoice Deleted Successfully!');
        } catch (Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting invoice: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete invoice. Please try again.'])
                ->withInput();
        }
    }

}
