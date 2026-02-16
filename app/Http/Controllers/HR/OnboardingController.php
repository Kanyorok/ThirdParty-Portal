<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeDocument;
use App\Models\HR\EmployeeSalaryHistory;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HR\OnboardingQueue;
use App\Models\HR\OnboardingTask;
use App\Models\HRM\Department;
use App\Services\HR\PayrollMandatoryAllocator;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    private const STATUSES = ['Pending', 'InProgress', 'Completed', 'Converted', 'Cancelled'];

    public function index(Request $request)
    {
        $query = OnboardingQueue::with(['application.applicant', 'offer', 'employee'])
            ->orderByDesc('Id');

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $queues = $query->paginate(20);
        $statusList = self::STATUSES;

        return view('hr.recruitment.onboarding.index', compact('queues', 'statusList'));
    }

    public function show($id)
    {
        $queue = OnboardingQueue::with([
            'application.applicant',
            'application.opening',
            'offer',
            'tasks',
            'employee',
        ])->findOrFail($id);

        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        $statusList = self::STATUSES;

        return view('hr.recruitment.onboarding.show', compact('queue', 'departments', 'branches', 'grades', 'roles', 'statusList'));
    }

    public function addTask(Request $request, $id)
    {
        $queue = OnboardingQueue::findOrFail($id);
        $data = $request->validate([
            'Title' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsRequired' => ['nullable', 'boolean'],
            'DueDate' => ['nullable', 'date'],
        ]);

        OnboardingTask::create([
            'OnboardingID' => $queue->Id,
            'Title' => $data['Title'],
            'Description' => $data['Description'] ?? null,
            'IsRequired' => $request->boolean('IsRequired', true),
            'DueDate' => $data['DueDate'] ?? null,
            'Status' => 'Pending',
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        if ($queue->Status === 'Pending') {
            $queue->update([
                'Status' => 'InProgress',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }

        return redirect()->route('hr.recruitment.onboarding.show', $queue->Id)->with('success', 'Task added.');
    }

    public function completeTask($id, $taskId)
    {
        $queue = OnboardingQueue::findOrFail($id);
        $task = OnboardingTask::where('OnboardingID', $queue->Id)->where('Id', $taskId)->firstOrFail();
        $task->update([
            'Status' => 'Completed',
            'CompletedBy' => auth()->id(),
            'CompletedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $remaining = OnboardingTask::where('OnboardingID', $queue->Id)
            ->where('Status', '!=', 'Completed')
            ->count();
        if ($remaining === 0 && $queue->Status !== 'Converted') {
            $queue->update([
                'Status' => 'Completed',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }

        return redirect()->route('hr.recruitment.onboarding.show', $queue->Id)->with('success', 'Task marked as completed.');
    }

    public function convert(Request $request, $id)
    {
        $queue = OnboardingQueue::with(['application.applicant', 'application.opening', 'offer'])->findOrFail($id);
        if ($queue->EmployeeID) {
            return redirect()->route('hr.recruitment.onboarding.show', $queue->Id)
                ->withErrors(['status' => 'Employee already created for this onboarding record.']);
        }

        $data = $request->validate([
            'EmployeeNo' => 'required|string|max:50|unique:t_HREmployees,EmployeeNo',
            'FirstName' => 'required|string|max:100',
            'LastName' => 'required|string|max:100',
            'OtherNames' => 'nullable|string|max:100',
            'Email' => 'nullable|email|max:150',
            'Phone' => 'nullable|string|max:50',
            'Gender' => 'nullable|string|max:20',
            'DateOfBirth' => 'nullable|date',
            'BranchID' => 'required|integer|exists:t_Branches,Id',
            'DepartmentID' => 'required|integer|exists:t_Departments,Id',
            'GradeID' => 'nullable|integer|exists:t_HRJobGrades,Id',
            'RoleID' => 'nullable|integer|exists:t_HRJobRoles,Id',
            'EmploymentDate' => 'nullable|date',
            'EmploymentType' => 'nullable|string|max:50',
            'ContractType' => 'nullable|string|max:50',
            'BasicSalary' => 'required|numeric|min:0',
            'PaymentMode' => 'required|string|max:50',
        ]);

        $employee = Employee::create([
            'EmployeeNo' => $data['EmployeeNo'],
            'FirstName' => $data['FirstName'],
            'LastName' => $data['LastName'],
            'OtherNames' => $data['OtherNames'] ?? null,
            'Email' => $data['Email'] ?? null,
            'Phone' => $data['Phone'] ?? null,
            'Gender' => $data['Gender'] ?? null,
            'DateOfBirth' => $data['DateOfBirth'] ?? null,
            'BranchID' => $data['BranchID'],
            'DepartmentID' => $data['DepartmentID'],
            'GradeID' => $data['GradeID'] ?? null,
            'RoleID' => $data['RoleID'] ?? null,
            'EmploymentDate' => $data['EmploymentDate'] ?? null,
            'EmploymentType' => $data['EmploymentType'] ?? null,
            'ContractType' => $data['ContractType'] ?? null,
            'BasicSalary' => $data['BasicSalary'],
            'PaymentMode' => $data['PaymentMode'],
            'Status' => 'Pending',
            'IsActive' => 1,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        EmployeeSalaryHistory::create([
            'EmployeeID' => $employee->Id,
            'BasicSalary' => $employee->BasicSalary,
            'EffectiveFrom' => $employee->EmploymentDate ?? now()->toDateString(),
            'Notes' => 'Onboarding conversion',
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        $this->copyApplicationDocuments($queue, $employee);

        app(PayrollMandatoryAllocator::class)->syncForEmployee($employee, now()->month, now()->year);

        $queue->update([
            'EmployeeID' => $employee->Id,
            'Status' => 'Converted',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.employees.show', $employee->Id)
            ->with('success', 'Employee created from onboarding.');
    }

    private function copyApplicationDocuments(OnboardingQueue $queue, Employee $employee): void
    {
        $application = $queue->application;
        if (! $application) {
            return;
        }

        $documents = $application->documents ?? collect();
        foreach ($documents as $doc) {
            EmployeeDocument::create([
                'EmployeeID' => $employee->Id,
                'FileName' => $doc->FileName,
                'FilePath' => $doc->FilePath,
                'Category' => $doc->Category ?? 'Application Document',
                'Description' => $doc->Description ?? null,
                'UploadedBy' => auth()->id(),
                'UploadedOn' => now(),
            ]);
        }

        if ($application->ResumePath) {
            EmployeeDocument::create([
                'EmployeeID' => $employee->Id,
                'FileName' => 'Resume',
                'FilePath' => $application->ResumePath,
                'Category' => 'Resume',
                'UploadedBy' => auth()->id(),
                'UploadedOn' => now(),
            ]);
        }
        if ($application->CoverLetterPath) {
            EmployeeDocument::create([
                'EmployeeID' => $employee->Id,
                'FileName' => 'Cover Letter',
                'FilePath' => $application->CoverLetterPath,
                'Category' => 'Cover Letter',
                'UploadedBy' => auth()->id(),
                'UploadedOn' => now(),
            ]);
        }
    }
}
