<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HRM\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobRoleController extends Controller
{
    public function index()
    {
        $roles = JobRole::with(['grade', 'department'])->orderBy('Name')->paginate(20);

        return view('hr.config.jobroles.index', compact('roles'));
    }

    public function create()
    {
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $departments = Department::orderBy('Name')->get();

        return view('hr.config.jobroles.create', compact('grades', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRJobRoles,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'GradeID' => ['nullable', 'integer', 'exists:t_HRJobGrades,Id'],
            'DepartmentID' => ['nullable', 'integer', 'exists:t_Departments,Id'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        JobRole::create($data);

        return redirect()->route('hr.config.jobroles.index')
            ->with('success', 'Job role created successfully.');
    }

    public function edit($id)
    {
        $role = JobRole::findOrFail($id);
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $departments = Department::orderBy('Name')->get();

        return view('hr.config.jobroles.edit', compact('role', 'grades', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $role = JobRole::findOrFail($id);

        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRJobRoles', 'Code')->ignore($role->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'GradeID' => ['nullable', 'integer', 'exists:t_HRJobGrades,Id'],
            'DepartmentID' => ['nullable', 'integer', 'exists:t_Departments,Id'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $role->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $role->update($data);

        return redirect()->route('hr.config.jobroles.index')
            ->with('success', 'Job role updated successfully.');
    }

    public function destroy($id)
    {
        $role = JobRole::findOrFail($id);

        $role->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.jobroles.index')
            ->with('success', 'Job role deactivated successfully.');
    }

    public function activate($id)
    {
        $role = JobRole::findOrFail($id);

        $role->update([
            'IsActive' => 1,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.jobroles.index')
            ->with('success', 'Job role activated successfully.');
    }
}
