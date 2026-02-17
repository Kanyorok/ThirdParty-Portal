@extends('layouts.app')

@section('title', 'Company Summary Register')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Company Summary Register</h2>
        <div class="d-flex gap-2 d-print-none">
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.reports.summary.export', $run->Id) }}">Download Excel</a>
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
                <div class="text-muted small">Total Basic</div>
                <div class="h6 mb-0">{{ number_format($totals['Basic'] ?? 0, 2) }}</div>
            </div>
            <div>
                <div class="text-muted small">Total Allowances</div>
                <div class="h6 mb-0">{{ number_format($totals['Allowances'] ?? 0, 2) }}</div>
            </div>
            <div>
                <div class="text-muted small">Total Deductions</div>
                <div class="h6 mb-0">{{ number_format($totals['Deductions'] ?? 0, 2) }}</div>
            </div>
            <div>
                <div class="text-muted small">Total Net</div>
                <div class="h6 mb-0">{{ number_format($totals['Net'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Allowance Summary</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Allowance</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allowanceTotals as $row)
                                <tr>
                                    <td>{{ $row->Name }}</td>
                                    <td class="text-end">{{ number_format((float)$row->Total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No allowances.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Deduction Summary</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Deduction</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deductionTotals as $row)
                                <tr>
                                    <td>{{ $row->Name }}</td>
                                    <td class="text-end">{{ number_format((float)$row->Total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">No deductions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
