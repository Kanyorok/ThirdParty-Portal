@extends('layouts.app')

@section('title', 'Department Summary')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Department Summary</h2>
        <div class="d-flex gap-2 d-print-none">
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.reports.departments.export', $run->Id) }}">Download Excel</a>
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
        </div>
    </div>

    @forelse($groups as $department => $lines)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white">{{ $department }}</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th class="text-end">Basic</th>
                            <th class="text-end">Allowances</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end">Net Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                            <tr>
                                <td>{{ $line->employee?->FirstName }} {{ $line->employee?->LastName }}</td>
                                <td class="text-end">{{ number_format($line->BasicSalary, 2) }}</td>
                                <td class="text-end">{{ number_format($line->TotalAllowances, 2) }}</td>
                                <td class="text-end">{{ number_format($line->TotalDeductions, 2) }}</td>
                                <td class="text-end">{{ number_format($line->NetPay, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-end">Totals</th>
                            <th class="text-end">{{ number_format($lines->sum('BasicSalary'), 2) }}</th>
                            <th class="text-end">{{ number_format($lines->sum('TotalAllowances'), 2) }}</th>
                            <th class="text-end">{{ number_format($lines->sum('TotalDeductions'), 2) }}</th>
                            <th class="text-end">{{ number_format($lines->sum('NetPay'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted">No payroll lines.</div>
        </div>
    @endforelse
</div>
@endsection
