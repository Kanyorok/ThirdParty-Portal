@extends('layouts.app')

@section('title', 'Edit Acting Assignment')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Acting Assignment</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.movements.acting.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.movements.acting.update', $assignment->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee</label>
                        <select name="EmployeeID" class="form-select">
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}" @selected(old('EmployeeID', $assignment->EmployeeID) == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Acting Branch</label>
                        <select name="ActingBranchID" class="form-select">
                            <option value="">Select</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}" @selected(old('ActingBranchID', $assignment->ActingBranchID) == $branch->Id)>{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Acting Department</label>
                        <select name="ActingDepartmentID" class="form-select">
                            <option value="">Select</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->Id }}" @selected(old('ActingDepartmentID', $assignment->ActingDepartmentID) == $dept->Id)>{{ $dept->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Acting Role</label>
                        <select name="ActingRoleID" class="form-select">
                            <option value="">Select</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('ActingRoleID', $assignment->ActingRoleID) == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reference Salary (acting position/person)</label>
                        <input type="number" step="0.01" name="ActingReferenceSalary" class="form-control" value="{{ old('ActingReferenceSalary', $assignment->ActingReferenceSalary) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Acting Allowance Rate (%)</label>
                        <input type="number" step="0.01" name="ActingAllowanceRate" class="form-control" value="{{ old('ActingAllowanceRate', $assignment->ActingAllowanceRate ?? 20) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="StartDate" class="form-control" value="{{ old('StartDate', optional($assignment->StartDate)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="date" name="EndDate" class="form-control" value="{{ old('EndDate', optional($assignment->EndDate)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            @foreach(['Pending','Approved','Rejected'] as $st)
                                <option value="{{ $st }}" @selected(old('Status', $assignment->Status) == $st)>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason</label>
                        <textarea name="Reason" class="form-control" rows="2">{{ old('Reason', $assignment->Reason) }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
