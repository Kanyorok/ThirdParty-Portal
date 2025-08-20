<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VendorMasterController extends Controller
{
    public function index()
    {
        return view('finance.accountspayable.vendormaster.index');
    }

    public function create(){
        return view('finance.accountspayable.vendormaster.create');
    }
}
