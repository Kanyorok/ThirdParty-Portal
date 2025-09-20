<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoice;
use App\Models\ThirdParty\ThirdParties;
use App\Models\Core\CodeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReceiptsPostingController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.receiptsposting.index');
    }

    public function create(){
        $paymentMethods = CodeDetail::where('CodeID', 'PaymentMethod')
            ->orderBy('Description')
            ->get(['ID','Value','Description']);
        return view('finance.accountsreceivable.receiptsposting.create', compact('paymentMethods'));
    }

    public function show($id){
        return view('finance.accountsreceivable.receiptsposting.show');
    }

    /**
     * AJAX: Find customer + pending invoices
     */
    public function findCustomer(Request $request)
    {
        $request->validate([
            'id_number' => 'required|string|min:2'
        ]);

        $q = trim((string) $request->id_number);
        $customer = ThirdParties::query()
            ->where('RegistrationNumber', $q)
            ->orWhere('TaxPIN', $q)
            ->orWhere('Email', $q)
            ->orWhere('Phone', $q)
            ->orWhere('ThirdPartyName', 'like', "%{$q}%")
            ->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $invoices = FinanceInvoice::with('currency')
            ->where('CustomerID', $customer->Id)
            ->where('IsPaid', false)
            ->where('ApprovalStatus', 'posted')
            ->whereColumn('TotalAmount', '>', 'AmountPaid')
            ->orderBy('InvoiceDate')
            ->get()
            ->map(function ($inv) {
                return [
                    'id'        => $inv->Id,
                    'number'    => $inv->InvoiceNumber,
                    'issue_date'=> optional($inv->InvoiceDate)->format('Y-m-d'),
                    'due_date'  => optional($inv->DueDate)->format('Y-m-d'),
                    'currency'  => [
                        'code'   => $inv->currency->Code ?? null,
                        'symbol' => $inv->currency->Symbol ?? null
                    ],
                    'total'     => (float) ($inv->TotalAmount ?? 0),
                    'paid'      => (float) ($inv->AmountPaid ?? 0),
                ];
            });

        if ($invoices->isEmpty()) {
            return response()->json(['error' => 'No invoices found for this customer'], 404);
        }


        $status = is_object($customer->Status ?? null) && method_exists($customer->Status, 'label')
            ? $customer->Status->label()
            : ((string) ($customer->Status ?? ''));

        return response()->json([
            'customer' => [
                'id'        => $customer->Id,
                'name'      => $customer->ThirdPartyName,
                'id_number' => $customer->RegistrationNumber ?? $customer->TaxPIN ?? $q,
                'email'     => $customer->Email,
                'phone'     => $customer->Phone,
                'status'    => $status ?: '—',
                'currency'  => [
                    'code'   => $invoices->first()?->currency['code'] ?? 'KES',
                    'symbol' => $invoices->first()?->currency['symbol'] ?? 'KSh'
                ]
            ],
            'invoices' => $invoices
        ]);
    }

    /**
     * Store a new receipt
     */
    public function store(Request $request)
    {
        // Not implemented in this iteration; search/display uses ThirdParties and pending invoices at create page
        return back()->with('info', 'Receipt posting is not implemented yet in this module.');
    }
}
