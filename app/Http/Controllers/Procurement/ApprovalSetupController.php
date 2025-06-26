<?php

namespace App\Http\Controllers\Procurement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApprovalSetupController extends Controller
{
    public function index(){
        $approvalGroups = DB::table('t_ApprovalGroups as g')
        ->leftJoin('t_Permissions as p', 'g.Permission', '=', 'p.id')
        ->select('g.*', 'p.name as permission_name')
        ->get();

        $permissions = DB::table('t_Permissions')->get();
        return view('procurement.requisitions.approval-setup', compact('approvalGroups', 'permissions'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $data = $request->validate([
            'DocType' => 'required|string',
            'ApprovalType' => 'required|in:ANY,ALL,MAJ,AMT',
            'Permission' => 'nullable|integer',
        ]);

        DB::table('t_ApprovalGroups')->updateOrInsert(
            [
                'DocType' => $data['DocType'],
                'ApprovalType' => $data['ApprovalType'],
                'Permission' => $data['Permission'] ?? null,
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
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

    public function edit($id)
    {
        $approvalGroup = DB::table('t_ApprovalGroups')->where('id', $id)->first();
        if (!$approvalGroup) {
            return redirect()->back()->withErrors(['error' => 'Approval group not found.']);
        }

        $permissions = DB::table('t_Permissions')->get();
        return view('procurement.requisitions.edit-approval-group', compact('approvalGroup', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'DocType' => 'required|string',
            'ApprovalType' => 'required|in:ANY,ALL,MAJ,AMT',
            'Permission' => 'nullable|integer',
        ]);

        DB::table('t_ApprovalGroups')->where('id', $id)->update([
            'DocType' => $data['DocType'],
            'ApprovalType' => $data['ApprovalType'],
            'Permission' => $data['Permission'] ?? null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('approval-setup.index')->with('status', 'Approval group updated successfully!');
    }

    public function destroy($id)
    {
        DB::table('t_ApprovalGroups')->where('id', $id)->delete();
        return redirect()->route('approval-setup.index')->with('status', 'Approval group deleted successfully!');
    }
}