<?php

namespace App\Http\Controllers\API\Property;

use App\Http\Controllers\Controller;
use App\Http\Resources\Property\PropertyInvoiceCollection;
use App\Http\Resources\Property\PropertyInvoiceResource;
use App\Models\PropertyManagement\PropertyInvoice;

class PropertyInvoiceController extends Controller
{
    public function index(): PropertyInvoiceCollection
    {
        $invoices = PropertyInvoice::with([
            'lease',
            'currency',
            'tax',
            'createdByUser',
            'modifiedByUser'
        ])
        ->latest('Id')
        ->paginate(10);

        return new PropertyInvoiceCollection($invoices);
    }

    public function show($id): PropertyInvoiceResource
    {
        $invoice = PropertyInvoice::with([
            'lease',
            'currency',
            'tax',
            'createdByUser',
            'modifiedByUser'
        ])->findOrFail($id);

        return new PropertyInvoiceResource($invoice);
    }
}
