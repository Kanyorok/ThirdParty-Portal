@extends('layouts.app')

@section('title', 'Exit Requests')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Exit Requests</h2>
        <a class="btn btn-primary" href="{{ route('hr.exit.requests.create') }}">+ New Exit</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <select name="employee" class="form-select">
                        <option value="">All</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->Id }}" @selected(request('employee') == $employee->Id)>
                                {{ $employee->FirstName }} {{ $employee->LastName }} ({{ $employee->EmployeeNo }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Exit Type</label>
                    <select name="exitType" class="form-select">
                        <option value="">All</option>
                        @foreach($exitTypes as $exitType)
                            <option value="{{ $exitType->Id }}" @selected(request('exitType') == $exitType->Id)>
                                {{ $exitType->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach(['Draft','Submitted','Approved','Rejected','Closed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Exit No</th>
                            <th>Employee</th>
                            <th>Exit Type</th>
                            <th>Initiator</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Effective Date</th>
                            <th>Requested On</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $exit)
                            <tr>
                                <td>{{ $exit->ExitNo }}</td>
                                <td>{{ $exit->employee?->FirstName }} {{ $exit->employee?->LastName }}</td>
                                <td>{{ $exit->exitType?->Name ?? '-' }}</td>
                                <td>{{ $exit->InitiatorType ?? '-' }}</td>
                                <td>{{ $exit->Status }}</td>
                                <td>{{ $exit->ApprovalStatus ?? '-' }}</td>
                                <td>{{ $exit->EffectiveExitDate?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $exit->RequestedOn?->format('Y-m-d') ?? '-' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.exit.requests.show', $exit->Id) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">No exit requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
