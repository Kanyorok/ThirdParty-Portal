@extends('layouts.app')

@section('title', 'Bulk Payroll Uploads')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Bulk Payroll Uploads</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.bulk.index') }}">Back</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5>Salary Upload</h5>
                    <form method="POST" action="{{ route('hr.bulk.salary.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">Upload Excel/CSV file</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="col-md-4 text-end">
                                <a class="btn btn-outline-primary" href="{{ route('hr.bulk.salary.template') }}">Download Template</a>
                                <button class="btn btn-primary ms-2" type="submit">Upload</button>
                            </div>
                        </div>
                    </form>
                    <div class="small text-muted mt-2">Required: EmployeeNo, BasicSalary. Optional: EffectiveFrom, Notes.</div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5>Allowances Upload</h5>
                    <form method="POST" action="{{ route('hr.bulk.allowances.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">Upload Excel/CSV file</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="col-md-4 text-end">
                                <a class="btn btn-outline-primary" href="{{ route('hr.bulk.allowances.template') }}">Download Template</a>
                                <button class="btn btn-primary ms-2" type="submit">Upload</button>
                            </div>
                        </div>
                    </form>
                    <div class="small text-muted mt-2">
                        Mandatory allowances are skipped. If Amount is blank, the current rule is used to compute it.
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Deductions Upload</h5>
                    <form method="POST" action="{{ route('hr.bulk.deductions.import') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">Upload Excel/CSV file</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <div class="col-md-4 text-end">
                                <a class="btn btn-outline-primary" href="{{ route('hr.bulk.deductions.template') }}">Download Template</a>
                                <button class="btn btn-primary ms-2" type="submit">Upload</button>
                            </div>
                        </div>
                    </form>
                    <div class="small text-muted mt-2">
                        Mandatory deductions are skipped. If Amount is blank, deduction rules will be applied during payroll run.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
