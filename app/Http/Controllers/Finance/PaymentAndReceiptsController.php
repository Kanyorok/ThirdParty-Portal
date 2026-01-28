<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class PaymentAndReceiptsController extends Controller
{
    public function index()
    {
        return view('finance.cashlink.paymentandreceiptvouchers.index');
    }

    public function create()
    {
        return view('finance.cashlink.paymentandreceiptvouchers.create');
    }
}
