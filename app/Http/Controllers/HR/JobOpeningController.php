<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\HR\JobGrade;
use App\Models\HR\JobInterviewQuestion;
use App\Models\HR\JobOpening;
use App\Models\HR\JobOpeningQuestion;
use App\Models\HR\JobRequisition;
use App\Models\HR\JobRole;
use App\Models\HRM\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobOpeningController extends Controller
{
    private const STATUSES = ['Draft', 'Open', 'Closed'];

    public function index(Request $request)
    {
        $query = JobOpening::with(['department', 'branch', 'grade', 'role', 'requisition'])
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

        $openings = $query->paginate(20);
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $statusList = self::STATUSES;

        return view('hr.recruitment.openings.index', compact('openings', 'departments', 'branches', 'statusList'));
    }

    public function create(Request $request)
    {
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        $requisitions = JobRequisition::where('Status', 'Approved')->orderByDesc('Id')->get();
        $statusList = self::STATUSES;
        $requisition = null;
        $questionGroups = JobInterviewQuestion::with('group')
            ->where('IsActive', 1)
            ->orderBy('Title')
            ->get()
            ->groupBy(fn ($question) => $question->group?->Name ?? 'General');
        $selectedQuestions = [];

        if ($request->filled('requisition_id')) {
            $requisition = $requisitions->firstWhere('Id', (int)$request->requisition_id);
        }

        // Auto-generate opening code
        $generatedCode = $this->generateOpeningCode();

        return view('hr.recruitment.openings.create', compact(
            'departments',
            'branches',
            'grades',
            'roles',
            'requisitions',
            'statusList',
            'requisition',
            'questionGroups',
            'selectedQuestions',
            'generatedCode'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'RequisitionID' => ['nullable', 'integer', 'exists:t_HRJobRequisitions,Id'],
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRJobOpenings,Code'],
            'Title' => ['required', 'string', 'max:150'],
            'DepartmentID' => ['nullable', 'integer', 'exists:t_Departments,Id'],
            'BranchID' => ['nullable', 'integer', 'exists:t_Branches,Id'],
            'GradeID' => ['nullable', 'integer', 'exists:t_HRJobGrades,Id'],
            'RoleID' => ['nullable', 'integer', 'exists:t_Roles,id'],
            'EmploymentType' => ['nullable', 'string', 'max:50'],
            'ContractType' => ['nullable', 'string', 'max:50'],
            'Vacancies' => ['required', 'integer', 'min:1'],
            'Description' => ['nullable', 'string'],
            'Requirements' => ['nullable', 'string'],
            'CloseDate' => ['nullable', 'date'],
            'QuestionIDs' => ['nullable', 'array'],
            'QuestionIDs.*' => ['integer', 'exists:t_HRJobInterviewQuestions,Id'],
        ]);

        $action = $request->input('Action', 'draft');
        $data['Status'] = $action === 'publish' ? 'Open' : 'Draft';
        $data['PublishedOn'] = $action === 'publish' ? now() : null;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        $opening = JobOpening::create($data);

        $questionIds = $data['QuestionIDs'] ?? [];
        foreach ($questionIds as $questionId) {
            JobOpeningQuestion::create([
                'JobOpeningID' => $opening->Id,
                'QuestionID' => $questionId,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        return redirect()->route('hr.recruitment.openings.index')->with('success', 'Job opening saved.');
    }

    public function edit($id)
    {
        $opening = JobOpening::findOrFail($id);
        $departments = Department::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $branches = Branch::whereNull('DeletedOn')->orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);
        $requisitions = JobRequisition::where('Status', 'Approved')->orderByDesc('Id')->get();
        $statusList = self::STATUSES;
        $questionGroups = JobInterviewQuestion::with('group')
            ->where('IsActive', 1)
            ->orderBy('Title')
            ->get()
            ->groupBy(fn ($question) => $question->group?->Name ?? 'General');
        $selectedQuestions = $opening->questions()->pluck('t_HRJobInterviewQuestions.Id')->all();

        return view('hr.recruitment.openings.edit', compact(
            'opening',
            'departments',
            'branches',
            'grades',
            'roles',
            'requisitions',
            'statusList',
            'questionGroups',
            'selectedQuestions'
        ));
    }

    public function show($id)
    {
        $opening = JobOpening::with(['department', 'branch', 'grade', 'role', 'requisition', 'applications.applicant', 'questions'])
            ->findOrFail($id);

        return view('hr.recruitment.openings.show', compact('opening'));
    }

    public function update(Request $request, $id)
    {
        $opening = JobOpening::findOrFail($id);
        $data = $request->validate([
            'RequisitionID' => ['nullable', 'integer', 'exists:t_HRJobRequisitions,Id'],
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRJobOpenings', 'Code')->ignore($opening->Id, 'Id')],
            'Title' => ['required', 'string', 'max:150'],
            'DepartmentID' => ['nullable', 'integer', 'exists:t_Departments,Id'],
            'BranchID' => ['nullable', 'integer', 'exists:t_Branches,Id'],
            'GradeID' => ['nullable', 'integer', 'exists:t_HRJobGrades,Id'],
            'RoleID' => ['nullable', 'integer', 'exists:t_Roles,id'],
            'EmploymentType' => ['nullable', 'string', 'max:50'],
            'ContractType' => ['nullable', 'string', 'max:50'],
            'Vacancies' => ['required', 'integer', 'min:1'],
            'Description' => ['nullable', 'string'],
            'Requirements' => ['nullable', 'string'],
            'CloseDate' => ['nullable', 'date'],
            'QuestionIDs' => ['nullable', 'array'],
            'QuestionIDs.*' => ['integer', 'exists:t_HRJobInterviewQuestions,Id'],
        ]);

        $action = $request->input('Action');
        if ($action === 'publish' && $opening->Status !== 'Open') {
            $data['Status'] = 'Open';
            $data['PublishedOn'] = $opening->PublishedOn ?? now();
        }
        if ($action === 'close') {
            $data['Status'] = 'Closed';
        }

        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $opening->update($data);

        JobOpeningQuestion::where('JobOpeningID', $opening->Id)->delete();
        $questionIds = $data['QuestionIDs'] ?? [];
        foreach ($questionIds as $questionId) {
            JobOpeningQuestion::create([
                'JobOpeningID' => $opening->Id,
                'QuestionID' => $questionId,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        return redirect()->route('hr.recruitment.openings.index')->with('success', 'Job opening updated.');
    }

    public function close($id)
    {
        $opening = JobOpening::findOrFail($id);
        $opening->update([
            'Status' => 'Closed',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.openings.index')->with('success', 'Job opening closed.');
    }

    /**
     * Generate a unique opening code
     * Format: JOB-YYYY-NNNN (e.g., JOB-2026-0001)
     */
    private function generateOpeningCode(): string
    {
        $year = now()->year;
        $prefix = "JOB-{$year}-";

        // Get the last opening for this year
        $lastOpening = JobOpening::where('Code', 'like', "{$prefix}%")
            ->orderByDesc('Code')
            ->first();

        if ($lastOpening) {
            // Extract the number from the last code and increment
            $lastNumber = (int) substr($lastOpening->Code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            // First opening of the year
            $newNumber = 1;
        }

        // Format with leading zeros (4 digits)
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
