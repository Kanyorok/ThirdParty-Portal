<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyReceiptPrintController extends Controller
{
    //
    public function index()
    {
        return view('property.billingandreceipting.receiptprint.index');
    }

    public function create(){
        return view('property.billingandreceipting.receiptprint.create');
    }
}
