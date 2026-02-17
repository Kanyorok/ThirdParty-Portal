<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeDemotion;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeDemotionController extends Controller
{
    public function index()
    {
        $demotions = EmployeeDemotion::with(['employee'])->orderByDesc('Id')->paginate(20);

        return view('hr.movements.demotions.index', compact('demotions'));
    }

    public function create()
    {
        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get();
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.movements.demotions.create', compact('employees', 'grades', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['RequestedBy'] = auth()->id();
        $data['RequestedOn'] = now();
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        EmployeeDemotion::create($data);

        return redirect()->route('hr.movements.demotions.index')->with('success', 'Demotion request submitted.');
    }

    public function show($id)
    {
        $demotion = EmployeeDemotion::with('employee')->findOrFail($id);

        return view('hr.movements.demotions.show', compact('demotion'));
    }

    public function edit($id)
    {
        $demotion = EmployeeDemotion::findOrFail($id);
        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get();
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();

        return view('hr.movements.demotions.edit', compact('demotion', 'employees', 'grades', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $demotion = EmployeeDemotion::findOrFail($id);
        $data = $this->validateData($request, false);
        $demotion->update($data + [
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.movements.demotions.index')->with('success', 'Demotion updated.');
    }

    public function destroy($id)
    {
        $demotion = EmployeeDemotion::findOrFail($id);
        $demotion->update([
            'Status' => 'Rejected',
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.movements.demotions.index')->with('success', 'Demotion marked rejected.');
    }

    public function approve($id, Request $request)
    {
        $demotion = EmployeeDemotion::findOrFail($id);
        $demotion->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);

        return redirect()->route('hr.movements.demotions.index')->with('success', 'Demotion approved.');
    }

    public function reject($id, Request $request)
    {
        $demotion = EmployeeDemotion::findOrFail($id);
        $demotion->update([
            'Status' => 'Rejected',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);

        return redirect()->route('hr.movements.demotions.index')->with('success', 'Demotion rejected.');
    }

    private function validateData(Request $request, bool $requireEmployee = false): array
    {
        return $request->validate([
            'EmployeeID' => [$requireEmployee ? 'required' : 'nullable', 'integer'],
            'FromGradeID' => 'nullable|integer',
            'ToGradeID' => 'nullable|integer',
            'FromRoleID' => 'nullable|integer',
            'ToRoleID' => 'nullable|integer',
            'FromSalary' => 'nullable|numeric|min:0',
            'ToSalary' => 'nullable|numeric|min:0',
            'EffectiveDate' => 'required|date',
            'Reason' => 'nullable|string|max:255',
            'Status' => ['nullable', Rule::in(['Pending','Approved','Rejected'])],
        ]);
    }
}
