<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalSetupController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', \App\Models\Core\ApprovalGroup::class);
        $approvalGroups = DB::table('t_ApprovalGroups as g')
            ->leftJoin('t_Permissions as p', 'g.Permission', '=', 'p.id')
            ->select('g.*', 'p.name as permission_name')
            ->get();

        $permissions = DB::table('t_Permissions')->get();
        return view('procurement.requisitions.approval-setup', compact('approvalGroups', 'permissions'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', \App\Models\Core\ApprovalGroup::class);
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
        $this->authorize('create', \App\Models\Core\ApprovalGroup::class);
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
        $this->authorize('viewAny', \App\Models\Core\ApprovalGroup::class); // Using viewAny as we don't have a model instance easily from DB query here without checking
        // Or fetch first then authorize.
        $approvalGroup = DB::table('t_ApprovalGroups')->where('id', $id)->first();
        if (!$approvalGroup) {
            return redirect()->back()->withErrors(['error' => 'Approval group not found.']);
        }

        // Since we don't have a model instance, we rely on class-level check or need to hydrate a model (expensive).
        // For settings, viewAny usually implies access to the settings list/edit.
        // Let's check update permission on class for edit
        $this->authorize('update', \App\Models\Core\ApprovalGroup::class); // Check if user can update *any* approval group (settings role)

        $permissions = DB::table('t_Permissions')->get();
        return view('procurement.requisitions.edit-approval-group', compact('approvalGroup', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', \App\Models\Core\ApprovalGroup::class);
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
        $this->authorize('delete', \App\Models\Core\ApprovalGroup::class);
        DB::table('t_ApprovalGroups')->where('id', $id)->delete();
        return redirect()->route('approval-setup.index')->with('status', 'Approval group deleted successfully!');
    }
}
