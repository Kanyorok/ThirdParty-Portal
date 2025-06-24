<?php

namespace App\Http\Controllers\Procurement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ApprovalSetupController extends Controller
{
    public function index(){
        return view('procurement.requisitions.approval-setup');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'DocType' => 'required|string',
            'ApprovalType' => 'required|in:ANY,ALL,MAJ,AMT',
        ]);

        DB::table('t_ApprovalGroups')->updateOrInsert(
            ['DocType' => $data['DocType']],
            [
                'DocType' => $data['DocType'],
                'ApprovalType' => $data['ApprovalType'],
                'Permission' => null  // optional: set to null or auto-resolve later
            ]
        );

        return back()->with('status', 'Approval configuration saved!');
    }


    public function storeLimit(Request $request)
    {
        $data = $request->validate([
            'DocType' => 'required',
            'MaxAmount' => 'required|numeric',
            'Permission' => 'required|integer',
        ]);

        DB::table('t_ApprovalLimits')->insert($data);

        return back()->with('status', 'Approval limit saved!');
    }
}