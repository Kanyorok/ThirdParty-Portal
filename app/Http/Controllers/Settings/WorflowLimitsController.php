<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Http\Requests\Settings\WorkFlowLimitRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings\WorkFlowLimit;

class WorflowLimitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $morphMap = Relation::morphMap();

        // Logic to retrieve workflow limits
        $permissions = DB::table('t_Permissions')->get();
        $existingPermissionIds = DB::table('t_WorkflowLimits')
        ->pluck('PermissionID')
        ->toArray();
        $limits = DB::table('t_WorkflowLimits')
        ->join('t_Permissions', 't_WorkflowLimits.PermissionID', '=', 't_Permissions.id')
        ->select('t_WorkflowLimits.*', 't_Permissions.name as PermissionName')
        ->get();

        $sourceOptions = array_flip($morphMap);

        return view('settings.approvals.workflowlimitsetup', compact('permissions', 'limits', 'existingPermissionIds', 'sourceOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WorkFlowLimitRequest $request)
    {
        $validated = $request->validated();
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        try{
            $workflowLimit = WorkFlowLimit::create([
                'Source' => $validated['DocType'],
                'PermissionId' => $validated['Permission'],
                'MaxAmount' => $validated['AmountLimit'],
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
                'CreatedOn' => now(),
            ]);

            activity()->causedBy($user)
                ->performedOn($workflowLimit)
                ->event('create')
                ->log('Created approval workflow limit: ' . $validated['DocType']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create approval limit: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Approval workflow limit created.');        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
