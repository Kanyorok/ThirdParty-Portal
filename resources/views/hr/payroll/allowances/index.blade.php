@extends('layouts.app')

@section('title', 'Monthly Allowances')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Monthly Allowances</h2>
        <a class="btn btn-primary" href="{{ route('hr.payroll.allowances.create') }}">New Allowance</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Employee Search</label>
                    <input type="text" name="employee_search" class="form-control" placeholder="Search by name..." value="{{ $employeeSearch ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="">All</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (string)($month ?? '') === (string)$m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="">All</option>
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ (string)($year ?? '') === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary" type="submit">Apply Filter</button>
                </div>
                <div class="col-md-auto">
                    <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.allowances.index') }}">Clear</a>
                </div>
            </form>
        </div>
    </div>

    @if($allowances->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-4">
                No allowances yet.
            </div>
        </div>
    @else
        @foreach($allowances as $employeeId => $employeeAllowances)
            @php
                $employee = $employeeAllowances->first()->employee;
                $counter = rand(100, 9999);
            @endphp
            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ trim(($employee->FirstName ?? '') . ' ' . ($employee->LastName ?? '')) }}</strong>
                        <div class="small text-muted">{{ $employeeAllowances->count() }} allowance(s)</div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                            data-bs-target="#employee-{{ $employeeId }}-{{ $counter }}" aria-expanded="false">
                        View allowances
                    </button>
                </div>
                <div id="employee-{{ $employeeId }}-{{ $counter }}" class="collapse">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr class="table-light">
                                    <th>Allowance</th>
                                    <th>Mandatory</th>
                                    <th class="text-end">Amount</th>
                                    <th>Period</th>
                                    <th>Taxable</th>
                                    <th>Recurring</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employeeAllowances as $row)
                                    <tr>
                                        <td>{{ $row->allowance?->Name ?? $row->Name }}</td>
                                        <td>{{ $row->allowance?->IsMandatory ? 'Yes' : 'No' }}</td>
                                        <td class="text-end">{{ number_format($row->Amount, 2) }}</td>
                                        <td>{{ $row->Month }}/{{ $row->Year }}</td>
                                        <td>{{ $row->IsTaxable ? 'Yes' : 'No' }}</td>
                                        <td>{{ $row->IsRecurring ? 'Yes' : 'No' }}</td>
                                        <td>
                                            @if($row->Status === 'Approved')
                                                <span class="badge bg-success">{{ $row->Status }}</span>
                                            @elseif($row->Status === 'Rejected')
                                                <span class="badge bg-danger">{{ $row->Status }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $row->Status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($row->Status === 'Pending')
                                                <form method="POST" action="{{ route('hr.payroll.allowances.approve', $row->Id) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('hr.payroll.allowances.reject', $row->Id) }}" class="d-inline ms-1">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('hr.payroll.allowances.destroy', $row->Id) }}" class="d-inline ms-1" onsubmit="return confirm('Remove this monthly allowance?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-secondary">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
