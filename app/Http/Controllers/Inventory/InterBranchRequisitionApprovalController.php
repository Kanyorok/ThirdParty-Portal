<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InterBranchRequisitionApprovalController extends Controller
{
    //
    public function index()
    {
        return view('inventory.interbranchrequisition.approval.index');
    }

    public function create(){
        return view('inventory.interbranchrequisition.approval.create');
    }
}
