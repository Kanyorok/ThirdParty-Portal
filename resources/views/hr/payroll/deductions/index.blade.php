@extends('layouts.app')

@section('title', 'Monthly Deductions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Monthly Deductions</h2>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.deductions.index', ['month' => $month, 'year' => $year]) }}">Refresh</a>
            <a class="btn btn-primary" href="{{ route('hr.payroll.deductions.create') }}">New Deduction</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <form method="GET" class="col-12 col-lg-7 row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <input type="number" name="month" class="form-control" min="1" max="12" value="{{ $month }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control" min="2000" max="2100" value="{{ $year }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
                </form>

                <div class="col-12 col-lg-5 d-flex justify-content-lg-end">
                    <form method="POST" action="{{ route('hr.payroll.syncMandatory') }}">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button class="btn btn-outline-primary" type="submit">Sync Mandatory ({{ $month }}/{{ $year }})</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion" id="deductionsAccordion">
        @forelse($employees as $emp)
            @php
                $rows = $deductionsByEmployee[$emp->Id] ?? collect();
                $total = $rows->sum('Amount');
            @endphp
            <div class="accordion-item mb-2">
                <h2 class="accordion-header" id="heading_{{ $emp->Id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $emp->Id }}">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div>{{ $emp->FirstName }} {{ $emp->LastName }}</div>
                            <div class="text-muted small me-3">Total: {{ number_format($total, 2) }} | Items: {{ $rows->count() }}</div>
                        </div>
                    </button>
                </h2>
                <div id="collapse_{{ $emp->Id }}" class="accordion-collapse collapse" data-bs-parent="#deductionsAccordion">
                    <div class="accordion-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Deduction</th>
                                    <th>Amount</th>
                                    <th>Recurring</th>
                                    <th>Calc</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ $row->deduction?->Name ?? $row->Name }}</td>
                                        <td>
                                            @if($row->IsAutoCalculated && (float)$row->Amount == 0.0)
                                                <span class="text-muted">Auto</span>
                                            @else
                                                {{ number_format($row->Amount, 2) }}
                                            @endif
                                        </td>
                                        <td>{{ $row->IsRecurring ? 'Yes' : 'No' }}</td>
                                        <td>{{ $row->IsAutoCalculated ? 'Auto' : 'Manual' }}</td>
                                        <td>{{ $row->Status }}</td>
                                        <td class="text-end">
                                            @if($row->Status === 'Pending')
                                                <form method="POST" action="{{ route('hr.payroll.deductions.approve', $row->Id) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('hr.payroll.deductions.reject', $row->Id) }}" class="d-inline ms-1">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No deductions for this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-3">No employees found.</div>
        @endforelse
    </div>
</div>
@endsection
