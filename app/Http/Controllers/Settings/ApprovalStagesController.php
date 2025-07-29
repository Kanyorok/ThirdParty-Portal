<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Settings\ApprovalStage;
use Illuminate\Http\Request;
use App\Http\Requests\Settings\ApprovalSetupRequest;
use Illuminate\Support\Facades\DB;

class ApprovalStagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $morphMap = Relation::morphMap();

        $approvalGroups = ApprovalStage::all();

        $sourceOptions = array_flip($morphMap);

        return view('settings.approvals.sections', compact('approvalGroups', 'sourceOptions'));
    }

    public function show($id)
    {
        $approval = ApprovalStage::findOrFail($id);
        $sourceOptions = array_flip(Relation::morphMap());
        $approvalTypes = DB::table('t_WorkFlowTypes')->get(); 
        $permissions = DB::table('t_Permissions')->get();

        return view('settings.approvals.show', compact('approval', 'sourceOptions', 'permissions', 'approvalTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApprovalSetupRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();

        try{
            $approvalStage =ApprovalStage::create([
                'Name' => $validated['Name'],
                'Description' => $validated['Description'],
                'Source' => $validated['DocType'],
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
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
