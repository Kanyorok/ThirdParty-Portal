<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyNewTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = PropertyInvoice::with([
            'receipts',
            'lease.property',
            'lease.unit',
            'lease.tenant.thirdParty'
        ]);

        // ---- Filters (ALL by default) ----
        if ($request->filled('property_id')) {
            $query->whereHas('lease.property', function ($q) use ($request) {
                $q->where('Id', $request->property_id);
            });
        }

        if ($request->filled('tenant_id')) {
            $query->whereHas('lease.tenant', function ($q) use ($request) {
                $q->where('Id', $request->tenant_id);
            });
        }

        if ($request->filled('billing_month')) {
            $query->whereMonth('InvoiceDate', Carbon::parse($request->billing_month)->month)
                  ->whereYear('InvoiceDate', Carbon::parse($request->billing_month)->year);
        }

        $invoices = $query->get();

        // ---- Finance Invoices mapping ----
        $requestIds = $invoices->pluck('RequestID')->filter()->unique();
        $financeByReq = collect();

        if ($requestIds->isNotEmpty()) {
            $financeByReq = collect(
                DB::table('t_FinanceInvoices')
                    ->whereIn('RequestID', $requestIds)
                    ->get()
            )->keyBy('RequestID');
        }

        // ---- Derive amounts & status ----
        $invoices->each(function ($inv) use ($financeByReq) {
            $due = (float)($inv->RentAmount ?? 0)
                + (float)($inv->ServicesCharge ?? 0)
                + (float)($inv->ParkingFee ?? 0)
                + (float)($inv->OtherCharges ?? 0);

            $fin = $inv->RequestID ? $financeByReq->get($inv->RequestID) : null;
            $paid = $fin ? (float)($fin->AmountPaid ?? 0) : 0;

            $inv->DerivedDue = $due;
            $inv->DerivedPaid = $paid;
            $inv->DerivedStatus = $paid >= $due && $due > 0
                ? 'Fully Paid'
                : ($paid > 0 ? 'Partial Paid' : 'Pending');
        });

        // ---- Monthly chart data ----
        $invoiceByMonth = $invoices->groupBy(fn ($i) =>
            Carbon::parse($i->InvoiceDate)->format('Y-m')
        )->map(fn ($g) => $g->sum('DerivedDue'));

        $financeIds = $financeByReq->pluck('Id')->filter();
        $allocations = collect();

        if ($financeIds->isNotEmpty()) {
            $allocations = collect(
                DB::table('t_FinanceReceiptAllocations as a')
                    ->join('t_FinanceReceipts as r', 'r.Id', '=', 'a.ReceiptID')
                    ->whereIn('a.InvoiceID', $financeIds)
                    ->select('r.ReceiptDate', 'a.AmountAllocated')
                    ->get()
            );
        }

        $receiptByMonth = $allocations->groupBy(fn ($r) =>
            Carbon::parse($r->ReceiptDate)->format('Y-m')
        )->map(fn ($g) => $g->sum('AmountAllocated'));

        $months = $invoiceByMonth->keys()->merge($receiptByMonth->keys())->unique()->sort();

        $chartData = $months->mapWithKeys(fn ($m) => [
            $m => [
                'invoiced' => $invoiceByMonth->get($m, 0),
                'collected' => $receiptByMonth->get($m, 0),
            ]
        ]);

        // ---- Summary cards ----
        $collected = (float)$allocations->sum('AmountAllocated');

        $overdue = $invoices->sum(fn ($i) =>
            max(($i->DerivedDue ?? 0) - ($i->DerivedPaid ?? 0), 0)
        );

        $partial = $invoices->sum(fn ($i) =>
            ($i->DerivedPaid > 0 && $i->DerivedPaid < $i->DerivedDue)
                ? $i->DerivedPaid
                : 0
        );

        $dueSoon = 0; // Optional future logic

        // ---- Filters data ----
        $properties = PropertyRegistry::all();
        $tenants = PropertyNewTenant::with('thirdParty')->get();

        return view('property.billingandreceipting.rentdashboard.index', compact(
            'invoices',
            'collected',
            'dueSoon',
            'overdue',
            'partial',
            'properties',
            'tenants',
            'chartData'
        ));
    }
}
