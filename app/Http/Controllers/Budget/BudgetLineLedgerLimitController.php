<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetLineLedgerLimit;
use Illuminate\Http\Request;

class BudgetLineLedgerLimitController extends Controller
{
    public function index() {
        $limits = BudgetLineLedgerLimit::orderBy('CreatedOn', 'desc')->get();
        return view('budgetandanalytics.limits.index', compact('limits'));
    }

    public function create() {
        return view('budgetandanalytics.limits.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'BudgetLineID'=>'required',
            'LedgerID'=>'required',
            'LimitType'=>'required',
            'LimitAmount'=>'required|numeric|min:1',
            'EffectiveFrom'=>'required|date'
        ]);

        BudgetLineLedgerLimit::create($validated + ['CreatedBy'=>auth()->id()]);
        return redirect()->route('budget.limits.index')->with('success','Ledger limit saved.');
    }
}