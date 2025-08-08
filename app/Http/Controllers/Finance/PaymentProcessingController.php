<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceVoucher;
use Illuminate\Http\Request;

class PaymentProcessingController extends Controller
{
    public function index()
    {
        return view('finance.accountspayable.paymentprocessing.index');
    }

    public function create(){

        // $this->authorize('create', PaymentProcessing::class);
        $vouchers = FinanceVoucher::select('Id', 'VoucherNo', 'InvoiceNo', 'TotAmnt')
            ->where('Status','Approved')
            ->get();

        return view('finance.accountspayable.paymentprocessing.create', compact('vouchers'));
    }
}
