<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    //
    public function index()
    {
        return view('procurement.deliverynotes.index');
    }

    public function create(){
        return view('procurement.deliverynotes.create');
    } 
}
