<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoice;
use Illuminate\Http\Request;

class InvoiceGenerationController extends Controller
{

    public function index()
    {
        $invoices = FinanceInvoice::select([
            'Id',
            'RequestID',
            'InvoiceNumber',
            'InvoiceTitle',
            'CustomerID',
            'SourceTable',
            'CurrencyID',
            'ModuleID',
            'TotalAmount',
            'DueDate',
            'Status',
            'ApprovalStatus',
        ])
            ->with([
                'customer:Id,TenantName',
                'source:ModuleID,Name',
                'currency:Id,Code'
            ])
            ->orderByDesc('CreatedOn')
            ->paginate(15);

        return view('finance.accountsreceivable.invoicegeneration.index', compact('invoices'));
    }

    public function create(){
        return view('finance.accountsreceivable.invoicegeneration.create');
    }

    public function show($id)
    {
        $invoice = FinanceInvoice::with([
            'customer:Id,TenantName,PhoneNumber,EmailAddress,PostalAddress,IDRegistrationNo,Nationality',
            'source:ModuleID,Name',
            'currency:Id,Code,Symbol,Name',
            'lines:Id,InvoiceID,InvoiceLineName,Description,UnitCost,Quantity,Tax,TaxAmount,Discount,Total'
        ])->findOrFail($id);

        return view('finance.accountsreceivable.invoicegeneration.show', compact('invoice'));
    }
}
