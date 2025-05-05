<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    //
    public function index()
    {
        return view('inventory.inventory reports.index');
    }

    public function create(){
        return view('inventory.inventory reports.create');
    }
}
