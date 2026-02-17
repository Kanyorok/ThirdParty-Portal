@extends('layouts.app')

@section('title', 'Onboarding Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Onboarding Details</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.onboarding.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div><strong>Candidate:</strong> {{ $queue->CandidateName ?? $queue->application?->applicant?->FirstName }} {{ $queue->application?->applicant?->LastName }}</div>
                    <div><strong>Job:</strong> {{ $queue->application?->opening?->Title ?? '-' }}</div>
                    <div><strong>Status:</strong> {{ $queue->Status }}</div>
                </div>
                <div class="col-md-6">
                    <div><strong>Start Date:</strong> {{ $queue->StartDate ? \Carbon\Carbon::parse($queue->StartDate)->format('Y-m-d') : '-' }}</div>
                    <div><strong>Offer Date:</strong> {{ $queue->offer?->OfferDate ? \Carbon\Carbon::parse($queue->offer->OfferDate)->format('Y-m-d') : '-' }}</div>
                    <div><strong>Employee:</strong> {{ $queue->employee ? $queue->employee->FirstName.' '.$queue->employee->LastName : 'Not converted' }}</div>
                </div>
            </div>
            @if($queue->Notes)
                <div class="mt-2"><strong>Notes:</strong> {{ $queue->Notes }}</div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Onboarding Checklist</h6>
            <form method="POST" action="{{ route('hr.recruitment.onboarding.tasks.add', $queue->Id) }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="Title" class="form-control form-control-sm" placeholder="New task" required>
                <button class="btn btn-sm btn-outline-primary" type="submit">Add</button>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Required</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queue->tasks as $task)
                        <tr>
                            <td>{{ $task->Title }}</td>
                            <td>{{ $task->IsRequired ? 'Yes' : 'No' }}</td>
                            <td>{{ $task->Status }}</td>
                            <td>{{ $task->DueDate ? \Carbon\Carbon::parse($task->DueDate)->format('Y-m-d') : '-' }}</td>
                            <td class="text-end">
                                @if($task->Status !== 'Completed')
                                    <form method="POST" action="{{ route('hr.recruitment.onboarding.tasks.complete', [$queue->Id, $task->Id]) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" type="submit">Mark Done</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No tasks defined.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h6 class="mb-0">Convert to Employee</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('hr.recruitment.onboarding.convert', $queue->Id) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Employee No *</label>
                        <input type="text" name="EmployeeNo" class="form-control" value="{{ old('EmployeeNo') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="FirstName" class="form-control" value="{{ old('FirstName', $queue->application?->applicant?->FirstName) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="LastName" class="form-control" value="{{ old('LastName', $queue->application?->applicant?->LastName) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Other Names</label>
                        <input type="text" name="OtherNames" class="form-control" value="{{ old('OtherNames', $queue->application?->applicant?->OtherNames) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="Email" class="form-control" value="{{ old('Email', $queue->application?->applicant?->Email) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="Phone" class="form-control" value="{{ old('Phone', $queue->application?->applicant?->Phone) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="Gender" class="form-select">
                            <option value="">Select</option>
                            @foreach(['Male','Female','Other'] as $gender)
                                <option value="{{ $gender }}" @selected(old('Gender', $queue->application?->applicant?->Gender) === $gender)>{{ $gender }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Branch *</label>
                        <select name="BranchID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}" @selected(old('BranchID', $queue->application?->opening?->BranchID) == $branch->Id)>{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department *</label>
                        <select name="DepartmentID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->Id }}" @selected(old('DepartmentID', $queue->application?->opening?->DepartmentID) == $dept->Id)>{{ $dept->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Grade</label>
                        <select name="GradeID" class="form-select">
                            <option value="">Select</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(old('GradeID', $queue->application?->opening?->GradeID) == $grade->Id)>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <select name="RoleID" class="form-select">
                            <option value="">Select</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('RoleID', $queue->application?->opening?->RoleID) == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employment Date</label>
                        <input type="date" name="EmploymentDate" class="form-control" value="{{ old('EmploymentDate', $queue->StartDate) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employment Type</label>
                        <input type="text" name="EmploymentType" class="form-control" value="{{ old('EmploymentType', $queue->application?->opening?->EmploymentType) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contract Type</label>
                        <input type="text" name="ContractType" class="form-control" value="{{ old('ContractType', $queue->application?->opening?->ContractType) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Basic Salary *</label>
                        <input type="number" step="0.01" name="BasicSalary" class="form-control" value="{{ old('BasicSalary', $queue->offer?->SalaryOffered) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Mode *</label>
                        <input type="text" name="PaymentMode" class="form-control" value="{{ old('PaymentMode', 'Bank') }}" required>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Convert to Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
