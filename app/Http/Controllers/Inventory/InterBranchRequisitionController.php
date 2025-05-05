<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InterBranchRequisitionController extends Controller
{
    //
    public function index()
    {
        return view('inventory.interbranch requisition.index');
    }

    public function create(){
        return view('inventory.interbranch requisition.create');
    }
}
