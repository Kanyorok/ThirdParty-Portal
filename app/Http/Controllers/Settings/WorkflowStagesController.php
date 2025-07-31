<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Settings\ApprovalStage;
use Illuminate\Http\Request;
use App\Http\Requests\Settings\ApprovalSetupRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkflowStagesController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
}
