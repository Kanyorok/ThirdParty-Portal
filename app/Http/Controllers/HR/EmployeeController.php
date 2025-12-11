<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\Finance\Bank;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HR\EmployeeContact;
use App\Models\HR\EmployeeDocument;
use App\Models\HR\EmployeeSalaryHistory;
use App\Models\HR\EmployeeEducation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    private const STATUSES = ['Pending', 'Active', 'OnHold', 'Dormant', 'Deactivated', 'Exited'];

    public function index(Request $request)
    {
        $query = Employee::query()
            ->with(['branch', 'department', 'grade', 'role', 'supervisor']);

        if ($request->filled('branch_id')) {
            $query->where('BranchID', $request->branch_id);
        }

        if ($request->filled('department_id')) {
            $query->where('DepartmentID', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('EmployeeNo', 'like', "%{$search}%")
                  ->orWhere('FirstName', 'like', "%{$search}%")
                  ->orWhere('LastName', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $employees = $query->paginate($perPage);

        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $statusList = self::STATUSES;

        return view('hr.employees.index', compact('employees', 'branches', 'departments', 'statusList'));
    }

    public function create()
    {
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles  = JobRole::where('IsActive', 1)->orderBy('Name')->get();
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get();
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get();
        $supervisors = Employee::where('IsActive', 1)->orderBy('FirstName')->get(['Id', 'FirstName', 'LastName']);
        $banks = Bank::where('IsActive', 1)->orderBy('BankName')->get(['BankID', 'BankName']);

        $statusList = self::STATUSES;

        return view('hr.employees.create', compact('grades', 'roles', 'branches', 'departments', 'supervisors', 'banks', 'statusList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeNo'      => 'required|string|max:50|unique:t_HREmployees,EmployeeNo',
            'FirstName'       => 'required|string|max:100',
            'LastName'        => 'required|string|max:100',
            'Email'           => 'nullable|email|max:150',
            'Phone'           => 'nullable|string|max:50',
            'Gender'          => ['nullable', Rule::in(['Male','Female','Other'])],
            'DateOfBirth'     => 'nullable|date',
            'BranchID'        => 'required|integer',
            'DepartmentID'    => 'required|integer',
            'GradeID'         => 'nullable|integer',
            'RoleID'          => 'nullable|integer',
            'SupervisorID'    => 'nullable|integer',
            'EmploymentDate'  => 'nullable|date',
            'EmploymentType'  => 'nullable|string|max:50',
            'ContractType'    => 'nullable|string|max:50',
            'Address'         => 'nullable|string|max:255',
            'NSSFNo'          => 'nullable|string|max:50',
            'NHIFNo'          => 'nullable|string|max:50',
            'KRAPIN'          => 'nullable|string|max:50',
            'BasicSalary'     => 'required|numeric|min:0',
            'PaymentMode'     => 'required|string|max:50',
            'BankName'        => 'nullable|string|max:150',
            'BankBranch'      => 'nullable|string|max:150',
            'BankAccount'     => 'nullable|string|max:100',
            'Status'          => ['nullable', 'string', 'max:20', Rule::in(self::STATUSES)],
            'SalaryEffectiveFrom' => 'nullable|date',
            'Photo'           => 'nullable|image|max:5120',
            // Contacts
            'contact_name.*'  => 'nullable|string|max:150',
            'contact_relation.*' => 'nullable|string|max:100',
            'contact_phone.*' => 'nullable|string|max:50',
            'contact_email.*' => 'nullable|email|max:150',
            'contact_is_next_of_kin.*' => 'nullable|boolean',
            'contact_is_primary.*' => 'nullable|boolean',
            'contact_is_emergency.*' => 'nullable|boolean',
            // Education
            'edu_level.*'     => 'nullable|string|max:100',
            'edu_institution.*' => 'nullable|string|max:200',
            'edu_course.*'    => 'nullable|string|max:200',
            'edu_year_from.*' => 'nullable|digits:4',
            'edu_year_to.*'   => 'nullable|digits:4',
            'edu_grade.*'     => 'nullable|string|max:50',
            // Documents
            'documents.*'     => 'nullable|file|max:5120',
            'documents_category.*' => 'nullable|string|max:100',
            'documents_description.*' => 'nullable|string|max:255',
        ]);

        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        if ($request->hasFile('Photo')) {
            $data['PhotoPath'] = $request->file('Photo')->store('employee-photos', 'public');
        }

        $employee = Employee::create($data);

        $this->syncContacts($employee, $request);
        $this->syncEducation($employee, $request);
        $this->syncDocuments($employee, $request);

        $salaryEffective = $request->input('SalaryEffectiveFrom') ?: ($employee->EmploymentDate ?? now()->toDateString());
        EmployeeSalaryHistory::create([
            'EmployeeID'   => $employee->Id,
            'BasicSalary'  => $employee->BasicSalary,
            'EffectiveFrom'=> $salaryEffective,
            'Notes'        => 'Initial salary',
            'CreatedBy'    => auth()->id(),
            'CreatedOn'    => now(),
        ]);

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show($id)
    {
        $employee = Employee::with(['branch', 'department', 'grade', 'role', 'supervisor', 'contacts', 'documents', 'salaryHistory'])
            ->findOrFail($id);

        $attendanceSummary = $employee->attendanceDaily()
            ->where('WorkDate', '>=', now()->subDays(30)->toDateString())
            ->selectRaw("COUNT(*) as days, SUM(CASE WHEN Status = 'Present' THEN 1 ELSE 0 END) as present_days, SUM(CASE WHEN Status != 'Present' THEN 1 ELSE 0 END) as other_days, SUM(COALESCE(OvertimeHours,0)) as overtime_hours")
            ->first();

        return view('hr.employees.show', compact('employee', 'attendanceSummary'));

    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $grades   = JobGrade::where('IsActive', 1)->orderBy('Name')->get();
        $roles    = JobRole::where('IsActive', 1)->orderBy('Name')->get();
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get();
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get();
        $supervisors = Employee::where('IsActive', 1)->orderBy('FirstName')->get(['Id', 'FirstName', 'LastName']);
        $banks = Bank::where('IsActive', 1)->orderBy('BankName')->get(['BankID', 'BankName']);
        $statusList = self::STATUSES;

        return view('hr.employees.edit', compact('employee', 'grades', 'roles', 'branches', 'departments', 'supervisors', 'banks', 'statusList'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'FirstName'       => 'required|string|max:100',
            'LastName'        => 'required|string|max:100',
            'Email'           => 'nullable|email|max:150',
            'Phone'           => 'nullable|string|max:50',
            'Gender'          => ['nullable', Rule::in(['Male','Female','Other'])],
            'DateOfBirth'     => 'nullable|date',
            'BranchID'        => 'required|integer',
            'DepartmentID'    => 'required|integer',
            'GradeID'         => 'nullable|integer',
            'RoleID'          => 'nullable|integer',
            'SupervisorID'    => 'nullable|integer',
            'EmploymentDate'  => 'nullable|date',
            'EmploymentType'  => 'nullable|string|max:50',
            'ContractType'    => 'nullable|string|max:50',
            'Address'         => 'nullable|string|max:255',
            'NSSFNo'          => 'nullable|string|max:50',
            'NHIFNo'          => 'nullable|string|max:50',
            'KRAPIN'          => 'nullable|string|max:50',
            'BasicSalary'     => 'required|numeric|min:0',
            'PaymentMode'     => 'required|string|max:50',
            'BankName'        => 'nullable|string|max:150',
            'BankBranch'      => 'nullable|string|max:150',
            'BankAccount'     => 'nullable|string|max:100',
            'Status'          => ['nullable', 'string', 'max:20', Rule::in(self::STATUSES)],
            'SalaryEffectiveFrom' => 'nullable|date',
            'Photo'           => 'nullable|image|max:5120',
            // Contacts
            'contact_name.*'  => 'nullable|string|max:150',
            'contact_relation.*' => 'nullable|string|max:100',
            'contact_phone.*' => 'nullable|string|max:50',
            'contact_email.*' => 'nullable|email|max:150',
            'contact_is_next_of_kin.*' => 'nullable|boolean',
            'contact_is_primary.*' => 'nullable|boolean',
            'contact_is_emergency.*' => 'nullable|boolean',
            // Education
            'edu_level.*'     => 'nullable|string|max:100',
            'edu_institution.*' => 'nullable|string|max:200',
            'edu_course.*'    => 'nullable|string|max:200',
            'edu_year_from.*' => 'nullable|digits:4',
            'edu_year_to.*'   => 'nullable|digits:4',
            'edu_grade.*'     => 'nullable|string|max:50',
            // Documents
            'documents.*'     => 'nullable|file|max:5120',
            'documents_category.*' => 'nullable|string|max:100',
            'documents_description.*' => 'nullable|string|max:255',
        ]);

        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $salaryChanged = $data['BasicSalary'] != $employee->BasicSalary;

        if ($request->hasFile('Photo')) {
            $data['PhotoPath'] = $request->file('Photo')->store('employee-photos', 'public');
        }

        $employee->update($data);
        $this->syncContacts($employee, $request);
        $this->syncEducation($employee, $request);
        $this->syncDocuments($employee, $request);

        if ($salaryChanged) {
            $salaryEffective = $request->input('SalaryEffectiveFrom') ?: now()->toDateString();
            EmployeeSalaryHistory::create([
                'EmployeeID'   => $employee->Id,
                'BasicSalary'  => $employee->BasicSalary,
                'EffectiveFrom'=> $salaryEffective,
                'Notes'        => 'Updated salary',
                'CreatedBy'    => auth()->id(),
                'CreatedOn'    => now(),
            ]);
        }

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        $employee->update([
            'Status'    => 'Deactivated',
            'StatusReason' => 'Deactivated via list action',
            'StatusChangedBy' => auth()->id(),
            'StatusChangedOn' => now(),
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Employee deactivated successfully.');
    }

    public function statusForm($id)
    {
        $employee = Employee::with(['branch', 'department'])->findOrFail($id);
        $statusList = self::STATUSES;
        return view('hr.employees.status', compact('employee', 'statusList'));
    }

    public function statusUpdate(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $data = $request->validate([
            'Status' => ['required', Rule::in(self::STATUSES)],
            'StatusReason' => ['nullable', 'string', 'max:255'],
        ]);

        $flags = [
            'IsActive' => in_array($data['Status'], ['Active', 'Pending', 'OnHold'], true) ? 1 : 0,
        ];

        $employee->update(array_merge($data, [
            'StatusChangedBy' => auth()->id(),
            'StatusChangedOn' => now(),
            'ModifiedBy'      => auth()->id(),
            'ModifiedOn'      => now(),
        ], $flags));

        return redirect()
            ->route('hr.employees.index')
            ->with('success', 'Employee status updated.');
    }

    private function syncContacts(Employee $employee, Request $request): void
    {
        $names = $request->input('contact_name', []);
        $relations = $request->input('contact_relation', []);
        $phones = $request->input('contact_phone', []);
        $emails = $request->input('contact_email', []);
        $nextOfKin = $request->input('contact_is_next_of_kin', []);
        $primary = $request->input('contact_is_primary', []);
        $emergency = $request->input('contact_is_emergency', []);

        EmployeeContact::where('EmployeeID', $employee->Id)->delete();

        foreach ($names as $idx => $name) {
            if (! $name) {
                continue;
            }
            EmployeeContact::create([
                'EmployeeID'   => $employee->Id,
                'Name'         => $name,
                'Relation'     => $relations[$idx] ?? null,
                'Phone'        => $phones[$idx] ?? null,
                'Email'        => $emails[$idx] ?? null,
                'IsPrimary'    => isset($primary[$idx]) ? (bool)$primary[$idx] : false,
                'IsNextOfKin'  => isset($nextOfKin[$idx]) ? (bool)$nextOfKin[$idx] : false,
                'IsEmergency'  => isset($emergency[$idx]) ? (bool)$emergency[$idx] : true,
                'CreatedBy'    => auth()->id(),
                'CreatedOn'    => now(),
            ]);
        }
    }

    private function syncDocuments(Employee $employee, Request $request): void
    {
        if (! $request->hasFile('documents')) {
            return;
        }

        foreach ($request->file('documents', []) as $idx => $file) {
            if (! $file) {
                continue;
            }
            $storedPath = $file->store('employee-docs', 'public');
            EmployeeDocument::create([
                'EmployeeID'  => $employee->Id,
                'FileName'    => $file->getClientOriginalName(),
                'FilePath'    => $storedPath,
                'Category'    => $request->input("documents_category.$idx") ?: null,
                'Description' => $request->input("documents_description.$idx") ?: null,
                'UploadedBy'  => auth()->id(),
                'UploadedOn'  => now(),
            ]);
        }
    }

    private function syncEducation(Employee $employee, Request $request): void
    {
        $levels = $request->input('edu_level', []);
        $institutions = $request->input('edu_institution', []);
        $courses = $request->input('edu_course', []);
        $yearFrom = $request->input('edu_year_from', []);
        $yearTo = $request->input('edu_year_to', []);
        $grades = $request->input('edu_grade', []);

        // Clear existing if any new education input provided
        if ($levels || $institutions || $courses) {
            EmployeeEducation::where('EmployeeID', $employee->Id)->delete();
        }

        foreach ($levels as $idx => $lvl) {
            if (! $lvl && empty($institutions[$idx]) && empty($courses[$idx])) {
                continue;
            }
            EmployeeEducation::create([
                'EmployeeID'  => $employee->Id,
                'Level'       => $lvl ?: null,
                'Institution' => $institutions[$idx] ?? null,
                'Course'      => $courses[$idx] ?? null,
                'YearFrom'    => $yearFrom[$idx] ?? null,
                'YearTo'      => $yearTo[$idx] ?? null,
                'Grade'       => $grades[$idx] ?? null,
                'CreatedBy'   => auth()->id(),
                'CreatedOn'   => now(),
            ]);
        }
    }
}
