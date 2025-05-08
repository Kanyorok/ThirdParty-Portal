<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyInvoiceController extends Controller
{
    //
    public function index()
    {
        return view('property.billingandreceipting.invoicing.index');
    }

    public function create(){
        return view('property.billingandreceipting.invoicing.create');
    }
}
