@extends('layouts.app')

@section('title', 'Edit Trainer')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Trainer</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.training.trainers.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.training.trainers.update', $trainer->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Trainer Type *</label>
                        <select name="TrainerType" id="TrainerType" class="form-select" required>
                            <option value="Internal" @selected(old('TrainerType', $trainer->TrainerType) === 'Internal')>Internal</option>
                            <option value="External" @selected(old('TrainerType', $trainer->TrainerType) === 'External')>External</option>
                        </select>
                    </div>
                    <div class="col-md-9" id="internalBlock">
                        <label class="form-label">Employee *</label>
                        <select name="EmployeeID" class="form-select">
                            <option value="">Select</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->Id }}" @selected(old('EmployeeID', $trainer->EmployeeID) == $employee->Id)>
                                    {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4" id="externalNameBlock">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $trainer->Name) }}">
                    </div>
                    <div class="col-md-4" id="externalEmailBlock">
                        <label class="form-label">Email</label>
                        <input type="email" name="Email" class="form-control" value="{{ old('Email', $trainer->Email) }}">
                    </div>
                    <div class="col-md-4" id="externalPhoneBlock">
                        <label class="form-label">Phone</label>
                        <input type="text" name="Phone" class="form-control" value="{{ old('Phone', $trainer->Phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expertise</label>
                        <textarea name="Expertise" class="form-control" rows="3">{{ old('Expertise', $trainer->Expertise) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Certifications</label>
                        <textarea name="Certifications" class="form-control" rows="3">{{ old('Certifications', $trainer->Certifications) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rate</label>
                        <input type="number" step="0.01" name="Rate" class="form-control" value="{{ old('Rate', $trainer->Rate) }}">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $trainer->IsActive))>
                            <label class="form-check-label" for="IsActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">Update Trainer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('TrainerType');
        const internalBlock = document.getElementById('internalBlock');
        const externalName = document.getElementById('externalNameBlock');
        const externalEmail = document.getElementById('externalEmailBlock');
        const externalPhone = document.getElementById('externalPhoneBlock');

        function toggleBlocks() {
            const isInternal = typeSelect.value === 'Internal';
            internalBlock.style.display = isInternal ? '' : 'none';
            externalName.style.display = isInternal ? 'none' : '';
            externalEmail.style.display = isInternal ? 'none' : '';
            externalPhone.style.display = isInternal ? 'none' : '';
        }

        typeSelect.addEventListener('change', toggleBlocks);
        toggleBlocks();
    });
</script>
@endsection
