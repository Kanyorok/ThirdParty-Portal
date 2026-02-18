@extends('layouts.app')

@section('title', 'New Sanction')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Sanction</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.sanctions.index') }}">Back</a>
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
            <form action="{{ route('hr.discipline.sanctions.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="Code" class="form-control" value="{{ old('Code') }}" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="2">{{ old('Description') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Employment Status</label>
                        <input type="text" name="EmploymentStatus" class="form-control" value="{{ old('EmploymentStatus') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Default Duration (days)</label>
                        <input type="number" name="DefaultDurationDays" class="form-control" value="{{ old('DefaultDurationDays') }}">
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsSuspension" value="1" @checked(old('IsSuspension'))>
                            <label class="form-check-label">Suspension</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="SuspensionWithoutPay" value="1" @checked(old('SuspensionWithoutPay'))>
                            <label class="form-check-label">Suspension Without Pay</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="AffectsPayroll" value="1" @checked(old('AffectsPayroll'))>
                            <label class="form-check-label">Affects Payroll</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="BlocksLeave" value="1" @checked(old('BlocksLeave'))>
                            <label class="form-check-label">Blocks Leave</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="UpdatesEmploymentStatus" value="1" @checked(old('UpdatesEmploymentStatus'))>
                            <label class="form-check-label">Updates Employment Status</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.discipline.sanctions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Sanction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
