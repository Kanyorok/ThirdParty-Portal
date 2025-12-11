@extends('layouts.app')

@section('title', 'Overtime Requests')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Overtime Requests</h2>
        <a class="btn btn-primary" href="{{ route('hr.attendance.overtime.create') }}">+ New Overtime Request</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td>{{ $req->employee->FirstName ?? '' }} {{ $req->employee->LastName ?? '' }}</td>
                                <td>{{ $req->WorkDate }}</td>
                                <td>{{ $req->HoursRequested }}</td>
                                <td>{{ $req->Status }}</td>
                                <td class="text-end">
                                    @if($req->Status === 'Pending')
                                        <form method="POST" action="{{ route('hr.attendance.overtime.approve', $req->Id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" type="submit">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('hr.attendance.overtime.reject', $req->Id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No overtime requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
