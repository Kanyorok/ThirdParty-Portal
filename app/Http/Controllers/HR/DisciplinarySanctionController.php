<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinarySanction;
use Illuminate\Http\Request;

class DisciplinarySanctionController extends Controller
{
    public function index()
    {
        $sanctions = DisciplinarySanction::orderBy('Name')->get();

        return view('hr.discipline.sanctions.index', compact('sanctions'));
    }

    public function create()
    {
        return view('hr.discipline.sanctions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', 'unique:t_HRDisciplinarySanctions,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsSuspension' => ['sometimes', 'boolean'],
            'SuspensionWithoutPay' => ['sometimes', 'boolean'],
            'AffectsPayroll' => ['sometimes', 'boolean'],
            'BlocksLeave' => ['sometimes', 'boolean'],
            'UpdatesEmploymentStatus' => ['sometimes', 'boolean'],
            'EmploymentStatus' => ['nullable', 'string', 'max:50'],
            'DefaultDurationDays' => ['nullable', 'integer', 'min:0'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        DisciplinarySanction::create([
            'Code' => $data['Code'],
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsSuspension' => $request->boolean('IsSuspension', false),
            'SuspensionWithoutPay' => $request->boolean('SuspensionWithoutPay', false),
            'AffectsPayroll' => $request->boolean('AffectsPayroll', false),
            'BlocksLeave' => $request->boolean('BlocksLeave', false),
            'UpdatesEmploymentStatus' => $request->boolean('UpdatesEmploymentStatus', false),
            'EmploymentStatus' => $data['EmploymentStatus'] ?? null,
            'DefaultDurationDays' => $data['DefaultDurationDays'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.sanctions.index')->with('success', 'Sanction saved.');
    }

    public function edit($id)
    {
        $sanction = DisciplinarySanction::findOrFail($id);

        return view('hr.discipline.sanctions.edit', compact('sanction'));
    }

    public function update(Request $request, $id)
    {
        $sanction = DisciplinarySanction::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', 'unique:t_HRDisciplinarySanctions,Code,'.$sanction->Id.',Id'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsSuspension' => ['sometimes', 'boolean'],
            'SuspensionWithoutPay' => ['sometimes', 'boolean'],
            'AffectsPayroll' => ['sometimes', 'boolean'],
            'BlocksLeave' => ['sometimes', 'boolean'],
            'UpdatesEmploymentStatus' => ['sometimes', 'boolean'],
            'EmploymentStatus' => ['nullable', 'string', 'max:50'],
            'DefaultDurationDays' => ['nullable', 'integer', 'min:0'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $sanction->update([
            'Code' => $data['Code'],
            'Name' => $data['Name'],
            'Description' => $data['Description'] ?? null,
            'IsSuspension' => $request->boolean('IsSuspension', false),
            'SuspensionWithoutPay' => $request->boolean('SuspensionWithoutPay', false),
            'AffectsPayroll' => $request->boolean('AffectsPayroll', false),
            'BlocksLeave' => $request->boolean('BlocksLeave', false),
            'UpdatesEmploymentStatus' => $request->boolean('UpdatesEmploymentStatus', false),
            'EmploymentStatus' => $data['EmploymentStatus'] ?? null,
            'DefaultDurationDays' => $data['DefaultDurationDays'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.sanctions.index')->with('success', 'Sanction updated.');
    }
}
