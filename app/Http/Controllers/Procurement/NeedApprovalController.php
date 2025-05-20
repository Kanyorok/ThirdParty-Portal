<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\Procurement\Item;
use Illuminate\Http\Request;

class NeedApprovalController extends Controller
{
    //
            public function index()
    {
        $NeedsApprovalviews = DepartmentNeeds::all();
        $NeedsApprovalviews = DepartmentNeeds::with('creator')->get();
        return view('procurement.procurementplan.departmentneeds.approval.index', compact('NeedsApprovalviews'));
    }

    public function show($Id)
    {
        $need = DepartmentNeeds::with(['item.category', 'creator'])->findOrFail($Id);
        return view('procurement.procurementplan.departmentneeds.approval.show', compact('need'));
    }

}

