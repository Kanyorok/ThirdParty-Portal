<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalObligation;
use App\Models\Legal\LegalObligationAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalObligationAssignmentController extends Controller
{
    public function index($obligationId)
    {
        $obligation = LegalObligation::findOrFail($obligationId);
        $assignments = $obligation->assignments()->with('obligation')->get();
        return view('legal.obligations.assignments.index', compact('obligation', 'assignments'));
    }

    public function create($obligationId)
    {
        $obligation = LegalObligation::findOrFail($obligationId);
        $users = User::where('IsActive', 1)->get(); // Can filter by department or legal role
        return view('legal.obligations.assignments.create', compact('obligation', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ObligationID' => 'required|integer|exists:t_LegalObligations,ID',
            'AssignedTo' => 'required|integer|exists:users,id',
            'Remarks' => 'nullable|string',
        ]);

        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = now();
        $data['IsActive'] = 1;

        LegalObligationAssignment::create($data);

        return redirect()->route('legal.obligations.assignments.index', $data['ObligationID'])->with('success', 'Assignment created.');
    }

    public function edit($id)
    {
        $assignment = LegalObligationAssignment::findOrFail($id);
        $users = User::where('IsActive', 1)->get();
        return view('legal.obligations.assignments.edit', compact('assignment', 'users'));
    }

    public function update(Request $request, $id)
    {
        $assignment = LegalObligationAssignment::findOrFail($id);

        $data = $request->validate([
            'AssignedTo' => 'required|integer|exists:users,id',
            'Remarks' => 'nullable|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        $assignment->update($data);

        return redirect()->route('legal.obligations.assignments.index', $assignment->ObligationID)->with('success', 'Assignment updated.');
    }
}
