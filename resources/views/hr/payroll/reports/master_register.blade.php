@extends('layouts.app')

@section('title', 'Master Register')

@push('styles')
<style>
    @media print {
        .d-print-none { display: none !important; }
        .table { font-size: 9px; }
        .card { border: none; box-shadow: none; }
    }
    .master-register-table {
        font-size: 0.875rem;
        white-space: nowrap;
    }
    .master-register-table th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 0.5rem 0.3rem;
    }
    .master-register-table td {
        padding: 0.4rem 0.3rem;
    }
    .master-register-table .text-success {
        color: #198754 !important;
    }
    .master-register-table .text-danger {
        color: #dc3545 !important;
    }
    .master-register-table .text-primary {
        color: #0d6efd !important;
    }
</style>
@endpush

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
            <div>
                <div class="text-muted small">Allowance Types</div>
                <div class="h6 mb-0">{{ count($allowanceNames) }}</div>
            </div>
            <div>
                <div class="text-muted small">Deduction Types</div>
                <div class="h6 mb-0">{{ count($deductionNames) }}</div>
            </div>
            <div>
                <div class="text-muted small">Employer Contribution Types</div>
                <div class="h6 mb-0">{{ count($employerContributionNames) }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 table-sm table-bordered master-register-table">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2">Employee</th>
                            <th rowspan="2">Branch</th>
                            <th rowspan="2">Department</th>
                            <th rowspan="2" class="text-end">Basic</th>
                            @if(count($allowanceNames) > 0)
                                <th colspan="{{ count($allowanceNames) }}" class="text-center bg-success bg-opacity-10">ALLOWANCES</th>
                            @endif
                            <th rowspan="2" class="text-end fw-bold">Gross Pay</th>
                            @if(count($deductionNames) > 0)
                                <th colspan="{{ count($deductionNames) }}" class="text-center bg-danger bg-opacity-10">DEDUCTIONS</th>
                            @endif
                            @if(count($employerContributionNames) > 0)
                                <th colspan="{{ count($employerContributionNames) }}" class="text-center bg-primary bg-opacity-10">EMPLOYER CONTRIBUTIONS</th>
                            @endif
                            <th rowspan="2" class="text-end fw-bold">Net Pay</th>
                        </tr>
                        <tr>
                            @foreach($allowanceNames as $allowanceName)
                                <th class="text-end text-success bg-success bg-opacity-10">{{ $allowanceName }}</th>
                            @endforeach
                            @foreach($deductionNames as $deductionName)
                                <th class="text-end text-danger bg-danger bg-opacity-10">{{ $deductionName }}</th>
                            @endforeach
                            @foreach($employerContributionNames as $contributionName)
                                <th class="text-end text-primary bg-primary bg-opacity-10">{{ $contributionName }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalBasic = 0;
                            $totalGrossPay = 0;
                            $totalAllowances = array_fill_keys($allowanceNames, 0);
                            $totalDeductions = array_fill_keys($deductionNames, 0);
                            $totalEmployerContributions = array_fill_keys($employerContributionNames, 0);
                            $totalNetPay = 0;
                        @endphp
                        @forelse($run->lines as $line)
                            @php
                                $empAllowances = $allowances->get($line->EmployeeID, collect());
                                $empDeductions = $deductions->get($line->EmployeeID, collect());
                                $empContributions = $employerContributions->get($line->EmployeeID, collect());
                                $totalBasic += $line->BasicSalary;
                                $totalGrossPay += $line->GrossPay;
                                $totalNetPay += $line->NetPay;
                            @endphp
                            <tr>
                                <td>{{ $line->employee?->FirstName }} {{ $line->employee?->LastName }}</td>
                                <td>{{ $line->employee?->branch?->Name ?? '-' }}</td>
                                <td>{{ $line->employee?->department?->Name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($line->BasicSalary, 2) }}</td>
                                @foreach($allowanceNames as $allowanceName)
                                    @php
                                        $amount = $empAllowances->where('Name', $allowanceName)->sum('Amount');
                                        $totalAllowances[$allowanceName] += $amount;
                                    @endphp
                                    <td class="text-end text-success">{{ $amount > 0 ? number_format($amount, 2) : '-' }}</td>
                                @endforeach
                                <td class="text-end fw-bold">{{ number_format($line->GrossPay, 2) }}</td>
                                @foreach($deductionNames as $deductionName)
                                    @php
                                        $amount = $empDeductions->where('Name', $deductionName)->sum('Amount');
                                        $totalDeductions[$deductionName] += $amount;
                                    @endphp
                                    <td class="text-end text-danger">{{ $amount > 0 ? number_format($amount, 2) : '-' }}</td>
                                @endforeach
                                @foreach($employerContributionNames as $contributionName)
                                    @php
                                        $amount = $empContributions->filter(function($contrib) use ($contributionName) {
                                            return ($contrib->deduction?->Name ?? 'Unknown') === $contributionName;
                                        })->sum('Amount');
                                        $totalEmployerContributions[$contributionName] += $amount;
                                    @endphp
                                    <td class="text-end text-primary">{{ $amount > 0 ? number_format($amount, 2) : '-' }}</td>
                                @endforeach
                                <td class="text-end fw-bold">{{ number_format($line->NetPay, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 5 + count($allowanceNames) + count($deductionNames) + count($employerContributionNames) + 1 }}" class="text-center text-muted py-3">No payroll lines.</td></tr>
                        @endforelse
                        @if($run->lines->count() > 0)
                            <tr class="table-secondary fw-bold">
                                <td colspan="3" class="text-end">TOTALS:</td>
                                <td class="text-end">{{ number_format($totalBasic, 2) }}</td>
                                @foreach($allowanceNames as $allowanceName)
                                    <td class="text-end text-success">{{ number_format($totalAllowances[$allowanceName], 2) }}</td>
                                @endforeach
                                <td class="text-end">{{ number_format($totalGrossPay, 2) }}</td>
                                @foreach($deductionNames as $deductionName)
                                    <td class="text-end text-danger">{{ number_format($totalDeductions[$deductionName], 2) }}</td>
                                @endforeach
                                @foreach($employerContributionNames as $contributionName)
                                    <td class="text-end text-primary">{{ number_format($totalEmployerContributions[$contributionName], 2) }}</td>
                                @endforeach
                                <td class="text-end">{{ number_format($totalNetPay, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
