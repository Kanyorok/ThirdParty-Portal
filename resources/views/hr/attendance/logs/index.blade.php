@extends('layouts.app')

@section('title', 'Attendance Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Raw Attendance Logs</h2>
    </div>

    <form class="row g-3 mb-3" method="GET">
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Employee</label>
            <select name="employee_id" class="form-select">
                <option value="">All</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->Id }}" @selected(request('employee_id') == $emp->Id)>{{ $emp->FirstName }} {{ $emp->LastName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Channel</label>
            <input type="text" name="channel" class="form-control" placeholder="Android/Bio/Web" value="{{ request('channel') }}">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit">Filter</button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Log Time</th>
                            <th>Type</th>
                            <th>Channel</th>
                            <th>Device</th>
                            <th>GPS</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->employee->FirstName ?? '' }} {{ $log->employee->LastName ?? '' }}</td>
                                <td>{{ $log->LogTime }}</td>
                                <td>{{ $log->LogType }}</td>
                                <td>{{ $log->Channel }}</td>
                                <td>{{ $log->DeviceID ?? '-' }}</td>
                                <td>{{ $log->Latitude ? $log->Latitude.','.$log->Longitude : '-' }}</td>
                                <td>{{ $log->Remarks ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
