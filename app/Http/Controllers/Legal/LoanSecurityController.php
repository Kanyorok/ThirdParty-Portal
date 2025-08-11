<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LoanSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanSecurityController extends Controller
{
    public function index()
    {
        // $securities = LoanSecurity::whereNull('DeletedOn')->get();
        return view('legal/securities/index');
    }

    public function create()
    {
        return view('legal/securities/create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'SecurityType' => 'required|string',
            'Description' => 'nullable|string',
            'OwnerName' => 'required|string',
            'OwnerIDNumber' => 'nullable|string',
            'LoanAccountNumber' => 'nullable|string',
            'Value' => 'nullable|numeric',
            'Institution' => 'nullable|string',
            'RegistrationDetails' => 'nullable|string',
            'SecurityStatus' => 'nullable|string',
            'Remarks' => 'nullable|string',
        ]);

        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = now();

        LoanSecurity::create($data);

        return redirect()->route('legal.securities.index')->with('success', 'Security registered successfully.');
    }

    public function edit($id)
    {
        $security = LoanSecurity::findOrFail($id);
        return view('legal/securities/edit', compact('security'));
    }

    public function update(Request $request, $id)
    {
        $security = LoanSecurity::findOrFail($id);

        $data = $request->validate([
            'SecurityType' => 'required|string',
            'Description' => 'nullable|string',
            'OwnerName' => 'required|string',
            'OwnerIDNumber' => 'nullable|string',
            'LoanAccountNumber' => 'nullable|string',
            'Value' => 'nullable|numeric',
            'Institution' => 'nullable|string',
            'RegistrationDetails' => 'nullable|string',
            'SecurityStatus' => 'nullable|string',
            'Remarks' => 'nullable|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        $security->update($data);

        return redirect()->route('legal.securities.index')->with('success', 'Security updated successfully.');
    }
}
