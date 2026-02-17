<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitClearanceDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExitClearanceDepartmentController extends Controller
{
    public function index()
    {
        ExitClearanceDepartment::syncFromDepartments(auth()->id());
        $departments = ExitClearanceDepartment::orderBy('Sequence')->orderBy('Name')->get();
        return view('hr.exit.config.clearance_departments.index', compact('departments'));
    }

    public function create()
    {
        return view('hr.exit.config.clearance_departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150', 'unique:t_HRExitClearanceDepartments,Name'],
            'Sequence' => ['nullable', 'integer', 'min:1'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        ExitClearanceDepartment::create([
            'Name' => $data['Name'],
            'Sequence' => $data['Sequence'] ?? 1,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-clearance-departments.index')->with('success', 'Clearance department saved.');
    }

    public function edit($id)
    {
        $department = ExitClearanceDepartment::findOrFail($id);
        return view('hr.exit.config.clearance_departments.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        $department = ExitClearanceDepartment::findOrFail($id);
        $data = $request->validate([
            'Name' => ['required', 'string', 'max:150', Rule::unique('t_HRExitClearanceDepartments', 'Name')->ignore($department->Id, 'Id')],
            'Sequence' => ['nullable', 'integer', 'min:1'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $department->update([
            'Name' => $data['Name'],
            'Sequence' => $data['Sequence'] ?? 1,
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-clearance-departments.index')->with('success', 'Clearance department updated.');
    }
}
