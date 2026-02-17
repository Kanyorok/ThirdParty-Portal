@extends('layouts.app')

@section('title', 'New Training Session')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Training Session</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.training.sessions.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.training.sessions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Program *</label>
                        <select name="ProgramID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->Id }}" @selected(old('ProgramID') == $program->Id)>{{ $program->Title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Session Code</label>
                        <input type="text" name="SessionCode" class="form-control" value="{{ old('SessionCode') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="StartDate" class="form-control" value="{{ old('StartDate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="EndDate" class="form-control" value="{{ old('EndDate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="StartTime" class="form-control" value="{{ old('StartTime') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="EndTime" class="form-control" value="{{ old('EndTime') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <input type="text" name="Location" class="form-control" value="{{ old('Location') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Online Link</label>
                        <input type="text" name="OnlineLink" class="form-control" value="{{ old('OnlineLink') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trainer</label>
                        <select name="TrainerID" class="form-select">
                            <option value="">Select</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->Id }}" @selected(old('TrainerID') == $trainer->Id)>{{ $trainer->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Participants</label>
                        <input type="number" name="MaxParticipants" class="form-control" value="{{ old('MaxParticipants') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            <option value="Planned" @selected(old('Status', 'Planned') === 'Planned')>Planned</option>
                            <option value="Open" @selected(old('Status') === 'Open')>Open for enrollment</option>
                            <option value="Ongoing" @selected(old('Status') === 'Ongoing')>Ongoing</option>
                            <option value="Completed" @selected(old('Status') === 'Completed')>Completed</option>
                            <option value="Cancelled" @selected(old('Status') === 'Cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Agenda File</label>
                        <input type="file" name="AgendaFile" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Materials File</label>
                        <input type="file" name="MaterialsFile" class="form-control">
                    </div>
                </div>

                <hr class="my-4">

                <h5>Enroll Participants</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Enroll by Department</label>
                        <select name="EnrollDepartments[]" id="enrollDepartmentSelect" class="form-select" multiple>
                            @foreach($departments as $department)
                                <option value="{{ $department->Id }}" @selected(in_array($department->Id, old('EnrollDepartments', []), true))>{{ $department->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Enroll by Grade</label>
                        <select name="EnrollGrades[]" id="enrollGradeSelect" class="form-select" multiple>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(in_array($grade->Id, old('EnrollGrades', []), true))>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Enroll by Role</label>
                        <select name="EnrollRoles[]" id="enrollRoleSelect" class="form-select" multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(in_array($role->Id, old('EnrollRoles', []), true))>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <div class="text-muted small">Choose one filter type; other filters will be disabled.</div>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-12">
                        <label class="form-label">Select Employees</label>
                        <select name="EmployeeIDs[]" id="employeeSelect" class="form-select" multiple>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}"
                                        data-department-id="{{ $employee->DepartmentID }}"
                                        data-grade-id="{{ $employee->GradeID }}"
                                        data-role-id="{{ $employee->RoleID }}"
                                        @selected(in_array($employee->Id, old('EmployeeIDs', []), true))>
                                    {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deptSelect = document.getElementById('enrollDepartmentSelect');
        const gradeSelect = document.getElementById('enrollGradeSelect');
        const roleSelect = document.getElementById('enrollRoleSelect');
        const employeeSelect = document.getElementById('employeeSelect');

        if (!deptSelect || !gradeSelect || !roleSelect || !employeeSelect) {
            return;
        }

        const employeeOptions = Array.from(employeeSelect.options);

        function selectedValues(select) {
            return Array.from(select.selectedOptions).map((option) => option.value).filter(Boolean);
        }

        function clearSelect(select) {
            Array.from(select.options).forEach((option) => {
                option.selected = false;
            });
        }

        function updateExclusive() {
            const deptValues = selectedValues(deptSelect);
            const gradeValues = selectedValues(gradeSelect);
            const roleValues = selectedValues(roleSelect);

            if (deptValues.length) {
                gradeSelect.disabled = true;
                roleSelect.disabled = true;
                clearSelect(gradeSelect);
                clearSelect(roleSelect);
            } else if (gradeValues.length) {
                deptSelect.disabled = true;
                roleSelect.disabled = true;
                clearSelect(deptSelect);
                clearSelect(roleSelect);
            } else if (roleValues.length) {
                deptSelect.disabled = true;
                gradeSelect.disabled = true;
                clearSelect(deptSelect);
                clearSelect(gradeSelect);
            } else {
                deptSelect.disabled = false;
                gradeSelect.disabled = false;
                roleSelect.disabled = false;
            }
        }

        function filterEmployees() {
            const deptValues = selectedValues(deptSelect);
            const gradeValues = selectedValues(gradeSelect);
            const roleValues = selectedValues(roleSelect);

            let filterType = null;
            let filterValues = [];

            if (deptValues.length) {
                filterType = 'department';
                filterValues = deptValues;
            } else if (gradeValues.length) {
                filterType = 'grade';
                filterValues = gradeValues;
            } else if (roleValues.length) {
                filterType = 'role';
                filterValues = roleValues;
            }

            employeeOptions.forEach((option) => {
                if (!filterType) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                let value = '';
                if (filterType === 'department') {
                    value = option.dataset.departmentId || '';
                } else if (filterType === 'grade') {
                    value = option.dataset.gradeId || '';
                } else if (filterType === 'role') {
                    value = option.dataset.roleId || '';
                }

                const matches = value && filterValues.includes(value);
                option.hidden = !matches;
                option.disabled = !matches;
                if (!matches) {
                    option.selected = false;
                }
            });
        }

        function applyFilters() {
            updateExclusive();
            filterEmployees();
        }

        deptSelect.addEventListener('change', applyFilters);
        gradeSelect.addEventListener('change', applyFilters);
        roleSelect.addEventListener('change', applyFilters);

        applyFilters();
    });
</script>
@endsection
