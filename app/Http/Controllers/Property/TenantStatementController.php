<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenantStatementController extends Controller
{
    //
    public function index()
    {
        return view('property.billingandreceipting.tenantledger.index');
    }

    public function create(){
        return view('property.billingandreceipting.tenantledger.create');
    }
}
