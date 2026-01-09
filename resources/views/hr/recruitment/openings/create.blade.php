@extends('layouts.app')

@section('title', 'New Job Opening')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Job Opening</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.openings.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.recruitment.openings.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Requisition</label>
                        <select name="RequisitionID" class="form-select">
                            <option value="">Select</option>
                            @foreach($requisitions as $req)
                                <option value="{{ $req->Id }}" @selected(old('RequisitionID', $requisition?->Id) == $req->Id)>
                                    {{ $req->Code }} - {{ $req->Title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Title *</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title', $requisition?->Title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <select name="DepartmentID" class="form-select">
                            <option value="">Select</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->Id }}" @selected(old('DepartmentID', $requisition?->DepartmentID) == $dept->Id)>{{ $dept->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>
                        <select name="BranchID" class="form-select">
                            <option value="">Select</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}" @selected(old('BranchID', $requisition?->BranchID) == $branch->Id)>{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Job Grade</label>
                        <select name="GradeID" class="form-select">
                            <option value="">Select</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(old('GradeID', $requisition?->GradeID) == $grade->Id)>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Job Role</label>
                        <select name="RoleID" class="form-select">
                            <option value="">Select</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('RoleID', $requisition?->RoleID) == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employment Type</label>
                        <input type="text" name="EmploymentType" class="form-control" value="{{ old('EmploymentType', $requisition?->EmploymentType) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contract Type</label>
                        <input type="text" name="ContractType" class="form-control" value="{{ old('ContractType', $requisition?->ContractType) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vacancies *</label>
                        <input type="number" name="Vacancies" class="form-control" min="1" value="{{ old('Vacancies', $requisition?->Vacancies ?? 1) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Close Date</label>
                        <input type="date" name="CloseDate" class="form-control" value="{{ old('CloseDate') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="3">{{ old('Description') }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Requirements</label>
                        <textarea name="Requirements" class="form-control" rows="3">{{ old('Requirements') }}</textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Interview Questions</label>
                        @if($questionGroups->isEmpty())
                            <div class="text-muted">No questions in the library yet.</div>
                        @else
                            <div class="border rounded p-3" style="max-height: 300px; overflow:auto;">
                                @foreach($questionGroups as $groupName => $questions)
                                    <div class="mb-3">
                                        <div class="fw-semibold">{{ $groupName }}</div>
                                        @foreach($questions as $question)
                                            <label class="d-flex gap-2 align-items-center mb-2">
                                                <input type="checkbox" name="QuestionIDs[]" value="{{ $question->Id }}"
                                                    {{ in_array($question->Id, old('QuestionIDs', $selectedQuestions), true) ? 'checked' : '' }}>
                                                <span>{{ $question->Title }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" name="Action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
                    <button type="submit" name="Action" value="publish" class="btn btn-primary">Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
