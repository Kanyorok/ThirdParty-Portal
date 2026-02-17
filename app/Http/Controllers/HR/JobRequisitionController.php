<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRequisition;
use App\Models\HR\JobRole;
use App\Models\HRM\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobRequisitionController extends Controller
{
    private const STATUSES = ['Draft', 'Submitted', 'Approved', 'Rejected', 'Closed'];

    public function index(Request $request)
    {
        $query = JobRequisition::with(['department', 'branch', 'grade', 'role'])
            ->orderByDesc('Id');

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('department_id')) {
            $query->where('DepartmentID', $request->department_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('BranchID', $request->branch_id);
        }

        $requisitions = $query->paginate(20);
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $statusList = self::STATUSES;

        return view('hr.recruitment.requisitions.index', compact('requisitions', 'departments', 'branches', 'statusList'));
    }

    public function create()
    {
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name', 'DepartmentID', 'GradeID']);
        $statusList = self::STATUSES;

        // Auto-generate requisition code
        $generatedCode = $this->generateRequisitionCode();

        return view('hr.recruitment.requisitions.create', compact('departments', 'branches', 'grades', 'roles', 'statusList', 'generatedCode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRJobRequisitions,Code'],
            'Title' => ['required', 'string', 'max:150'],
            'DepartmentID' => ['nullable', 'integer', 'exists:t_Departments,Id'],
            'BranchID' => ['nullable', 'integer', 'exists:t_Branches,Id'],
            'GradeID' => ['nullable', 'integer', 'exists:t_HRJobGrades,Id'],
            'RoleID' => ['nullable', 'integer', 'exists:t_Roles,id'],
            'EmploymentType' => ['nullable', 'string', 'max:50'],
            'ContractType' => ['nullable', 'string', 'max:50'],
            'Vacancies' => ['required', 'integer', 'min:1'],
            'Priority' => ['nullable', 'string', 'max:20'],
            'Justification' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $request->input('Action', 'draft');
        $data['Status'] = $action === 'submit' ? 'Submitted' : 'Draft';
        $data['RequestedBy'] = $action === 'submit' ? auth()->id() : null;
        $data['RequestedOn'] = $action === 'submit' ? now() : null;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        JobRequisition::create($data);

        return redirect()->route('hr.recruitment.requisitions.index')
            ->with('success', 'Job requisition saved.');
    }

    public function edit($id)
    {
        $requisition = JobRequisition::findOrFail($id);
        if (in_array($requisition->Status, ['Approved', 'Closed'], true)) {
            return redirect()->route('hr.recruitment.requisitions.index')
                ->withErrors(['status' => 'Approved/closed requisitions cannot be edited.']);
        }

        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name', 'DepartmentID', 'GradeID']);
        $statusList = self::STATUSES;

        return view('hr.recruitment.requisitions.edit', compact('requisition', 'departments', 'branches', 'grades', 'roles', 'statusList'));
    }

    public function update(Request $request, $id)
    {
        $requisition = JobRequisition::findOrFail($id);
        if (in_array($requisition->Status, ['Approved', 'Closed'], true)) {
            return redirect()->route('hr.recruitment.requisitions.index')
                ->withErrors(['status' => 'Approved/closed requisitions cannot be updated.']);
        }

        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRJobRequisitions', 'Code')->ignore($requisition->Id, 'Id')],
            'Title' => ['required', 'string', 'max:150'],
            'DepartmentID' => ['nullable', 'integer', 'exists:t_Departments,Id'],
            'BranchID' => ['nullable', 'integer', 'exists:t_Branches,Id'],
            'GradeID' => ['nullable', 'integer', 'exists:t_HRJobGrades,Id'],
            'RoleID' => ['nullable', 'integer', 'exists:t_Roles,id'],
            'EmploymentType' => ['nullable', 'string', 'max:50'],
            'ContractType' => ['nullable', 'string', 'max:50'],
            'Vacancies' => ['required', 'integer', 'min:1'],
            'Priority' => ['nullable', 'string', 'max:20'],
            'Justification' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $request->input('Action', 'draft');
        if ($action === 'submit' && $requisition->Status !== 'Submitted') {
            $data['Status'] = 'Submitted';
            $data['RequestedBy'] = auth()->id();
            $data['RequestedOn'] = now();
        }

        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $requisition->update($data);

        return redirect()->route('hr.recruitment.requisitions.index')
            ->with('success', 'Job requisition updated.');
    }

    public function approve($id)
    {
        $requisition = JobRequisition::findOrFail($id);
        if ($requisition->Status !== 'Submitted') {
            return redirect()->route('hr.recruitment.requisitions.index')
                ->withErrors(['status' => 'Only submitted requisitions can be approved.']);
        }

        $requisition->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.requisitions.index')->with('success', 'Requisition approved.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'RejectionReason' => ['nullable', 'string', 'max:255'],
        ]);
        $requisition = JobRequisition::findOrFail($id);
        $requisition->update([
            'Status' => 'Rejected',
            'RejectedBy' => auth()->id(),
            'RejectedOn' => now(),
            'RejectionReason' => $data['RejectionReason'] ?? null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.requisitions.index')->with('success', 'Requisition rejected.');
    }

    public function close($id)
    {
        $requisition = JobRequisition::findOrFail($id);
        $requisition->update([
            'Status' => 'Closed',
            'ClosedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.requisitions.index')->with('success', 'Requisition closed.');
    }

    /**
     * Generate a unique requisition code
     * Format: REQ-YYYY-NNNN (e.g., REQ-2026-0001)
     */
    private function generateRequisitionCode(): string
    {
        $year = now()->year;
        $prefix = "REQ-{$year}-";

        // Get the last requisition for this year
        $lastRequisition = JobRequisition::where('Code', 'like', "{$prefix}%")
            ->orderByDesc('Code')
            ->first();

        if ($lastRequisition) {
            // Extract the number from the last code and increment
            $lastNumber = (int) substr($lastRequisition->Code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            // First requisition of the year
            $newNumber = 1;
        }

        // Format with leading zeros (4 digits)
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
