<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
