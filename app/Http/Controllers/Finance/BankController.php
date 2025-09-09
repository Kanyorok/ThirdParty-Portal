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
        $banks = Bank::all();
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
            'BankName' => 'required|string|max:200',
            'ShortName' => 'nullable|string|max:50',
        ]);

        Bank::create($request->all());

        return redirect()->route('finance.bank.index');
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
            'BankName' => 'required|string|max:200',
        ]);

        $bank = Bank::findOrFail($id);
        $bank->update($request->all());

        return redirect()->route('finance.bank.index');
    }

    // Delete the bank
    public function destroy($id)
    {
        Bank::destroy($id);
        return redirect()->route('finance.bank.index');
    }
}
