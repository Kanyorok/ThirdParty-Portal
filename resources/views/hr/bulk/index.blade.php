@extends('layouts.app')

@section('title', 'Bulk Uploads')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Bulk Uploads</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Employee Upload</h5>
                    <p class="text-muted mb-3">Create or update employees in bulk.</p>
                    <a class="btn btn-outline-primary" href="{{ route('hr.bulk.employees') }}">Open</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Payroll Uploads</h5>
                    <p class="text-muted mb-3">Bulk salary, allowances, and deductions.</p>
                    <a class="btn btn-outline-primary" href="{{ route('hr.bulk.payroll') }}">Open</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Attendance Upload</h5>
                    <p class="text-muted mb-3">Upload raw attendance logs.</p>
                    <a class="btn btn-outline-primary" href="{{ route('hr.bulk.attendance') }}">Open</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>KPI Targets Upload</h5>
                    <p class="text-muted mb-3">Upload KPI targets per employee.</p>
                    <a class="btn btn-outline-primary" href="{{ route('hr.bulk.kpi-targets') }}">Open</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
