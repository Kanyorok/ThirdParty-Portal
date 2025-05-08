<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyReceiptController extends Controller
{
    //
    public function index()
    {
        return view('property.billingandreceipting.receipting.index');
    }

    public function create(){
        return view('property.billingandreceipting.receipting.create');
    }
}
