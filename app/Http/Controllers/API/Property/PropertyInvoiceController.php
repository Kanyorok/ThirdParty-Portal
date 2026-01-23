<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Http\Resources\Property\PropertyInvoiceCollection;
use App\Http\Resources\Property\PropertyInvoiceResource;
use App\Models\PropertyManagement\PropertyInvoice;
use Illuminate\Http\Request;

class PropertyInvoiceController extends Controller
{
    public function index(Request $request): PropertyInvoiceCollection
    {
        $request->validate([
            'tenant_id' => 'required|integer',
        ]);

        $tenantId = $request->query('tenant_id');

        $invoices = PropertyInvoice::with([
            'lease',
            'currency',
            'tax',
            'createdByUser',
            'modifiedByUser'
        ])
        ->whereHas('lease', function ($q) use ($tenantId) {
            $q->where('Tenant', $tenantId);
        })
        ->latest('Id')
        ->paginate(10);

        return new PropertyInvoiceCollection($invoices);
    }

    public function show(Request $request): PropertyInvoiceResource
    {
        $request->validate([
            'tenant_id'  => 'required|integer',
            'invoice_id' => 'required|integer',
        ]);

        $tenantId  = $request->query('tenant_id');
        $invoiceId = $request->query('invoice_id');

        $invoice = PropertyInvoice::with([
            'lease',
            'currency',
            'tax',
            'createdByUser',
            'modifiedByUser'
        ])
        ->where('Id', $invoiceId)
        ->whereHas('lease', fn ($q) => $q->where('Tenant', $tenantId))
        ->firstOrFail();

        return new PropertyInvoiceResource($invoice);
    }
}
