<div>
    <!-- Simplicity is the essence of happiness. - Cedric Bledsoe -->
</div>
@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Leave Requests</h2>
        <a class="btn btn-primary" href="{{ route('hr.leave.requests.create') }}">+ New Leave Request</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form class="row g-3 mb-3" method="GET">
        <div class="col-md-3">
            <label class="form-label">Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->Id }}" @selected(request('employee_id') == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Any</option>
                @foreach(['Pending','Approved','Rejected','Cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status') == $st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td>{{ $req->employee->FirstName ?? '' }} {{ $req->employee->LastName ?? '' }}</td>
                                <td>{{ $req->type->Name ?? '' }}</td>
                                <td>{{ $req->StartDate }}</td>
                                <td>{{ $req->EndDate }}</td>
                                <td>{{ $req->TotalDays }}</td>
                                <td>{{ $req->Status }}</td>
                                <td class="text-end">
                                    @if($req->Status === 'Pending')
                                        <form method="POST" action="{{ route('hr.leave.requests.approve', $req->Id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('hr.leave.requests.reject', $req->Id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                                        </form>
                                    @endif
                                    @if(!in_array($req->Status, ['Cancelled']))
                                        <form method="POST" action="{{ route('hr.leave.requests.cancel', $req->Id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No leave requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $requests->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
