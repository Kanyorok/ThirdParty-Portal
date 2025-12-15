@extends('layouts.app')

@section('title', 'Payroll Run')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Payroll Run #{{ $run->Id }}</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.runs.index') }}">Back</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-4">
            <div>
                <div class="text-muted small">Cycle</div>
                <div class="h6 mb-0">{{ $run->cycle?->Month }}/{{ $run->cycle?->Year }}</div>
            </div>
            <div>
                <div class="text-muted small">Status</div>
                <div class="h6 mb-0">{{ $run->Status }}</div>
            </div>
            <div>
                <div class="text-muted small">Generated On</div>
                <div>{{ $run->GeneratedOn ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">Employees</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Basic</th>
                        <th>Allowances</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($run->lines as $line)
                        <tr>
                            <td>{{ $line->employee?->FirstName }} {{ $line->employee?->LastName }}</td>
                            <td>{{ number_format($line->BasicSalary, 2) }}</td>
                            <td>{{ number_format($line->TotalAllowances, 2) }}</td>
                            <td>{{ number_format($line->TotalDeductions + $line->StatutoryDeductions + $line->LoanDeductions, 2) }}</td>
                            <td>{{ number_format($line->NetPay, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No lines yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
