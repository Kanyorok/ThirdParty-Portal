<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoice;
use App\Models\PropertyManagement\PropertyNewTenant;
use Illuminate\Http\Request;

class ReceiptsPostingController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.receiptsposting.index');
    }

    public function create(){
        return view('finance.accountsreceivable.receiptsposting.create');
    }

    public function show($id)
    {
        return view('finance.accountsreceivable.receiptsposting.show');
    }

    /**
     * AJAX: Find customer + pending invoices
     */
    public function findCustomer(Request $request)
    {
        $request->validate([
            'id_number' => 'required|string'
        ]);

        $customer = PropertyNewTenant::where('IDRegistrationNo', $request->id_number)->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $invoices = FinanceInvoice::with('currency')
            ->where('CustomerID', $customer->Id)
            ->where('IsPaid', false)
            ->whereColumn('TotalAmount', '>', 'AmountPaid')
            ->orderBy('InvoiceDate')
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->Id,
                    'number' => $inv->InvoiceNumber,
                    'issue_date' => $inv->InvoiceDate->format('Y-m-d'),
                    'due_date' => $inv->DueDate->format('Y-m-d'),
                    'currency' => [
                        'code' => $inv->currency->Code,
                        'symbol' => $inv->currency->Symbol
                    ],
                    'total' => (float)$inv->TotalAmount,
                    'paid' => (float)$inv->AmountPaid,
                ];
            });

        return response()->json([
            'customer' => [
                'id' => $customer->Id,
                'name' => $customer->TenantName,
                'id_number' => $customer->IDRegistrationNo,
                'email' => $customer->EmailAddress,
                'phone' => $customer->PhoneNumber,
                'status' => $customer->IsActive ? 'Active' : 'Inactive',
                'currency' => ['code' => $invoices->first()?->currency['code'] ?? 'KES',
                    'symbol' => $invoices->first()?->currency['symbol'] ?? 'KSh']
            ],
            'invoices' => $invoices
        ]);
    }

    /**
     * Store a new receipt
     */
    public function store(Request $request)
    {
        $request->validate([
            'CustomerId' => 'required|integer|exists:Customer,Id',
            'AmountReceived' => 'required|numeric|min:0.01',
            'PaymentMethod' => 'required|string',
            'ReferenceNo' => 'nullable|string|max:100',
            'ValueDate' => 'required|date',
            'PostingDate' => 'required|date',
            'Remarks' => 'nullable|string',
            'Allocations' => 'nullable|json'
        ]);

        DB::transaction(function () use ($request) {
            // 1. Save receipt
            $receipt = new Receipt();
            $receipt->CustomerID = $request->CustomerId;
            $receipt->AmountReceived = $request->AmountReceived;
            $receipt->PaymentMethod = $request->PaymentMethod;
            $receipt->ReferenceNo = $request->ReferenceNo;
            $receipt->ValueDate = $request->ValueDate;
            $receipt->PostingDate = $request->PostingDate;
            $receipt->Remarks = $request->Remarks;
            $receipt->CreatedBy = auth()->id();
            $receipt->save();

            $allocations = json_decode($request->Allocations, true) ?? [];

            // 2. Loop allocations
            foreach ($allocations as $alloc) {
                $invoice = FinanceInvoice::find($alloc['invoice_id']);
                if (!$invoice) continue;

                $amount = min($alloc['allocate'], ($invoice->TotalAmount - $invoice->AmountPaid));

                $allocation = new ReceiptAllocation();
                $allocation->ReceiptID = $receipt->Id;
                $allocation->InvoiceID = $invoice->Id;
                $allocation->Amount = $amount;
                $allocation->save();

                // 3. Update invoice paid amounts
                $invoice->AmountPaid += $amount;
                if ($invoice->AmountPaid >= $invoice->TotalAmount) {
                    $invoice->IsPaid = true;
                }
                $invoice->save();
            }

            // 4. If unapplied and create credit
            $applied = array_sum(array_column($allocations, 'allocate'));
            $unapplied = $request->AmountReceived - $applied;
            if ($unapplied > 0 && $request->has('CreateCredit')) {
                // You may insert into a CustomerCredit table
                DB::table('CustomerCredit')->insert([
                    'CustomerID' => $request->CustomerId,
                    'Amount' => $unapplied,
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                ]);
            }
        });

        return redirect()->route('receiptsposting.index')
            ->with('success', 'Receipt successfully recorded.');
    }
}
