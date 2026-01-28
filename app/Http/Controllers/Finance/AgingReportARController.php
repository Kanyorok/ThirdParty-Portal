<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoice;
use App\Models\ThirdParty\ThirdParties;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AgingReportARController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'customer_id' => ['nullable', 'integer', 'exists:t_ThirdParties,Id'],
        ]);

        $toDate = isset($validated['to_date'])
            ? Carbon::parse($validated['to_date'])->endOfDay()
            : now()->endOfDay();

        $fromDate = isset($validated['from_date'])
            ? Carbon::parse($validated['from_date'])->startOfDay()
            : $toDate->copy()->subDays(30)->startOfDay();

        $customerId = $validated['customer_id'] ?? null;
        $referenceDate = $toDate->toDateString();

        $outstandingExpr = 'COALESCE(t_FinanceInvoices.TotalAmount, 0) - COALESCE(t_FinanceInvoices.AmountPaid, 0)';

        $rows = FinanceInvoice::query()
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 't_FinanceInvoices.CustomerID')
            ->select([
                't_FinanceInvoices.CustomerID',
                DB::raw("COALESCE(tp.ThirdPartyName, 'Unknown Creditor') as customer_name"),
                DB::raw('tp.Email as customer_email'),
                DB::raw('tp.Phone as customer_phone'),
            ])
            ->selectRaw("
                SUM(CASE WHEN DATEDIFF(day, t_FinanceInvoices.DueDate, ?) <= 30 THEN {$outstandingExpr} ELSE 0 END) as bucket_030,
                SUM(CASE WHEN DATEDIFF(day, t_FinanceInvoices.DueDate, ?) BETWEEN 31 AND 60 THEN {$outstandingExpr} ELSE 0 END) as bucket_3160,
                SUM(CASE WHEN DATEDIFF(day, t_FinanceInvoices.DueDate, ?) BETWEEN 61 AND 90 THEN {$outstandingExpr} ELSE 0 END) as bucket_6190,
                SUM(CASE WHEN DATEDIFF(day, t_FinanceInvoices.DueDate, ?) > 90 THEN {$outstandingExpr} ELSE 0 END) as bucket_90p,
                SUM({$outstandingExpr}) as invoice_total
            ", [
                $referenceDate,
                $referenceDate,
                $referenceDate,
                $referenceDate,
            ])
            ->whereBetween('t_FinanceInvoices.DueDate', [$fromDate->toDateString(), $toDate->toDateString()])
            ->when($customerId, fn ($query) => $query->where('t_FinanceInvoices.CustomerID', $customerId))
            ->whereNull('t_FinanceInvoices.DeletedOn')
            ->groupBy(
                't_FinanceInvoices.CustomerID',
                'tp.ThirdPartyName',
                'tp.Email',
                'tp.Phone'
            )
            ->orderBy('tp.ThirdPartyName')
            ->get()
            ->map(function ($row) {
                $row->bucket_030 = (float) $row->bucket_030;
                $row->bucket_3160 = (float) $row->bucket_3160;
                $row->bucket_6190 = (float) $row->bucket_6190;
                $row->bucket_90p = (float) $row->bucket_90p;
                $row->invoice_total = (float) $row->invoice_total;
                $row->total_outstanding = $row->bucket_030 + $row->bucket_3160 + $row->bucket_6190 + $row->bucket_90p;

                return $row;
            });

        $totals = $this->buildTotals($rows);

        $customerDetails = $customerId
            ? ThirdParties::select('Id', 'ThirdPartyName', 'Email', 'Phone')->find($customerId)
            : null;

        $filters = [
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'customer_id' => $customerId,
            'customer_label' => $customerDetails?->ThirdPartyName,
        ];

        return view('finance.accountsreceivable.agingreportar.index', [
            'rows' => $rows,
            'totals' => $totals,
            'filters' => $filters,
            'asOfDate' => $toDate->toDateString(),
            'selectedCustomer' => $customerDetails,
        ]);
    }

    public function show(Request $request, int $customerId)
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $toDate = isset($validated['to_date'])
            ? Carbon::parse($validated['to_date'])->endOfDay()
            : now()->endOfDay();

        $fromDate = isset($validated['from_date'])
            ? Carbon::parse($validated['from_date'])->startOfDay()
            : $toDate->copy()->subDays(30)->startOfDay();

        $customer = ThirdParties::select('Id', 'ThirdPartyName', 'Email', 'Phone')->findOrFail($customerId);

        $invoices = FinanceInvoice::query()
            ->select(['Id', 'InvoiceNumber', 'InvoiceDate', 'DueDate', 'TotalAmount', 'AmountPaid', 'InvoiceRemarks'])
            ->where('CustomerID', $customerId)
            ->whereBetween('DueDate', [$fromDate->toDateString(), $toDate->toDateString()])
            ->whereNull('DeletedOn')
            ->orderBy('DueDate')
            ->get()
            ->map(function ($invoice) use ($toDate) {
                $total = (float) ($invoice->TotalAmount ?? 0);
                $paid = (float) ($invoice->AmountPaid ?? 0);
                $outstanding = max($total - $paid, 0);
                $invoice->OutstandingAmount = $outstanding;

                $dueDate = $invoice->DueDate ? Carbon::parse($invoice->DueDate) : null;
                if ($dueDate) {
                    $diff = $dueDate->diffInDays($toDate, false);
                    $invoice->bucket = $this->resolveBucketLabel($diff);
                    $invoice->daysPastDue = $diff;
                } else {
                    $invoice->bucket = 'N/A';
                    $invoice->daysPastDue = null;
                }

                $invoice->invoice_date_formatted = $invoice->InvoiceDate
                    ? Carbon::parse($invoice->InvoiceDate)->toDateString()
                    : null;
                $invoice->due_date_formatted = $dueDate?->toDateString();

                return $invoice;
            });

        $bucketTotals = [
            '0–30 Days' => $invoices->where('bucket', '0–30 Days')->sum('OutstandingAmount'),
            '31–60 Days' => $invoices->where('bucket', '31–60 Days')->sum('OutstandingAmount'),
            '61–90 Days' => $invoices->where('bucket', '61–90 Days')->sum('OutstandingAmount'),
            '90+ Days' => $invoices->where('bucket', '90+ Days')->sum('OutstandingAmount'),
        ];

        $filters = [
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
        ];

        return view('finance.accountsreceivable.agingreportar.show', [
            'customer' => $customer,
            'invoices' => $invoices,
            'filters' => $filters,
            'bucketTotals' => $bucketTotals,
            'grandTotal' => $invoices->sum('OutstandingAmount'),
        ]);
    }

    public function customerLookup(Request $request)
    {
        $term = trim($request->input('q', ''));

        $customers = ThirdParties::query()
            ->select('Id', 'ThirdPartyName', 'Email', 'Phone')
            ->whereNull('DeletedOn')
            ->when($term, function ($query) use ($term) {
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('ThirdPartyName', 'like', "%{$term}%")
                        ->orWhere('Email', 'like', "%{$term}%")
                        ->orWhere('Phone', 'like', "%{$term}%");
                });
            })
            ->orderBy('ThirdPartyName')
            ->limit(20)
            ->get()
            ->map(function ($customer) {
                $label = $customer->ThirdPartyName ?? 'Unnamed Creditor';
                $meta = collect([$customer->Email, $customer->Phone])
                    ->filter()
                    ->implode(' • ');

                return [
                    'id' => $customer->Id,
                    'text' => $meta ? "{$label} ({$meta})" : $label,
                    'email' => $customer->Email,
                    'phone' => $customer->Phone,
                ];
            });

        return response()->json([
            'results' => $customers,
        ]);
    }

    public function create()
    {
        return view('finance.accountsreceivable.agingreportar.create');
    }

    private function buildTotals(Collection $rows): array
    {
        return [
            'bucket_030' => $rows->sum('bucket_030'),
            'bucket_3160' => $rows->sum('bucket_3160'),
            'bucket_6190' => $rows->sum('bucket_6190'),
            'bucket_90p' => $rows->sum('bucket_90p'),
            'overall' => $rows->sum('total_outstanding'),
        ];
    }

    private function resolveBucketLabel(int $difference): string
    {
        if ($difference <= 30) {
            return '0–30 Days';
        }

        if ($difference >= 31 && $difference <= 60) {
            return '31–60 Days';
        }

        if ($difference >= 61 && $difference <= 90) {
            return '61–90 Days';
        }

        return '90+ Days';
    }
}
