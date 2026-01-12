<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyNewTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RentDashboardController extends Controller
{

    public function index(Request $request)
    {
        $query = PropertyInvoice::with(['receipts', 'lease.property', 'lease.unit']);

        if ($request->filled('property_id')) {
            $query->whereHas('lease.property', function ($q) use ($request) {
                $q->where('Id', $request->property_id);
            });
        }

        if ($request->filled('tenant_id')) {
            $query->whereHas('lease.tenant', function ($q) use ($request) {
                $q->where('id', $request->tenant_id);
            });
        }

        if ($request->filled('billing_month')) {
            $query->where('BillingMonth', $request->billing_month);
        }

        $invoices = $query->get();

        // Map Finance invoices by RequestID (no Finance code changes; Property-side reads)
        $requestIds = $invoices->pluck('RequestID')->filter()->unique()->values();
        $financeByReq = collect();
        if ($requestIds->isNotEmpty()) {
            $financeByReq = collect(DB::table('t_FinanceInvoices')
                ->whereIn('RequestID', $requestIds)
                ->get())->keyBy('RequestID');
        }

        // Derive due/paid/status per Property invoice from Finance amounts
        $invoices->each(function ($inv) use ($financeByReq) {
            $due = (float)($inv->RentAmount ?? 0)
                + (float)($inv->ServicesCharge ?? 0)
                + (float)($inv->ParkingFee ?? 0)
                + (float)($inv->OtherCharges ?? 0);

            $fin = $inv->RequestID ? $financeByReq->get($inv->RequestID) : null;
            $paid = $fin ? (float)($fin->AmountPaid ?? 0) : 0.0;

            $inv->DerivedDue = $due;
            $inv->DerivedPaid = $paid;
            $inv->DerivedStatus = $due <= 0 ? 'Pending'
                : ($paid >= $due ? 'Fully Paid' : ($paid > 0 ? 'Partial Paid' : 'Pending'));
        });

        // --- Group by month (e.g., "2025-01") ---
        $invoiceByMonth = $invoices->groupBy(function ($inv) {
            return Carbon::parse($inv->InvoiceDate)->format('Y-m');
        })->map(function ($group) {
            return $group->sum(function ($inv) {
                return ($inv->RentAmount ?? 0) + ($inv->ServicesCharge ?? 0) + ($inv->ParkingFee ?? 0) + ($inv->OtherCharges ?? 0);
            });
        });

        // Collections from Finance receipt allocations for these invoices
        $financeIds = $financeByReq->pluck('Id')->filter()->values();
        $allocations = collect();
        if ($financeIds->isNotEmpty()) {
            $allocations = collect(DB::table('t_FinanceReceiptAllocations as a')
                ->join('t_FinanceReceipts as r', 'r.Id', '=', 'a.ReceiptID')
                ->whereIn('a.InvoiceID', $financeIds)
                ->select('r.ReceiptDate', 'a.AmountAllocated')
                ->get());
        }

        $receiptByMonth = $allocations
            ->groupBy(function ($row) {
                return Carbon::parse($row->ReceiptDate)->format('Y-m');
            })
            ->map(function ($group) {
                return (float)collect($group)->sum('AmountAllocated');
            });

        // --- Merge both monthly keys to cover all months ---
        $allMonths = $invoiceByMonth->keys()->merge($receiptByMonth->keys())->unique()->sort();

        $chartData = $allMonths->mapWithKeys(function ($month) use ($invoiceByMonth, $receiptByMonth) {
            return [
                $month => [
                    'invoiced' => $invoiceByMonth->get($month, 0),
                    'collected' => $receiptByMonth->get($month, 0),
                ]
            ];
        });

        // Summary calculations (Finance-driven)
        $collected = (float)$allocations->sum('AmountAllocated');
        $dueSoon = 0; // Optional: compute based on due dates
        $overdue = $invoices->sum(function ($inv) {
            $diff = ($inv->DerivedDue ?? 0) - ($inv->DerivedPaid ?? 0);
            return $diff > 0 ? $diff : 0;
        });

        $partial = $invoices->sum(function ($inv) {
            $paid = ($inv->DerivedPaid ?? 0);
            $due = ($inv->DerivedDue ?? 0);
            return ($paid > 0 && $paid < $due) ? $paid : 0;
        });

        // Filters
        $properties = PropertyRegistry::all();
        $tenants = PropertyNewTenant::all();

        return view('property.billingandreceipting.rentdashboard.index', compact(
            'invoices', 'collected', 'dueSoon', 'overdue', 'partial',
            'properties', 'tenants', 'chartData'
        ));
    }
}
