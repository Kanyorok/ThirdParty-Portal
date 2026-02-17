<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExitTypeController extends Controller
{
    public function index()
    {
        $types = ExitType::orderBy('Name')->get();

        return view('hr.exit.config.exit_types.index', compact('types'));
    }

    public function create()
    {
        return view('hr.exit.config.exit_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', 'unique:t_HRExitTypes,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsEmployerInitiated' => ['sometimes', 'boolean'],
            'RequiresCase' => ['sometimes', 'boolean'],
            'RequiresHearing' => ['sometimes', 'boolean'],
            'IsRedundancy' => ['sometimes', 'boolean'],
            'IsSummaryDismissal' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        ExitType::create([
            'Code' => strtoupper($data['Code']),
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsEmployerInitiated' => $request->boolean('IsEmployerInitiated'),
            'RequiresCase' => $request->boolean('RequiresCase'),
            'RequiresHearing' => $request->boolean('RequiresHearing'),
            'IsRedundancy' => $request->boolean('IsRedundancy'),
            'IsSummaryDismissal' => $request->boolean('IsSummaryDismissal'),
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-types.index')->with('success', 'Exit type saved.');
    }

    public function edit($id)
    {
        $type = ExitType::findOrFail($id);

        return view('hr.exit.config.exit_types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = ExitType::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', Rule::unique('t_HRExitTypes', 'Code')->ignore($type->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsEmployerInitiated' => ['sometimes', 'boolean'],
            'RequiresCase' => ['sometimes', 'boolean'],
            'RequiresHearing' => ['sometimes', 'boolean'],
            'IsRedundancy' => ['sometimes', 'boolean'],
            'IsSummaryDismissal' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $type->update([
            'Code' => strtoupper($data['Code']),
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsEmployerInitiated' => $request->boolean('IsEmployerInitiated'),
            'RequiresCase' => $request->boolean('RequiresCase'),
            'RequiresHearing' => $request->boolean('RequiresHearing'),
            'IsRedundancy' => $request->boolean('IsRedundancy'),
            'IsSummaryDismissal' => $request->boolean('IsSummaryDismissal'),
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-types.index')->with('success', 'Exit type updated.');
    }
}
