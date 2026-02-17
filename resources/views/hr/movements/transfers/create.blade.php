@extends('layouts.app')

@section('title', 'New Transfer')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Transfer Request</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.movements.transfers.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.movements.transfers.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee *</label>
                        <select name="EmployeeID" id="EmployeeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}"
                                    data-branch="{{ $emp->BranchID }}"
                                    data-department="{{ $emp->DepartmentID }}"
                                    data-role="{{ $emp->RoleID }}"
                                    @selected(old('EmployeeID') == $emp->Id)>
                                    {{ $emp->FirstName }} {{ $emp->LastName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Branch</label>
                        <select name="FromBranchID" class="form-select">
                            <option value="">Select</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}" @selected(old('FromBranchID') == $branch->Id)>{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Branch</label>
                        <select name="ToBranchID" class="form-select">
                            <option value="">Select</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}" @selected(old('ToBranchID') == $branch->Id)>{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Department</label>
                        <select name="FromDepartmentID" class="form-select">
                            <option value="">Select</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->Id }}" @selected(old('FromDepartmentID') == $dept->Id)>{{ $dept->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Department</label>
                        <select name="ToDepartmentID" class="form-select">
                            <option value="">Select</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->Id }}" @selected(old('ToDepartmentID') == $dept->Id)>{{ $dept->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">From Role</label>
                        <select name="FromRoleID" class="form-select">
                            <option value="">Select</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('FromRoleID') == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Role</label>
                        <select name="ToRoleID" class="form-select">
                            <option value="">Select</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(old('ToRoleID') == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective Date *</label>
                        <input type="date" name="EffectiveDate" class="form-control" value="{{ old('EffectiveDate') }}" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Reason</label>
                        <textarea name="Reason" class="form-control" rows="2">{{ old('Reason') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const employeeSelect = document.getElementById('EmployeeID');
    const mapSelect = (id, value) => {
        if (!value) return;
        const el = document.querySelector(`select[name='${id}']`);
        if (el) el.value = value;
    };
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            const branch = option.getAttribute('data-branch');
            const dept = option.getAttribute('data-department');
            const role = option.getAttribute('data-role');
            mapSelect('FromBranchID', branch);
            mapSelect('FromDepartmentID', dept);
            mapSelect('FromRoleID', role);
            // Default target to current values for convenience
            mapSelect('ToBranchID', branch);
            mapSelect('ToDepartmentID', dept);
            mapSelect('ToRoleID', role);
        });
    }
});
</script>
@endpush
@endsection
