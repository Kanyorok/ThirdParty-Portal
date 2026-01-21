@extends('layouts.app')

@section('title', 'Master Register')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Master Register</h2>
        <div class="d-flex gap-2 d-print-none">
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.reports.master.export', $run->Id) }}">Download Excel</a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Print</button>
            <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.runs.show', $run->Id) }}">Back</a>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-4">
            <div>
                <div class="text-muted small">Cycle</div>
                <div class="h6 mb-0">{{ $run->cycle?->Month }}/{{ $run->cycle?->Year }}</div>
            </div>
            <div>
                <div class="text-muted small">Employees</div>
                <div class="h6 mb-0">{{ $run->lines->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th class="text-end">Basic</th>
                        <th class="text-end">Allowances</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($run->lines as $line)
                        <tr>
                            <td>{{ $line->employee?->FirstName }} {{ $line->employee?->LastName }}</td>
                            <td>{{ $line->employee?->branch?->Name ?? '-' }}</td>
                            <td>{{ $line->employee?->department?->Name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($line->BasicSalary, 2) }}</td>
                            <td class="text-end">{{ number_format($line->TotalAllowances, 2) }}</td>
                            <td class="text-end">{{ number_format($line->TotalDeductions, 2) }}</td>
                            <td class="text-end">{{ number_format($line->NetPay, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No payroll lines.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
