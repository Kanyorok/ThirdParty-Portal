<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobGrade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobGradeController extends Controller
{
    public function index()
    {
        $grades = JobGrade::orderBy('Name')->paginate(20);
        return view('hr.config.jobgrades.index', compact('grades'));
    }

    public function create()
    {
        return view('hr.config.jobgrades.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code'      => ['required', 'string', 'max:50', 'unique:t_HRJobGrades,Code'],
            'Name'      => ['required', 'string', 'max:150'],
            'MinSalary' => ['nullable', 'numeric', 'min:0'],
            'MaxSalary' => ['nullable', 'numeric', 'min:0'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        JobGrade::create($data);

        return redirect()->route('hr.config.jobgrades.index')
            ->with('success', 'Job grade created successfully.');
    }

    public function edit($id)
    {
        $grade = JobGrade::findOrFail($id);
        return view('hr.config.jobgrades.edit', compact('grade'));
    }

    public function update(Request $request, $id)
    {
        $grade = JobGrade::findOrFail($id);

        $data = $request->validate([
            'Code'      => ['required', 'string', 'max:50', Rule::unique('t_HRJobGrades', 'Code')->ignore($grade->Id, 'Id')],
            'Name'      => ['required', 'string', 'max:150'],
            'MinSalary' => ['nullable', 'numeric', 'min:0'],
            'MaxSalary' => ['nullable', 'numeric', 'min:0'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive'    => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $grade->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $grade->update($data);

        return redirect()->route('hr.config.jobgrades.index')
            ->with('success', 'Job grade updated successfully.');
    }

    public function destroy($id)
    {
        $grade = JobGrade::findOrFail($id);

        $grade->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.jobgrades.index')
            ->with('success', 'Job grade deactivated successfully.');
    }
}
