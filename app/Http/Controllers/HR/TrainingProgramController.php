<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HR\TrainingCategory;
use App\Models\HR\TrainingProgram;
use App\Models\HR\TrainingProgramTarget;
use App\Models\HRM\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingProgram::with('category');

        if ($request->filled('category_id')) {
            $query->where('CategoryID', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('mandatory')) {
            $query->where('IsMandatory', (int)$request->mandatory);
        }

        $programs = $query->orderBy('Title')->paginate(30);
        $categories = TrainingCategory::orderBy('Name')->get();
        $statusList = ['Active', 'Inactive'];

        return view('hr.training.programs.index', compact('programs', 'categories', 'statusList'));
    }

    public function create()
    {
        $categories = TrainingCategory::orderBy('Name')->get();
        $departments = Department::orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::orderBy('Name')->get(['Id', 'Name']);
        $deliveryModes = ['Classroom', 'Online', 'Blended'];

        return view('hr.training.programs.create', compact('categories', 'departments', 'grades', 'roles', 'deliveryModes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', 'unique:t_HRTrainingPrograms,Code'],
            'Title' => ['required', 'string', 'max:200'],
            'CategoryID' => ['nullable', 'exists:t_HRTrainingCategories,Id'],
            'DeliveryMode' => ['nullable', 'string', 'max:50'],
            'DurationHours' => ['nullable', 'numeric', 'min:0'],
            'Objectives' => ['nullable', 'string'],
            'TargetAudience' => ['nullable', 'string'],
            'BudgetedCost' => ['nullable', 'numeric', 'min:0'],
            'ActualCost' => ['nullable', 'numeric', 'min:0'],
            'IsMandatory' => ['sometimes', 'boolean'],
            'HasCertification' => ['sometimes', 'boolean'],
            'Status' => ['nullable', 'string', 'max:30'],
            'TargetDepartments' => ['array'],
            'TargetDepartments.*' => ['integer', 'exists:t_Departments,Id'],
            'TargetGrades' => ['array'],
            'TargetGrades.*' => ['integer', 'exists:t_HRJobGrades,Id'],
            'TargetRoles' => ['array'],
            'TargetRoles.*' => ['integer', 'exists:t_HRJobRoles,Id'],
        ]);

        $data['IsMandatory'] = $request->boolean('IsMandatory', false);
        $data['HasCertification'] = $request->boolean('HasCertification', false);
        $data['Status'] = $data['Status'] ?? 'Active';
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        $program = TrainingProgram::create($data);

        $this->syncTargets($program, $request);

        return redirect()->route('hr.training.programs.index')
            ->with('success', 'Training program created.');
    }

    public function show($id)
    {
        $program = TrainingProgram::with(['category', 'targets', 'sessions'])->findOrFail($id);
        $departmentIds = $program->targets->where('TargetType', 'Department')->pluck('TargetID')->all();
        $gradeIds = $program->targets->where('TargetType', 'Grade')->pluck('TargetID')->all();
        $roleIds = $program->targets->where('TargetType', 'Role')->pluck('TargetID')->all();

        $targetNames = [
            'departments' => $departmentIds ? Department::whereIn('Id', $departmentIds)->pluck('Name')->implode(', ') : '',
            'grades' => $gradeIds ? JobGrade::whereIn('Id', $gradeIds)->pluck('Name')->implode(', ') : '',
            'roles' => $roleIds ? JobRole::whereIn('Id', $roleIds)->pluck('Name')->implode(', ') : '',
        ];

        return view('hr.training.programs.show', compact('program', 'targetNames'));
    }

    public function edit($id)
    {
        $program = TrainingProgram::with('targets')->findOrFail($id);
        $categories = TrainingCategory::orderBy('Name')->get();
        $departments = Department::orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::orderBy('Name')->get(['Id', 'Name']);
        $deliveryModes = ['Classroom', 'Online', 'Blended'];

        $targetDepartments = $program->targets->where('TargetType', 'Department')->pluck('TargetID')->all();
        $targetGrades = $program->targets->where('TargetType', 'Grade')->pluck('TargetID')->all();
        $targetRoles = $program->targets->where('TargetType', 'Role')->pluck('TargetID')->all();

        return view('hr.training.programs.edit', compact(
            'program',
            'categories',
            'departments',
            'grades',
            'roles',
            'deliveryModes',
            'targetDepartments',
            'targetGrades',
            'targetRoles'
        ));
    }

    public function update(Request $request, $id)
    {
        $program = TrainingProgram::findOrFail($id);

        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', Rule::unique('t_HRTrainingPrograms', 'Code')->ignore($program->Id, 'Id')],
            'Title' => ['required', 'string', 'max:200'],
            'CategoryID' => ['nullable', 'exists:t_HRTrainingCategories,Id'],
            'DeliveryMode' => ['nullable', 'string', 'max:50'],
            'DurationHours' => ['nullable', 'numeric', 'min:0'],
            'Objectives' => ['nullable', 'string'],
            'TargetAudience' => ['nullable', 'string'],
            'BudgetedCost' => ['nullable', 'numeric', 'min:0'],
            'ActualCost' => ['nullable', 'numeric', 'min:0'],
            'IsMandatory' => ['sometimes', 'boolean'],
            'HasCertification' => ['sometimes', 'boolean'],
            'Status' => ['nullable', 'string', 'max:30'],
            'TargetDepartments' => ['array'],
            'TargetDepartments.*' => ['integer', 'exists:t_Departments,Id'],
            'TargetGrades' => ['array'],
            'TargetGrades.*' => ['integer', 'exists:t_HRJobGrades,Id'],
            'TargetRoles' => ['array'],
            'TargetRoles.*' => ['integer', 'exists:t_HRJobRoles,Id'],
        ]);

        $data['IsMandatory'] = $request->boolean('IsMandatory', false);
        $data['HasCertification'] = $request->boolean('HasCertification', false);
        $data['Status'] = $data['Status'] ?? $program->Status;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $program->update($data);
        $this->syncTargets($program, $request, true);

        return redirect()->route('hr.training.programs.index')
            ->with('success', 'Training program updated.');
    }

    private function syncTargets(TrainingProgram $program, Request $request, bool $refresh = false): void
    {
        if ($refresh) {
            TrainingProgramTarget::where('ProgramID', $program->Id)->delete();
        }

        $targets = [];
        foreach ((array)$request->input('TargetDepartments', []) as $departmentId) {
            $targets[] = ['TargetType' => 'Department', 'TargetID' => $departmentId];
        }
        foreach ((array)$request->input('TargetGrades', []) as $gradeId) {
            $targets[] = ['TargetType' => 'Grade', 'TargetID' => $gradeId];
        }
        foreach ((array)$request->input('TargetRoles', []) as $roleId) {
            $targets[] = ['TargetType' => 'Role', 'TargetID' => $roleId];
        }

        if (empty($targets)) {
            return;
        }

        $now = now();
        $insert = array_map(function ($target) use ($program, $now) {
            return [
                'ProgramID' => $program->Id,
                'TargetType' => $target['TargetType'],
                'TargetID' => $target['TargetID'],
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
            ];
        }, $targets);

        TrainingProgramTarget::insert($insert);
    }
}
