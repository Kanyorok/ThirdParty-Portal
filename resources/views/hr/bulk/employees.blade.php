@extends('layouts.app')

@section('title', 'Bulk Employee Upload')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Bulk Employee Upload</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.bulk.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.bulk.employees.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Upload Excel/CSV file</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="col-md-4 text-end">
                        <a class="btn btn-outline-primary" href="{{ route('hr.bulk.employees.template') }}">Download Template</a>
                        <button class="btn btn-primary ms-2" type="submit">Upload</button>
                    </div>
                </div>
            </form>
            <hr>
            <div class="small text-muted">
                Required: EmployeeNo, FirstName, LastName, Branch, Department, BasicSalary.
                Optional: Grade, Role, Supervisor, PaymentMode, Bank, BankBranch, BankAccount, Status, IsActive, SalaryEffectiveFrom.
            </div>
        </div>
    </div>
</div>
@endsection
