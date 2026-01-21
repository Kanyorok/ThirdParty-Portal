@extends('layouts.app')

@section('title', 'New Training Program')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Training Program</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.training.programs.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.training.programs.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Title *</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="CategoryID" class="form-select">
                            <option value="">Select</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}" @selected(old('CategoryID') == $category->Id)>{{ $category->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Delivery Mode</label>
                        <select name="DeliveryMode" class="form-select">
                            <option value="">Select</option>
                            @foreach($deliveryModes as $mode)
                                <option value="{{ $mode }}" @selected(old('DeliveryMode') === $mode)>{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Duration (hours)</label>
                        <input type="number" step="0.01" name="DurationHours" class="form-control" value="{{ old('DurationHours') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Objectives</label>
                        <textarea name="Objectives" class="form-control" rows="3">{{ old('Objectives') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Audience (notes)</label>
                        <textarea name="TargetAudience" class="form-control" rows="3">{{ old('TargetAudience') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Budgeted Cost</label>
                        <input type="number" step="0.01" name="BudgetedCost" class="form-control" value="{{ old('BudgetedCost') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Actual Cost</label>
                        <input type="number" step="0.01" name="ActualCost" class="form-control" value="{{ old('ActualCost') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsMandatory" value="1" id="IsMandatory" @checked(old('IsMandatory'))>
                            <label class="form-check-label" for="IsMandatory">Mandatory</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="HasCertification" value="1" id="HasCertification" @checked(old('HasCertification'))>
                            <label class="form-check-label" for="HasCertification">Certification</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            <option value="Active" @selected(old('Status', 'Active') === 'Active')>Active</option>
                            <option value="Inactive" @selected(old('Status') === 'Inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Target Departments</label>
                        <select name="TargetDepartments[]" class="form-select" multiple>
                            @foreach($departments as $department)
                                <option value="{{ $department->Id }}" @selected(in_array($department->Id, old('TargetDepartments', []), true))>{{ $department->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Target Grades</label>
                        <select name="TargetGrades[]" class="form-select" multiple>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(in_array($grade->Id, old('TargetGrades', []), true))>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Target Roles</label>
                        <select name="TargetRoles[]" class="form-select" multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(in_array($role->Id, old('TargetRoles', []), true))>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Program</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
