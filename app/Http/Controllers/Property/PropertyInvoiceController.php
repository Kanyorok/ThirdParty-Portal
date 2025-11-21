<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Enums\Property\PropertyInvoiceEnum;
use App\Enums\Property\PropertyNewLeaseEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\BillingAndReceipting\PropertyInvoiceRequest;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
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
        $this->authorize(PermissionEnum::PropertyInvoiceView, PropertyInvoice::class);
        $invoices = PropertyInvoice::all();
        return view('property.billingandreceipting.invoicing.index', compact('invoices'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyInvoiceCreate, PropertyInvoice::class);

        $newleases = PropertyNewLease::where('IsActive', true)
            ->where('Status', '!=', PropertyNewLeaseEnum::Terminate)
            ->with('tenant')
            ->get();

        // 👇 Add currencies and tax types from database
        $currencies = Currency::all();
        $taxTypes = FinanceTaxRuleConfiguration::all();

        return view('property.billingandreceipting.invoicing.create', compact('newleases', 'currencies', 'taxTypes'));
    }


public function show($id)
{
    $this->authorize(PermissionEnum::PropertyInvoiceView, PropertyInvoice::class);

    $invoice = PropertyInvoice::with([
        'lease.tenant.thirdParty',
        'currency',
        'tax',
        'createdByUser',
        'modifiedByUser'
    ])->findOrFail($id);

    return view('property.billingandreceipting.invoicing.show', compact('invoice'));
}

public function store(PropertyInvoiceRequest $request)
{
    $this->authorize(PermissionEnum::PropertyInvoiceCreate, PropertyInvoice::class);
    $validated = $request->validated();

    $Lease = PropertyNewLease::findOrFail($validated['Lease']);

    // Use Rent currency/tax as the main invoice reference
    $Currency = Currency::findOrFail($validated['Currency']);
    $Tax = FinanceTaxRuleConfiguration::findOrFail($validated['Tax']);
    $Status = PropertyInvoiceEnum::Pending;

    PropertyInvoiceService::create(
        $Lease,
        $validated['BillingMonth'],
        $validated['InvoiceDate'],
        $validated['RentAmount'],
        $validated['ServicesCharge'] ?? 0,
        $validated['OtherCharges'] ?? 0,
        $validated['ParkingFee'] ?? 0,
        $validated['InvoiceNotes'] ?? '',
        $validated['Description'] ?? null, 
        $validated['DescriptionRent'] ?? null,
        $validated['DescriptionService'] ?? null,
        $validated['DescriptionParking'] ?? null,
        $validated['DescriptionOther'] ?? null,
        $Currency,
        $Tax,
        $Status,
        Auth::user()
    );

    return redirect()->route('rentinvoice.index')->with('success', 'Invoice created successfully');
}


    // public function edit($id)
    // {
    //     $this->authorize(PermissionEnum::PropertyInvoiceUpdate, PropertyInvoice::class);

    //     $invoice = PropertyInvoice::with(['lease.tenant.thirdParty'])->findOrFail($id);

    //     $leases = PropertyNewLease::where('IsActive', true)
    //         ->where('Status', '!=', PropertyNewLeaseEnum::Terminate)
    //         ->with('tenant.thirdParty')
    //         ->orderBy('LeaseNumber')
    //         ->get();

    //     $currencies = Currency::orderBy('Code')->get();
    //     $taxTypes = FinanceTaxRuleConfiguration::with('taxType')->get();

    //     return view(
    //         'property.billingandreceipting.invoicing.edit',
    //         compact('invoice', 'leases', 'currencies', 'taxTypes')
    //     );
    // }


    // public function update(PropertyInvoiceRequest $request, $id)
    // {
    //     $this->authorize(PermissionEnum::PropertyInvoiceUpdate, PropertyInvoice::class);
    //     $validated = $request->validated();

    //     DB::beginTransaction();

    //     try {
    //         $invoice = PropertyInvoice::findOrFail($id);
    //         $Lease = PropertyNewLease::findOrFail($validated['Lease']);

    //         $invoice->update([
    //             'Lease' => $Lease->Id,
    //             'BillingMonth' => $validated['BillingMonth'],
    //             'InvoiceDate' => $validated['InvoiceDate'],
    //             'RentAmount' => $validated['RentAmount'],
    //             'ServicesCharge' => $validated['ServicesCharge'] ?? 0,
    //             'ParkingFee' => $validated['ParkingFee'] ?? 0,
    //             'OtherCharges' => $validated['OtherCharges'] ?? 0,
    //             'InvoiceNotes' => $validated['InvoiceNotes'] ?? '',
    //             'ModifiedBy' => Auth::id(),
    //         ]);

    //         DB::commit();

    //         activity()
    //             ->performedOn($invoice)
    //             ->causedBy(Auth::user())
    //             ->withProperties(['action' => 'update'])
    //             ->log('Updated Invoice Details');

    //         return redirect()->route('rentinvoice.index')
    //             ->with('success', 'Invoice updated successfully');

    //     } catch (Throwable $th) {
    //         DB::rollBack();
    //         return back()
    //             ->withErrors(['error' => $th->getMessage()])
    //             ->withInput();
    //     }
    // }


    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PropertyInvoiceDelete, PropertyInvoice::class);
        try {

            $invoice = PropertyInvoice::findOrFail($id);

            if ($invoice->receipts()->exists()) {
                return redirect()->back()
                    ->withErrors(['error' => 'This Invoice is in use and cannot be deleted.']);
            }

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
