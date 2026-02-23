@extends('layouts.app')

@section('title', 'Staff Loan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Staff Loan</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.loans.index') }}">Back</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Employee</div>
                    <div class="fw-semibold">{{ $loan->employee?->FirstName }} {{ $loan->employee?->LastName }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Reference</div>
                    <div class="fw-semibold">{{ $loan->LoanRef ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Status</div>
                    <div class="fw-semibold">{{ $loan->Status }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Principal</div>
                    <div class="fw-semibold">{{ number_format($loan->Principal, 2) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Balance</div>
                    <div class="fw-semibold">{{ number_format((float)($loan->Balance ?? 0), 2) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Interest Rate</div>
                    <div class="fw-semibold">{{ number_format($loan->InterestRate ?? 0, 4) }}%</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Monthly Repayment</div>
                    <div class="fw-semibold">{{ number_format($loan->InstallmentAmount, 2) }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Tenure (Months)</div>
                    <div class="fw-semibold">{{ (int)$loan->TenureMonths }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Start Date</div>
                    <div class="fw-semibold">{{ $loan->StartDate ? $loan->StartDate->format('Y-m-d') : '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">End Date</div>
                    <div class="fw-semibold">{{ $loan->EndDate ? $loan->EndDate->format('Y-m-d') : '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">Loan Repayment Schedule</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Principal</th>
                        <th>Interest</th>
                        <th>Total Due</th>
                        <th>Status</th>
                        <th>Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedule as $row)
                        <tr>
                            <td>{{ optional($row->DueDate)->format('m/Y') ?? '-' }}</td>
                            <td>{{ number_format((float)($row->PrincipalComponent ?? 0), 2) }}</td>
                            <td>{{ number_format((float)($row->InterestComponent ?? 0), 2) }}</td>
                            <td>{{ number_format((float)($row->TotalDue ?? 0), 2) }}</td>
                            <td>{{ $row->Status }}</td>
                            <td>{{ optional($row->PaidOn)->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No repayment schedule found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
