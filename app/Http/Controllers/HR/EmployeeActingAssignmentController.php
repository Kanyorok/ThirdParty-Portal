<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeActingAssignment;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\HR\JobRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeActingAssignmentController extends Controller
{
    public function index()
    {
        $assignments = EmployeeActingAssignment::with(['employee'])->orderByDesc('Id')->paginate(20);
        return view('hr.movements.acting.index', compact('assignments'));
    }

    public function create()
    {
        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get();
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get();
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();
        return view('hr.movements.acting.create', compact('employees', 'branches', 'departments', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['RequestedBy'] = auth()->id();
        $data['RequestedOn'] = now();
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        EmployeeActingAssignment::create($data);
        return redirect()->route('hr.movements.acting.index')->with('success', 'Acting assignment request submitted.');
    }

    public function show($id)
    {
        $assignment = EmployeeActingAssignment::with('employee')->findOrFail($id);
        return view('hr.movements.acting.show', compact('assignment'));
    }

    public function edit($id)
    {
        $assignment = EmployeeActingAssignment::findOrFail($id);
        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get();
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get();
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();
        return view('hr.movements.acting.edit', compact('assignment', 'employees', 'branches', 'departments', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $assignment = EmployeeActingAssignment::findOrFail($id);
        $data = $this->validateData($request, false);
        $assignment->update($data + [
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);
        return redirect()->route('hr.movements.acting.index')->with('success', 'Acting assignment updated.');
    }

    public function destroy($id)
    {
        $assignment = EmployeeActingAssignment::findOrFail($id);
        $assignment->update([
            'Status' => 'Rejected',
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);
        return redirect()->route('hr.movements.acting.index')->with('success', 'Acting assignment marked rejected.');
    }

    public function approve($id, Request $request)
    {
        $assignment = EmployeeActingAssignment::findOrFail($id);
        $assignment->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);
        // Optional hook: flag acting allowance generation (use configured ACTING allowance rule)
        // Placeholder: integrate with payroll staging when available.
        return redirect()->route('hr.movements.acting.index')->with('success', 'Acting assignment approved.');
    }

    public function reject($id, Request $request)
    {
        $assignment = EmployeeActingAssignment::findOrFail($id);
        $assignment->update([
            'Status' => 'Rejected',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);
        return redirect()->route('hr.movements.acting.index')->with('success', 'Acting assignment rejected.');
    }

    private function validateData(Request $request, bool $requireEmployee = false): array
    {
        return $request->validate([
            'EmployeeID'         => [$requireEmployee ? 'required' : 'nullable', 'integer'],
            'ActingBranchID'     => 'nullable|integer',
            'ActingDepartmentID' => 'nullable|integer',
            'ActingRoleID'       => 'nullable|integer',
            'StartDate'          => 'required|date',
            'EndDate'            => 'nullable|date|after_or_equal:StartDate',
            'Reason'             => 'nullable|string|max:255',
            'Status'             => ['nullable', Rule::in(['Pending','Approved','Rejected'])],
            'ActingReferenceSalary' => ['nullable','numeric'],
            'ActingAllowanceRate'   => ['nullable','numeric'],
        ]);
    }
}
