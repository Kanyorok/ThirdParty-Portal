<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\PropertyManagement\PropertyInvoice;
use App\Models\PropertyManagement\PropertyReceipt;
use Illuminate\Support\Facades\DB;

class RentDashboardController extends Controller
{
    //
    public function index()
    {
        $collected = PropertyReceipt::sum('AmountPaidNow');

        $dueSoon = PropertyInvoice::whereDate('InvoiceDate', '>=', now())
            ->whereDate('InvoiceDate', '<=', now()->addDays(7))
            ->sum(DB::raw('RentAmount + ServicesCharge + ParkingFee + OtherCharges'));

        $overdue = 0;
        $partial = 0;

        $invoices = PropertyInvoice::with('receipts', 'lease.unit', 'lease.tenant')->get();
        foreach ($invoices as $invoice) {
            $due = $invoice->RentAmount + $invoice->ServicesCharge + $invoice->ParkingFee + $invoice->OtherCharges;
            $paid = $invoice->receipts->sum('AmountPaidNow');

            if ($paid < $due && now()->gt($invoice->InvoiceDate)) {
                $overdue += ($due - $paid);
            }
            if ($paid > 0 && $paid < $due) {
                $partial += $paid;
            }
        }

        return view('property.billingandreceipting.rentdashboard.index', compact(
            'collected', 'dueSoon', 'overdue', 'partial', 'invoices'
        ));
    }

    public function create(){
        return view('property.billingandreceipting.rentdashboard.create');
    }
}
