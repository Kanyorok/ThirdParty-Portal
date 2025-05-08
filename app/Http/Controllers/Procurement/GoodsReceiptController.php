<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    //
    public function index()
    {
        return view('procurement.goodreceipts.index');
    }

    public function create(){
        return view('procurement.goodreceipts.create');
    }
}
