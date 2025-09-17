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
public function create(Request $request)
{
    $bank = null;
    if ($request->filled('bankId')) {
        $bank = \App\Models\Finance\Bank::find($request->query('bankId'));
    }
    return view('finance.bankbranch.create', compact('bank'));
}

    // Store the new branch
public function store(Request $request)
{
    $request->validate([
        'BankID'     => 'required|integer|exists:t_Banks,BankID',
        'BranchName' => 'required|string|max:200',
        'BranchCode' => 'nullable|string|max:50',
        'Address1'   => 'nullable|string|max:200',
        'Address2'   => 'nullable|string|max:200',
        'CityID'     => 'nullable|integer',
        'CountryID'  => 'nullable|integer',
        'ZipCode'    => 'nullable|string|max:20',
        'Phone'      => 'nullable|string|max:50',
        'EmailID'    => 'nullable|email|max:150',
        'IsActive'   => 'nullable|boolean',
    ]);

    $branch = new \App\Models\Finance\BankBranch($request->only([
        'BankID','BranchCode','BranchName','Address1','Address2',
        'CityID','CountryID','ZipCode','Phone','EmailID'
    ]));
    $branch->IsActive = $request->boolean('IsActive');
    $branch->save();

    return redirect()->route('finance.bankbranch.bybank', $branch->BankID)
        ->with('success', 'Branch created.');
}

    // Show the branch details
    public function show($bankId, $branchId)
    {
        $branch = BankBranch::findOrFail($branchId);
        return view('finance.bankbranch.show', compact('branch'));
    }

    // Edit the specified branch
public function edit($id)
{
    $branch = \App\Models\Finance\BankBranch::findOrFail($id);
    $bank   = \App\Models\Finance\Bank::find($branch->BankID);
    return view('finance.bankbranch.edit', compact('branch','bank'));
}


    // Update the specified branch
public function update(Request $request, $id)
{
    $request->validate([
        'BranchName' => 'required|string|max:200',
        'BranchCode' => 'nullable|string|max:50',
        'Address1'   => 'nullable|string|max:200',
        'Address2'   => 'nullable|string|max:200',
        'CityID'     => 'nullable|integer',
        'CountryID'  => 'nullable|integer',
        'ZipCode'    => 'nullable|string|max:20',
        'Phone'      => 'nullable|string|max:50',
        'EmailID'    => 'nullable|email|max:150',
        'IsActive'   => 'nullable|boolean',
    ]);

    $branch = \App\Models\Finance\BankBranch::findOrFail($id);
    $branch->fill($request->only([
        'BranchCode','BranchName','Address1','Address2',
        'CityID','CountryID','ZipCode','Phone','EmailID'
    ]));
    $branch->IsActive = $request->boolean('IsActive');
    $branch->save();

    return redirect()->route('finance.bankbranch.bybank', $branch->BankID)
        ->with('success', 'Branch updated.');
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
