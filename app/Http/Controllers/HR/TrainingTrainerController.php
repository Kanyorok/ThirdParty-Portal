<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\TrainingTrainer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrainingTrainerController extends Controller
{
    public function index()
    {
        $trainers = TrainingTrainer::with('employee')->orderBy('Name')->paginate(30);
        return view('hr.training.trainers.index', compact('trainers'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id', 'FirstName', 'LastName', 'EmployeeNo']);
        return view('hr.training.trainers.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'TrainerType' => ['required', 'in:Internal,External'],
            'EmployeeID' => ['nullable', 'exists:t_HREmployees,Id'],
            'Name' => ['nullable', 'string', 'max:150'],
            'Email' => ['nullable', 'email', 'max:150'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Expertise' => ['nullable', 'string'],
            'Certifications' => ['nullable', 'string'],
            'Rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['TrainerType'] === 'Internal' && empty($data['EmployeeID'])) {
            return back()->withErrors(['EmployeeID' => 'Select an employee for internal trainers.'])->withInput();
        }

        if ($data['TrainerType'] === 'External' && empty($data['Name'])) {
            return back()->withErrors(['Name' => 'Enter the trainer name for external trainers.'])->withInput();
        }

        if ($data['TrainerType'] === 'Internal' && empty($data['Name'])) {
            $employee = Employee::find($data['EmployeeID']);
            if ($employee) {
                $data['Name'] = trim($employee->FirstName . ' ' . $employee->LastName);
            }
        }

        $data['IsActive'] = 1;
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        TrainingTrainer::create($data);

        return redirect()->route('hr.training.trainers.index')
            ->with('success', 'Trainer added.');
    }

    public function edit($id)
    {
        $trainer = TrainingTrainer::findOrFail($id);
        $employees = Employee::orderBy('FirstName')->get(['Id', 'FirstName', 'LastName', 'EmployeeNo']);
        return view('hr.training.trainers.edit', compact('trainer', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $trainer = TrainingTrainer::findOrFail($id);

        $data = $request->validate([
            'TrainerType' => ['required', 'in:Internal,External'],
            'EmployeeID' => ['nullable', 'exists:t_HREmployees,Id'],
            'Name' => ['nullable', 'string', 'max:150'],
            'Email' => ['nullable', 'email', 'max:150'],
            'Phone' => ['nullable', 'string', 'max:50'],
            'Expertise' => ['nullable', 'string'],
            'Certifications' => ['nullable', 'string'],
            'Rate' => ['nullable', 'numeric', 'min:0'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        if ($data['TrainerType'] === 'Internal' && empty($data['EmployeeID'])) {
            return back()->withErrors(['EmployeeID' => 'Select an employee for internal trainers.'])->withInput();
        }

        if ($data['TrainerType'] === 'External' && empty($data['Name'])) {
            return back()->withErrors(['Name' => 'Enter the trainer name for external trainers.'])->withInput();
        }

        if ($data['TrainerType'] === 'Internal' && empty($data['Name'])) {
            $employee = Employee::find($data['EmployeeID']);
            if ($employee) {
                $data['Name'] = trim($employee->FirstName . ' ' . $employee->LastName);
            }
        }

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $trainer->IsActive;
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $trainer->update($data);

        return redirect()->route('hr.training.trainers.index')
            ->with('success', 'Trainer updated.');
    }

    public function destroy($id)
    {
        $trainer = TrainingTrainer::findOrFail($id);
        $trainer->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.training.trainers.index')
            ->with('success', 'Trainer deactivated.');
    }
}
