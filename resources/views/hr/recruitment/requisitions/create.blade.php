@extends('layouts.app')

@section('title', 'New Job Requisition')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Job Requisition</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.recruitment.requisitions.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.recruitment.requisitions.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ $generatedCode }}" readonly style="background-color: #f8f9fa;">
                        <small class="form-text text-muted">Auto-generated</small>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Title *</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <select name="DepartmentID" id="DepartmentID" class="form-select">
                            <option value="">Select</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->Id }}" @selected(old('DepartmentID') == $dept->Id)>{{ $dept->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>
                        <select name="BranchID" class="form-select">
                            <option value="">Select</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}" @selected(old('BranchID') == $branch->Id)>{{ $branch->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Job Grade</label>
                        <select name="GradeID" id="GradeID" class="form-select">
                            <option value="">Select</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->Id }}" @selected(old('GradeID') == $grade->Id)>{{ $grade->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Job Role</label>
                        <select name="RoleID" id="RoleID" class="form-select">
                            <option value="">Select</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}"
                                        data-department="{{ $role->DepartmentID }}"
                                        data-grade="{{ $role->GradeID }}"
                                        @selected(old('RoleID') == $role->Id)>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employment Type</label>
                        <input type="text" name="EmploymentType" class="form-control" value="{{ old('EmploymentType') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contract Type</label>
                        <input type="text" name="ContractType" class="form-control" value="{{ old('ContractType') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vacancies *</label>
                        <input type="number" name="Vacancies" class="form-control" min="1" value="{{ old('Vacancies', 1) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Priority</label>
                        <select name="Priority" class="form-select">
                            <option value="">Select</option>
                            @foreach(['Low','Medium','High','Urgent'] as $priority)
                                <option value="{{ $priority }}" @selected(old('Priority') === $priority)>{{ $priority }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Justification</label>
                        <textarea name="Justification" class="form-control" rows="3">{{ old('Justification') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" name="Action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
                    <button type="submit" name="Action" value="submit" class="btn btn-primary">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deptSelect = document.getElementById('DepartmentID');
    const gradeSelect = document.getElementById('GradeID');
    const roleSelect = document.getElementById('RoleID');
    if (!deptSelect || !gradeSelect || !roleSelect) return;

    const filterRoles = () => {
        const dept = deptSelect.value;
        const grade = gradeSelect.value;
        let cleared = false;
        Array.from(roleSelect.options).forEach(option => {
            if (!option.value) {
                return;
            }
            const roleDept = option.getAttribute('data-department');
            const roleGrade = option.getAttribute('data-grade');
            const deptMatch = !dept || roleDept === dept;
            const gradeMatch = !grade || roleGrade === grade;
            const match = deptMatch && gradeMatch;
            option.disabled = !match;
            option.hidden = !match;
            if (!match && option.selected) {
                option.selected = false;
                cleared = true;
            }
        });
        if (cleared) {
            roleSelect.value = '';
        }
    };

    deptSelect.addEventListener('change', filterRoles);
    gradeSelect.addEventListener('change', filterRoles);
    filterRoles();
});
</script>
@endpush
@endsection
