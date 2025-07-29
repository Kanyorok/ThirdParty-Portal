<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorflowLimitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Logic to retrieve workflow limits
        $permissions = DB::table('t_Permissions')->get();
        $existingPermissionIds = DB::table('t_WorkflowLimits')
        ->pluck('PermissionID')
        ->toArray();
        $limits = DB::table('t_WorkflowLimits')
        ->join('t_Permissions', 't_WorkflowLimits.PermissionID', '=', 't_Permissions.id')
        ->select('t_WorkflowLimits.*', 't_Permissions.name as PermissionName')
        ->get();

        return view('settings.approvals.workflowlimitsetup', compact('permissions', 'limits', 'existingPermissionIds'));
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
    public function store(Request $request)
    {
        //
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
