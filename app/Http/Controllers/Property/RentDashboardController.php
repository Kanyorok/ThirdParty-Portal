<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyNewTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        // --- Group by month (e.g., "2025-01") ---
        $invoiceByMonth = $invoices->groupBy(function ($inv) {
            return Carbon::parse($inv->InvoiceDate)->format('Y-m');
        })->map(function ($group) {
            return $group->sum(function ($inv) {
                return $inv->RentAmount + $inv->ServicesCharge + $inv->ParkingFee + $inv->OtherCharges;
            });
        });

        $receiptByMonth = $invoices->flatMap->receipts
            ->groupBy(function ($receipt) {
                return Carbon::parse($receipt->ReceiptDate)->format('Y-m');
            })->map(function ($group) {
                return $group->sum('AmountPaidNow');
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

        // Summary calculations (unchanged)
        $collected = $invoices->sum(fn($inv) => $inv->receipts->sum('AmountPaidNow'));
        $dueSoon = 0; // You can calculate due soon based on due dates
        $overdue = $invoices->filter(function ($inv) {
            $due = $inv->RentAmount + $inv->ServicesCharge + $inv->ParkingFee + $inv->OtherCharges;
            $paid = $inv->receipts->sum('AmountPaidNow');
            return $paid < $due && Carbon::parse($inv->InvoiceDate)->lt(now());
        })->sum(fn($inv) => ($inv->RentAmount + $inv->ServicesCharge + $inv->ParkingFee + $inv->OtherCharges) - $inv->receipts->sum('AmountPaidNow'));

        $partial = $invoices->filter(function ($inv) {
            $due = $inv->RentAmount + $inv->ServicesCharge + $inv->ParkingFee + $inv->OtherCharges;
            $paid = $inv->receipts->sum('AmountPaidNow');
            return $paid > 0 && $paid < $due;
        })->sum(fn($inv) => $inv->receipts->sum('AmountPaidNow'));

        // Filters
        $properties = PropertyRegistry::all();
        $tenants = PropertyNewTenant::all();

        return view('property.billingandreceipting.rentdashboard.index', compact(
            'invoices', 'collected', 'dueSoon', 'overdue', 'partial',
            'properties', 'tenants', 'chartData'
        ));
    }
}
