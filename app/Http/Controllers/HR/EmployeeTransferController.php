<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeTransfer;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\HR\JobRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeTransferController extends Controller
{
    public function index()
    {
        $transfers = EmployeeTransfer::with(['employee'])->orderByDesc('Id')->paginate(20);
        return view('hr.movements.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get();
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get();
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();
        return view('hr.movements.transfers.create', compact('employees', 'branches', 'departments', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['RequestedBy'] = auth()->id();
        $data['RequestedOn'] = now();
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        EmployeeTransfer::create($data);
        return redirect()->route('hr.movements.transfers.index')->with('success', 'Transfer request submitted.');
    }

    public function show($id)
    {
        $transfer = EmployeeTransfer::with('employee')->findOrFail($id);
        return view('hr.movements.transfers.show', compact('transfer'));
    }

    public function edit($id)
    {
        $transfer = EmployeeTransfer::findOrFail($id);
        $employees = Employee::where('IsActive', 1)->orderBy('FirstName')->get();
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get();
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get();
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get();
        return view('hr.movements.transfers.edit', compact('transfer', 'employees', 'branches', 'departments', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $transfer = EmployeeTransfer::findOrFail($id);
        $data = $this->validateData($request, false);
        $transfer->update($data + [
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);
        return redirect()->route('hr.movements.transfers.index')->with('success', 'Transfer updated.');
    }

    public function destroy($id)
    {
        $transfer = EmployeeTransfer::findOrFail($id);
        $transfer->update([
            'Status' => 'Rejected',
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);
        return redirect()->route('hr.movements.transfers.index')->with('success', 'Transfer marked rejected.');
    }

    public function approve($id, Request $request)
    {
        $transfer = EmployeeTransfer::findOrFail($id);
        $transfer->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);
        return redirect()->route('hr.movements.transfers.index')->with('success', 'Transfer approved.');
    }

    public function reject($id, Request $request)
    {
        $transfer = EmployeeTransfer::findOrFail($id);
        $transfer->update([
            'Status' => 'Rejected',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);
        return redirect()->route('hr.movements.transfers.index')->with('success', 'Transfer rejected.');
    }

    private function validateData(Request $request, bool $requireEmployee = false): array
    {
        return $request->validate([
            'EmployeeID'       => [$requireEmployee ? 'required' : 'nullable', 'integer'],
            'FromBranchID'     => 'nullable|integer',
            'ToBranchID'       => 'nullable|integer',
            'FromDepartmentID' => 'nullable|integer',
            'ToDepartmentID'   => 'nullable|integer',
            'FromRoleID'       => 'nullable|integer',
            'ToRoleID'         => 'nullable|integer',
            'EffectiveDate'    => 'required|date',
            'Reason'           => 'nullable|string|max:255',
            'Status'           => ['nullable', Rule::in(['Pending','Approved','Rejected'])],
        ]);
    }
}
