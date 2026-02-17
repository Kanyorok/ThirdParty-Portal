@extends('layouts.app')

@section('title', 'KPI Goals')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">KPI Goals</h2>
        <a class="btn btn-primary" href="{{ route('hr.kpi.goals.create') }}">+ New Goal Set</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->Id }}" @selected(request('employee_id') == $emp->Id)>
                                {{ $emp->FirstName }} {{ $emp->LastName }} ({{ $emp->EmployeeNo }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period_id" class="form-select">
                        <option value="">All</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->Id }}" @selected(request('period_id') == $period->Id)>{{ $period->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['Draft','Submitted','Approved','Returned','Rejected'] as $st)
                            <option value="{{ $st }}" @selected(request('status') == $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th class="text-end">Total Weight</th>
                        <th>Submitted</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goals as $goal)
                        <tr>
                            <td>{{ $goal->employee?->FirstName }} {{ $goal->employee?->LastName }}</td>
                            <td>{{ $goal->period?->Name }}</td>
                            <td>{{ $goal->Status }}</td>
                            <td class="text-end">{{ number_format((float)$goal->TotalWeight, 2) }}</td>
                            <td>{{ $goal->SubmittedOn ? $goal->SubmittedOn->format('Y-m-d') : '-' }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.kpi.goals.show', $goal->Id) }}">View</a>
                                @if(in_array($goal->Status, ['Draft','Returned','Rejected'], true))
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr.kpi.goals.edit', $goal->Id) }}">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No KPI goals found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $goals->links() }}
    </div>
</div>
@endsection
