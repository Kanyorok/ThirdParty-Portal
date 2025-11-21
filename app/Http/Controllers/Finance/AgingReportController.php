<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\ThirdParty\ThirdParties;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AgingReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'supplier_id' => ['nullable', 'integer', 'exists:t_ThirdParties,Id'],
        ]);

        $toDate = isset($validated['to_date'])
            ? Carbon::parse($validated['to_date'])->endOfDay()
            : now()->endOfDay();

        $fromDate = isset($validated['from_date'])
            ? Carbon::parse($validated['from_date'])->startOfDay()
            : $toDate->copy()->subDays(30)->startOfDay();

        $supplierId = $validated['supplier_id'] ?? null;
        $referenceDate = $toDate->toDateString();

        $rows = FinanceInvoiceEntry::query()
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 't_FinanceInvoiceEntry.SupplierID')
            ->select([
                't_FinanceInvoiceEntry.SupplierID',
                DB::raw("COALESCE(tp.ThirdPartyName, 'Unknown Supplier') as supplier_name"),
                DB::raw('tp.Email as supplier_email'),
                DB::raw('tp.Phone as supplier_phone'),
            ])
            ->selectRaw("
                SUM(CASE WHEN DATEDIFF(day, t_FinanceInvoiceEntry.DueDate, ?) <= 30 THEN t_FinanceInvoiceEntry.InvoiceAmount ELSE 0 END) as bucket_030,
                SUM(CASE WHEN DATEDIFF(day, t_FinanceInvoiceEntry.DueDate, ?) BETWEEN 31 AND 60 THEN t_FinanceInvoiceEntry.InvoiceAmount ELSE 0 END) as bucket_3160,
                SUM(CASE WHEN DATEDIFF(day, t_FinanceInvoiceEntry.DueDate, ?) BETWEEN 61 AND 90 THEN t_FinanceInvoiceEntry.InvoiceAmount ELSE 0 END) as bucket_6190,
                SUM(CASE WHEN DATEDIFF(day, t_FinanceInvoiceEntry.DueDate, ?) > 90 THEN t_FinanceInvoiceEntry.InvoiceAmount ELSE 0 END) as bucket_90p,
                SUM(t_FinanceInvoiceEntry.InvoiceAmount) as invoice_total
            ", [
                $referenceDate,
                $referenceDate,
                $referenceDate,
                $referenceDate,
            ])
            ->whereBetween('t_FinanceInvoiceEntry.DueDate', [$fromDate->toDateString(), $toDate->toDateString()])
            ->when($supplierId, fn($query) => $query->where('t_FinanceInvoiceEntry.SupplierID', $supplierId))
            ->whereNull('t_FinanceInvoiceEntry.DeletedOn')
            ->groupBy(
                't_FinanceInvoiceEntry.SupplierID',
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

        $supplierDetails = $supplierId
            ? ThirdParties::select('Id', 'ThirdPartyName', 'Email', 'Phone')->find($supplierId)
            : null;

        $filters = [
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'supplier_id' => $supplierId,
            'supplier_label' => $supplierDetails?->ThirdPartyName,
        ];

        return view('finance.accountspayable.agingreport.index', [
            'rows' => $rows,
            'totals' => $totals,
            'filters' => $filters,
            'asOfDate' => $toDate->toDateString(),
            'selectedSupplier' => $supplierDetails,
        ]);
    }

    public function show(Request $request, int $supplierId)
    {
        $validated = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
        ]);

        $toDate = Carbon::parse($validated['to_date'])->endOfDay();
        $fromDate = Carbon::parse($validated['from_date'])->startOfDay();

        $supplier = ThirdParties::select('Id', 'ThirdPartyName', 'Email', 'Phone')->findOrFail($supplierId);

        $invoices = FinanceInvoiceEntry::query()
            ->select(['Id', 'InvoiceNumber', 'InvoiceDate', 'DueDate', 'InvoiceAmount', 'Description'])
            ->where('SupplierID', $supplierId)
            ->whereBetween('DueDate', [$fromDate->toDateString(), $toDate->toDateString()])
            ->whereNull('DeletedOn')
            ->orderBy('DueDate')
            ->get()
            ->map(function ($invoice) use ($toDate) {
                $invoice->InvoiceAmount = (float) $invoice->InvoiceAmount;
                $invoiceDate = $invoice->InvoiceDate ? Carbon::parse($invoice->InvoiceDate) : null;
                $dueDate = $invoice->DueDate ? Carbon::parse($invoice->DueDate) : null;

                if ($dueDate) {
                    $diff = $dueDate->diffInDays($toDate, false);
                    $invoice->bucket = $this->resolveBucketLabel($diff);
                    $invoice->daysPastDue = $diff;
                } else {
                    $invoice->bucket = 'N/A';
                    $invoice->daysPastDue = null;
                }

                $invoice->invoice_date_formatted = $invoiceDate?->toDateString();
                $invoice->due_date_formatted = $dueDate?->toDateString();
                return $invoice;
            });

        $bucketTotals = [
            '0–30 Days' => $invoices->where('bucket', '0–30 Days')->sum('InvoiceAmount'),
            '31–60 Days' => $invoices->where('bucket', '31–60 Days')->sum('InvoiceAmount'),
            '61–90 Days' => $invoices->where('bucket', '61–90 Days')->sum('InvoiceAmount'),
            '90+ Days' => $invoices->where('bucket', '90+ Days')->sum('InvoiceAmount'),
        ];

        $filters = [
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
        ];

        return view('finance.accountspayable.agingreport.show', [
            'supplier' => $supplier,
            'invoices' => $invoices,
            'filters' => $filters,
            'bucketTotals' => $bucketTotals,
            'grandTotal' => $invoices->sum('InvoiceAmount'),
        ]);
    }

    public function create()
    {
        return view('finance.accountspayable.agingreport.create');
    }

    public function supplierLookup(Request $request)
    {
        $term = trim($request->input('q', ''));

        $suppliers = ThirdParties::query()
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
            ->map(function ($supplier) {
                $label = $supplier->ThirdPartyName ?? 'Unnamed Supplier';
                $meta = collect([$supplier->Email, $supplier->Phone])
                    ->filter()
                    ->implode(' • ');

                return [
                    'id' => $supplier->Id,
                    'text' => $meta ? "{$label} ({$meta})" : $label,
                    'email' => $supplier->Email,
                    'phone' => $supplier->Phone,
                ];
            });

        return response()->json([
            'results' => $suppliers,
        ]);
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
