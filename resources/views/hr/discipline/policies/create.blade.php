@extends('layouts.app')

@section('title', 'New Policy')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Create Policy</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.discipline.policies.index') }}">Back</a>
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
            <form action="{{ route('hr.discipline.policies.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="EffectiveFrom" class="form-control" value="{{ old('EffectiveFrom') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="EffectiveTo" class="form-control" value="{{ old('EffectiveTo') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employment Types</label>
                        <input type="text" name="EmploymentTypes" class="form-control" value="{{ old('EmploymentTypes') }}" placeholder="Permanent, Contract">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contract Types</label>
                        <input type="text" name="ContractTypes" class="form-control" value="{{ old('ContractTypes') }}" placeholder="Fixed, Casual">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Progressive Rules</label>
                        <textarea name="ProgressiveRules" class="form-control" rows="3">{{ old('ProgressiveRules') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Appeal Deadline (days) *</label>
                        <input type="number" name="AppealDeadlineDays" class="form-control" value="{{ old('AppealDeadlineDays', 7) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Retention (months) *</label>
                        <input type="number" name="RetentionMonths" class="form-control" value="{{ old('RetentionMonths', 24) }}" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="AllowDirectHearing" value="1">
                            <label class="form-check-label">Allow Direct Hearing</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('hr.discipline.policies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
