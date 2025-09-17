<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\Bank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BankController extends Controller
{
    // Display a listing of the banks
    public function index()
    {
        $banks = \App\Models\Finance\Bank::orderBy('BankName')->paginate(20);
        return view('finance.bank.index', compact('banks'));
    }

    // Show the form for creating a new bank
    public function create()
    {
        return view('finance.bank.create');
    }

    // Store a newly created bank
    public function store(Request $request)
    {
        $request->validate([
            'BankName'     => 'required|string|max:200',
            'ShortName'    => 'nullable|string|max:50',
            'BankCode'     => 'nullable|string|max:50',
            'SwiftCode'    => 'nullable|string|max:20',
            'ClearingCode' => 'nullable|string|max:50',
            'CountryID'    => 'nullable|integer',
            'EmailID'      => 'nullable|email|max:150',
            'Phone'        => 'nullable|string|max:50',
            'Website'      => 'nullable|url|max:150',
            'IsActive'     => 'nullable|boolean',
        ]);

        $bank = new \App\Models\Finance\Bank();
        $bank->BankName     = $request->BankName;
        $bank->ShortName    = $request->ShortName;
        $bank->BankCode     = $request->BankCode;
        $bank->SwiftCode    = $request->SwiftCode;
        $bank->ClearingCode = $request->ClearingCode;
        $bank->CountryID    = $request->CountryID;
        $bank->EmailID      = $request->EmailID;
        $bank->Phone        = $request->Phone;
        $bank->Website      = $request->Website;
        $bank->IsActive     = $request->boolean('IsActive'); // checkbox → 1/0
        $bank->CreatedBy    = auth()->id();
        // CreatedOn has DB default (GETDATE()), so no need to set here
        $bank->save();

        return redirect()->route('finance.bank.index')->with('success', 'Bank created.');
    }
    // Show a specific bank's details
    public function show($id)
    {
        $bank = Bank::findOrFail($id);
        return view('finance.bank.show', compact('bank'));
    }

    // Show the form for editing the specified bank
    public function edit($id)
    {
        $bank = Bank::findOrFail($id);
        return view('finance.bank.edit', compact('bank'));
    }

    // Update the specified bank
    public function update(Request $request, $id)
    {
        $request->validate([
            'BankName'     => 'required|string|max:200',
            'ShortName'    => 'nullable|string|max:50',
            'BankCode'     => 'nullable|string|max:50',
            'SwiftCode'    => 'nullable|string|max:20',
            'ClearingCode' => 'nullable|string|max:50',
            'CountryID'    => 'nullable|integer',
            'EmailID'      => 'nullable|email|max:150',
            'Phone'        => 'nullable|string|max:50',
            'Website'      => 'nullable|url|max:150',
            'IsActive'     => 'nullable|boolean',
        ]);

        $bank = \App\Models\Finance\Bank::findOrFail($id);
        $bank->BankName     = $request->BankName;
        $bank->ShortName    = $request->ShortName;
        $bank->BankCode     = $request->BankCode;
        $bank->SwiftCode    = $request->SwiftCode;
        $bank->ClearingCode = $request->ClearingCode;
        $bank->CountryID    = $request->CountryID;
        $bank->EmailID      = $request->EmailID;
        $bank->Phone        = $request->Phone;
        $bank->Website      = $request->Website;
        $bank->IsActive     = $request->boolean('IsActive');
        $bank->ModifiedBy   = auth()->id();
        $bank->ModifiedOn   = now(); // will work fine against SQL Server
        $bank->save();

        return redirect()->route('finance.bank.index')->with('success', 'Bank updated.');
    }

    // Delete the bank
    public function destroy($id)
    {
        Bank::destroy($id);
        return redirect()->route('finance.bank.index');
    }
}
