<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentVoucherController extends Controller
{
    public function index()
    {
        return view('finance.accountspayable.paymentvoucher.index');
    }

    public function create(){
        return view('finance.accountspayable.paymentvoucher.create');
    }
}
