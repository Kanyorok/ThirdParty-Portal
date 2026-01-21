@extends('layouts.app')

@section('title', 'New Shared Document')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Shared Document</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.shared-docs.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.shared-docs.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select name="CategoryID" class="form-select">
                            <option value="">Select</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->Id }}" @selected(old('CategoryID') == $category->Id)>{{ $category->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Title *</label>
                        <input type="text" name="Title" class="form-control" value="{{ old('Title') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Version</label>
                        <input type="text" name="Version" class="form-control" value="{{ old('Version') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective Date</label>
                        <input type="date" name="EffectiveDate" class="form-control" value="{{ old('EffectiveDate') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="ExpiryDate" class="form-control" value="{{ old('ExpiryDate') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Acknowledgement Due</label>
                        <input type="date" name="AcknowledgementDueOn" class="form-control" value="{{ old('AcknowledgementDueOn') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Owner Department</label>
                        <select name="OwnerDepartmentID" class="form-select">
                            <option value="">Select</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->Id }}" @selected(old('OwnerDepartmentID') == $department->Id)>{{ $department->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Access Level *</label>
                        <select name="AccessLevel" id="AccessLevel" class="form-select" required>
                            @foreach($accessLevels as $level)
                                <option value="{{ $level }}" @selected(old('AccessLevel', 'Public') === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6" id="departmentBlock">
                        <label class="form-label">Allowed Departments</label>
                        <select name="Departments[]" class="form-select" multiple>
                            @foreach($departments as $department)
                                <option value="{{ $department->Id }}" @selected(in_array($department->Id, old('Departments', []), true))>{{ $department->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6" id="roleBlock">
                        <label class="form-label">Allowed Roles</label>
                        <select name="Roles[]" class="form-select" multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->Id }}" @selected(in_array($role->Id, old('Roles', []), true))>{{ $role->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Language</label>
                        <input type="text" name="Language" class="form-control" value="{{ old('Language') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            <option value="Draft" @selected(old('Status') === 'Draft')>Draft</option>
                            <option value="Published" @selected(old('Status') === 'Published')>Published</option>
                            <option value="Archived" @selected(old('Status') === 'Archived')>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsDownloadable" value="1" id="IsDownloadable" @checked(old('IsDownloadable', true))>
                            <label class="form-check-label" for="IsDownloadable">Downloadable</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsMandatory" value="1" id="IsMandatory" @checked(old('IsMandatory'))>
                            <label class="form-check-label" for="IsMandatory">Mandatory Acknowledgement</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Document File</label>
                        <input type="file" name="DocumentFile" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="3">{{ old('Description') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Save Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const accessSelect = document.getElementById('AccessLevel');
        const deptBlock = document.getElementById('departmentBlock');
        const roleBlock = document.getElementById('roleBlock');

        function updateVisibility() {
            const value = accessSelect.value;
            deptBlock.style.display = value === 'Department' ? '' : 'none';
            roleBlock.style.display = value === 'Role' ? '' : 'none';
        }

        accessSelect.addEventListener('change', updateVisibility);
        updateVisibility();
    });
</script>
@endsection
