<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BudgetApprovalController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetworkspace.approval.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetworkspace.approval.create');
    }

    public function approve(Request $request)
    {
        //Permission to approve budget

        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'approval_reason' => 'required|string',
        ]);

        //Approve Budget
        try {
            Budget::where('id', $validated['BudgetID'])->update([
                'Status' => 'approved',
            ]);

            return back()->with('success', 'Budget Approved Successfully');
        } catch (\Throwable $th) {
            Log::error('Error in approving budget', $th->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    public function reject(Request $request)
    {
        //Permission to approve budget

        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'rejection_reason' => 'required|string',
        ]);

        //Approve Budget
        try {
            Budget::where('id', $validated['BudgetID'])->update([
                'Status' => 'rejected',
            ]);

            return back()->with('success', 'Budget Rejected Successfully');
        } catch (\Throwable $th) {
            Log::error('Error in approving budget', $th->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }
}
