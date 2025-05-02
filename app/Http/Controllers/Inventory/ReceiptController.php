<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function index()
    {
        return view('inventory.receipts.index');
    }

    public function create(){
        return view('inventory.receipts.create');
    }
}
