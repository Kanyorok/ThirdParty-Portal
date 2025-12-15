@extends('layouts.app')

@section('title', 'New Staff Loan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">New Staff Loan</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.loans.index') }}">Back</a>
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
            <form method="POST" action="{{ route('hr.payroll.loans.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Employee *</label>
                        <select name="EmployeeID" class="form-select" required>
                            <option value="">Select</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->Id }}" @selected(old('EmployeeID') == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Loan Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', 'Staff Loan') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Principal *</label>
                        <input type="number" step="0.01" name="Principal" class="form-control" value="{{ old('Principal') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Interest Rate (% p.a)</label>
                        <input type="number" step="0.0001" name="InterestRate" class="form-control" value="{{ old('InterestRate', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tenure (Months)</label>
                        <input type="number" name="TenureMonths" class="form-control" value="{{ old('TenureMonths', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="StartDate" class="form-control" value="{{ old('StartDate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="EndDate" class="form-control" value="{{ old('EndDate') }}">
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
