<?php

namespace App\Http\Controllers\Procurement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ApprovalSetupController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'DocType' => 'required',
            'ApprovalType' => 'required|in:ANY,ALL,MAJ,AMT',
            'Permission' => 'required|integer',
        ]);

        DB::table('t_ApprovalGroups')->updateOrInsert(
            ['DocType' => $data['DocType']],
            $data
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