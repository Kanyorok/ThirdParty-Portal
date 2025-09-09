<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\Bank;
use App\Models\Finance\BankBranch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BankBranchController extends Controller
{
    // Show all branches for a specific bank
    public function index($bankId)
    {
        $bank = Bank::findOrFail($bankId);
        $branches = $bank->branches;
        return view('finance.bankbranch.index', compact('bank', 'branches'));
    }

    // Show the form for creating a new branch
    public function create($bankId)
    {
        return view('finance.bankbranch.create', compact('bankId'));
    }

    // Store the new branch
    public function store(Request $request, $bankId)
    {
        $request->validate([
            'BranchName' => 'required|string|max:200',
        ]);

        BankBranch::create([
            'BankID' => $bankId,
            'BranchName' => $request->BranchName,
        ]);

        return redirect()->route('finance.bankbranch.index', $bankId);
    }

    // Show the branch details
    public function show($bankId, $branchId)
    {
        $branch = BankBranch::findOrFail($branchId);
        return view('finance.bankbranch.show', compact('branch'));
    }

    // Edit the specified branch
    public function edit($bankId, $branchId)
    {
        $branch = BankBranch::findOrFail($branchId);
        return view('finance.bankbranch.edit', compact('bankId', 'branch'));
    }

    // Update the specified branch
    public function update(Request $request, $bankId, $branchId)
    {
        $request->validate([
            'BranchName' => 'required|string|max:200',
        ]);

        $branch = BankBranch::findOrFail($branchId);
        $branch->update($request->all());

        return redirect()->route('finance.bankbranch.index', $bankId);
    }

    // Delete the specified branch
    public function destroy($bankId, $branchId)
    {
        BankBranch::destroy($branchId);
        return redirect()->route('finance.bankbranch.index', $bankId);
    }

    // List all branches (for all banks, menu-safe)
    public function listAll()
    {
        $branches = BankBranch::with('bank')->orderBy('BranchName')->paginate(20);
        return view('finance.bankbranch.index_all', compact('branches'));
    }
}
