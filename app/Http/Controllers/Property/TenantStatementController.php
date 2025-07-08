<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use Carbon\Carbon;

class TenantStatementController extends Controller
{
    public function index(Request $request)
    {
        // Get tenants with their leases
        $newleases = PropertyNewLease::with('tenant')->get();

        // Initialize empty ledger
        $ledger = collect();

        // Read filters from request (match your form names exactly)
        $tenantId = $request->tenant_id;
        $from = $request->from;
        $to = $request->to;

        // Only process if all filters are provided
        if ($tenantId && $from && $to) {
            $fromDate = Carbon::parse($from)->startOfDay();
            $toDate = Carbon::parse($to)->endOfDay();

            // Get invoices within the date range for the tenant
            $invoices = PropertyInvoice::where('InvoiceNumber', $InvoiceNumber)
                ->whereBetween('InvoiceDate', [$fromDate, $toDate])
                ->get();

            // Get receipts within the date range for the tenant
            $receipts = PropertyReceipt::where('TenantId', $tenantId)
                ->whereBetween('PaymentDate', [$fromDate, $toDate])
                ->get();

            // Format invoice entries
            foreach ($invoices as $invoice) {
                $ledger->push([
                    'date' => $invoice->InvoiceDate,
                    'reference' => $invoice->InvoiceNumber,
                    'description' => 'Invoice - ' . ($invoice->Description ?? 'Rent'),
                    'type' => 'debit',
                    'amount' => $invoice->TotalAmount,
                ]);
            }

            // Format receipt entries
            foreach ($receipts as $receipt) {
                $ledger->push([
                    'date' => $receipt->PaymentDate,
                    'reference' => $receipt->ReceiptNumber ?? '-',
                    'description' => 'Payment - ' . $receipt->ReferenceNo,
                    'type' => 'credit',
                    'amount' => $receipt->Amount,
                ]);
            }

            // Sort all ledger items by date
            $ledger = $ledger->sortBy('date')->values();

            // Add running balance
            $runningBalance = 0;
            $ledger = $ledger->map(function ($entry) use (&$runningBalance) {
                $debit = $entry['type'] === 'debit' ? $entry['amount'] : 0;
                $credit = $entry['type'] === 'credit' ? $entry['amount'] : 0;
                $runningBalance += $debit - $credit;
                $entry['balance'] = $runningBalance;
                return $entry;
            });
        }

        // Return the view with data
        return view('property.billingandreceipting.tenantledger.index', compact('newleases', 'ledger'));
    }
}
