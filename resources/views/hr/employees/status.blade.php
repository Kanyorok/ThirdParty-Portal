@extends('layouts.app')

@section('title', 'Change Employee Status')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Change Status</h2>
            <div class="text-muted">
                {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }}) • {{ $employee->branch->Name ?? '-' }} / {{ $employee->department->Name ?? '-' }}
            </div>
        </div>
        <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-secondary">Back to Employees</a>
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

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.employees.status.update', $employee->EmployeeNo) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">New Status *</label>
                        <select name="Status" class="form-select" required>
                            @foreach($statusList as $status)
                                <option value="{{ $status }}" @selected(old('Status', $employee->Status) == $status)>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Reason / Note</label>
                        <input type="text" name="StatusReason" class="form-control" value="{{ old('StatusReason', $employee->StatusReason) }}" placeholder="e.g. Returned from leave, exited, onboarding pending docs">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Save Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
