<div>
    <!-- You must be the change you wish to see in the world. - Mahatma Gandhi -->
</div>
@extends('layouts.app')

@section('title', 'Leave Balances')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
        <div>
            <h2 class="mb-0">Leave Balances</h2>
            <p class="text-muted mb-0">
                Annual leave schemes (IDs 1 &amp; 2) accrue by 1/12 of their entitlement every month.
                All other eligible leaves are loaded once per year (or manually) using the button beside it.
            </p>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('hr.leave.balances.accrue') }}">
                @csrf
                <button class="btn btn-outline-primary" type="submit">Run Monthly Accrual</button>
            </form>
            <form method="POST" action="{{ route('hr.leave.balances.load_yearly') }}">
                @csrf
                <button class="btn btn-outline-secondary" type="submit">Load Yearly Balances</button>
            </form>
        </div>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Leave Type Filter</label>
                    <select class="form-select" name="leave_type_filter">
                        <option value="annual" {{ $filter === 'annual' ? 'selected' : '' }}>Annual leaves (monthly accrual)</option>
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All leave types</option>
                        @foreach($leaveTypes as $type)
                            <option value="type-{{ $type->Id }}" {{ $filter === 'type-'.$type->Id ? 'selected' : '' }}>
                                {{ $type->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">Apply Filter</button>
                </div>
                <div class="col-auto">
                    <a class="btn btn-outline-secondary" href="{{ route('hr.leave.balances.export', ['leave_type_filter' => $filter]) }}">
                        Export to Excel
                    </a>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-secondary" type="button" onclick="window.print()">Print</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if($groupedBalances->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted">
                No balances.
            </div>
        </div>
    @else
        @foreach($groupedBalances as $employeeId => $balances)
            @php
                $employee = $balances->first()->employee;
                $counter = rand(100, 9999);
            @endphp
            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ trim(($employee->FirstName ?? '') . ' ' . ($employee->LastName ?? '')) }}</strong>
                        <div class="small text-muted">{{ $balances->count() }} leave type(s)</div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                            data-bs-target="#employee-{{ $employeeId }}-{{ $counter }}" aria-expanded="false">
                        View balances
                    </button>
                </div>
                <div id="employee-{{ $employeeId }}-{{ $counter }}" class="collapse">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr class="table-light">
                                    <th>Leave Type</th>
                                    <th>Entitlement</th>
                                    <th>Accrued</th>
                                    <th>Taken</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($balances as $bal)
                                    <tr>
                                        <td>{{ $bal->type->Name ?? '' }}</td>
                                        <td>{{ number_format($bal->Entitlement, 2) }}</td>
                                        <td>{{ number_format($bal->Accrued, 2) }}</td>
                                        <td>{{ number_format($bal->Taken, 2) }}</td>
                                        <td>{{ number_format($bal->Balance, 2) }}</td>
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
