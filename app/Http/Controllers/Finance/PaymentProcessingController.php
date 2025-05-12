<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentProcessingController extends Controller
{
    public function index()
    {
        return view('finance.accountspayable.paymentprocessing.index');
    }

    public function create(){
        return view('finance.accountspayable.paymentprocessing.create');
    }
}
