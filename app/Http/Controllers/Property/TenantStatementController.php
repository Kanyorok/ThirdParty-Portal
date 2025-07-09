<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use App\Models\PropertyManagement\TenantLedger;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

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
            $invoices = PropertyInvoice::where('TenantId', $tenantId)
                ->whereBetween('InvoiceDate', values: [$fromDate, $toDate])
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
                    'description' => 'Invoice - ' . ($invoice->InvoiceNotes ?? 'Rent'),
                    'type' => 'debit',
                    'amount' => $invoice->RentAmount,
                ]);
            }

            // Format receipt entries
            foreach ($receipts as $receipt) {
                $ledger->push([
                    'date' => $receipt->PaymentDate,
                    'reference' => $receipt->ReferenceNo ?? '-',
                    'description' => 'Payment - ' . $receipt->PaymentMethod,
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
    public function exportPdf(Request $request)
{
    $tenantId = $request->tenant_id;
    $from = $request->from;
    $to = $request->to;

    if (!$tenantId || !$from || !$to) {
        return redirect()->back()->with('error', 'Please provide tenant and date range.');
    }

    $lease = PropertyNewLease::with('tenant')->findOrFail($tenantId);
    $ledger = $this->generateLedger($tenantId, $from, $to);

    $balance = 0;
    $totalDebit = 0;
    $totalCredit = 0;
    $rows = '';

    foreach ($ledger as $entry) {
        $debit = $entry['type'] === 'debit' ? $entry['amount'] : 0;
        $credit = $entry['type'] === 'credit' ? $entry['amount'] : 0;
        $balance += $debit - $credit;
        $totalDebit += $debit;
        $totalCredit += $credit;

        $rows .= '<tr>
                    <td>' . e($entry['date']) . '</td>
                    <td>' . e($entry['reference']) . '</td>
                    <td>' . e($entry['description']) . '</td>
                    <td style="text-align:right">' . ($debit > 0 ? number_format($debit, 2) : '-') . '</td>
                    <td style="text-align:right">' . ($credit > 0 ? number_format($credit, 2) : '-') . '</td>
                    <td style="text-align:right">' . number_format($balance, 2) . '</td>
                </tr>';
    }

    $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #000; padding: 5px; }
                th { background: #eee; }
                .text-end { text-align: right; }
            </style>
        </head>
        <body>
            <h2 style="text-align:center;">Tenant Ledger Statement</h2>
            <p><strong>Tenant:</strong> ' . e($lease->tenant->TenantName) . '<br>
               <strong>Lease:</strong> ' . e($lease->LeaseNumber ?? '-') . '<br>
               <strong>Period:</strong> ' . e($from) . ' to ' . e($to) . '</p>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th>Debit (KES)</th>
                        <th>Credit (KES)</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>' . $rows . '</tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-end">' . number_format($totalDebit, 2) . '</th>
                        <th class="text-end">' . number_format($totalCredit, 2) . '</th>
                        <th class="text-end">' . number_format($balance, 2) . '</th>
                    </tr>
                </tfoot>
            </table>
        </body>
        </html>';

    $pdf = Pdf::loadHTML($html);
    return $pdf->download('Tenant_Ledger_' . $lease->tenant->TenantName . '.pdf');
}
private function generateLedger($tenantId, $from, $to)
{
    $fromDate = Carbon::parse($from)->startOfDay();
    $toDate = Carbon::parse($to)->endOfDay();

    $ledger = collect();

    $invoices = PropertyInvoice::where('TenantId', $tenantId)
        ->whereBetween('InvoiceDate', [$fromDate, $toDate])
        ->get();

    $receipts = PropertyReceipt::where('TenantId', $tenantId)
        ->whereBetween('PaymentDate', [$fromDate, $toDate])
        ->get();

    foreach ($invoices as $invoice) {
        $ledger->push([
            'date' => $invoice->InvoiceDate,
            'reference' => $invoice->InvoiceNumber,
            'description' => 'Invoice - ' . ($invoice->InvoiceNotes ?? 'Rent'),
            'type' => 'debit',
            'amount' => $invoice->RentAmount,
        ]);
    }

    foreach ($receipts as $receipt) {
        $ledger->push([
            'date' => $receipt->PaymentDate,
            'reference' => $receipt->ReferenceNo ?? '-',
            'description' => 'Payment - ' . $receipt->PaymentMethod,
            'type' => 'credit',
            'amount' => $receipt->Amount,
        ]);
    }

    return $ledger->sortBy('date')->values()->toArray();
}
}
