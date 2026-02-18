@extends('layouts.app')

@section('title', 'Leave Balances')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Leave Balances</h2>
            <p class="text-muted mb-0">Manage and view employee leave entitlements and balances.</p>
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

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search Employee</label>
                    <input type="text" name="employee_search" class="form-control" placeholder="Name or Employee No..." value="{{ $search }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Leave Type</label>
                    <select class="form-select" name="leave_type_filter">
                        <option value="annual" {{ $filter === 'annual' ? 'selected' : '' }}>Annual leaves (Monthly)</option>
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Leave Types</option>
                        @foreach($leaveTypes as $type)
                            <option value="type-{{ $type->Id }}" {{ $filter === 'type-'.$type->Id ? 'selected' : '' }}>
                                {{ $type->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a href="{{ route('hr.leave.balances.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <a class="btn btn-success ms-auto" href="{{ route('hr.leave.balances.export', request()->query()) }}">
                        Export
                    </a>
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

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Code</th>
                            <th>Leave Type</th>
                            <th class="text-end">Entitlement</th>
                            <th class="text-end">Accrued</th>
                            <th class="text-end">Taken</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balances as $balance)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <span class="avatar-title rounded-circle bg-primary text-white">
                                                {{ substr($balance->employee->FirstName ?? 'U', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $balance->employee->FirstName ?? '' }} {{ $balance->employee->LastName ?? '' }}</div>
                                            <small class="text-muted">{{ $balance->employee->EmployeeNo ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $balance->type->Code ?? '-' }}</td>
                                <td>{{ $balance->type->Name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($balance->Entitlement, 2) }}</td>
                                <td class="text-end">{{ number_format($balance->Accrued, 2) }}</td>
                                <td class="text-end">{{ number_format($balance->Taken, 2) }}</td>
                                <td class="text-end fw-bold {{ $balance->Balance < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($balance->Balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No leave balances found. Try adjusting filters or running accruals.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $balances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
