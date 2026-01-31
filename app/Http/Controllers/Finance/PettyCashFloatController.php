<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\PettyCashFloat;
use Illuminate\Http\Request;

class PettyCashFloatController extends Controller
{
    public function index()
    {
        $rows = PettyCashFloat::with('currency')->orderByDesc('FloatID')->paginate(25);

        return view('finance.pettycash.floats.index', compact('rows'));
    }

    public function create()
    {
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name']);

        return view('finance.pettycash.floats.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => 'required|string|max:50|unique:t_PettyCashFloats,Code',
            'Name' => 'required|string|max:150',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'CustodianUserID' => 'nullable|integer',
            'FloatLimit' => 'nullable|numeric|min:0',
            'ReorderLevel' => 'nullable|numeric|min:0',
            'OpeningBalance' => 'nullable|numeric|min:0',
            'IsActive' => 'nullable|boolean',
        ]);

        PettyCashFloat::create($data + ['IsActive' => (int)($data['IsActive'] ?? 1)]);

        return redirect()->route('finance.pettyfloats.index')->with('success', 'Petty cash float created.');
    }

    public function edit($id)
    {
        $row = PettyCashFloat::findOrFail($id);
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name']);

        return view('finance.pettycash.floats.edit', compact('row', 'currencies'));
    }

    public function update(Request $request, $id)
    {
        $row = PettyCashFloat::findOrFail($id);
        $data = $request->validate([
            'Name' => 'required|string|max:150',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'CustodianUserID' => 'nullable|integer',
            'FloatLimit' => 'nullable|numeric|min:0',
            'ReorderLevel' => 'nullable|numeric|min:0',
            'IsActive' => 'nullable|boolean',
        ]);
        $row->fill($data + ['IsActive' => (int)($data['IsActive'] ?? $row->IsActive)])->save();

        return redirect()->route('finance.pettyfloats.index')->with('success', 'Petty cash float updated.');
    }

    public function destroy($id)
    {
        $row = PettyCashFloat::findOrFail($id);
        $row->delete();

        return redirect()->route('finance.pettyfloats.index')->with('success', 'Petty cash float deleted.');
    }
}
