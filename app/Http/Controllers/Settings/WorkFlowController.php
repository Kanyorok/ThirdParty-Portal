<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Settings\WorkFlowRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Settings\ApprovalStage;

class WorkFlowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(ApprovalSetupRequest $request)
    {
        $validated = $request->validated();
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        try{
            $approvalStage =ApprovalStage::create([
                'Name' => $validated['Name'],
                'Description' => $validated['Description'],
                'Source' => $validated['DocType'],
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
                'CreatedOn' => now(),
            ]);

            activity()->causedBy($user)
                ->performedOn($approvalStage)
                ->event('create')
                ->log('Created approval workflow stage: ' . $validated['Name']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to create approval stage: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Approval workflow created.');
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
