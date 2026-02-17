@extends('layouts.app')

@section('title', 'Attendance Exceptions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Attendance Exceptions</h2>
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
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Resolution</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exceptions as $ex)
                            <tr>
                                <td>{{ $ex->WorkDate }}</td>
                                <td>{{ $ex->EmployeeID }}</td>
                                <td>{{ $ex->Type }}</td>
                                <td>{{ $ex->Status }}</td>
                                <td>{{ $ex->Resolution ?? '-' }}</td>
                                <td class="text-end">
                                    @if($ex->Status !== 'Resolved')
                                        <form method="POST" action="{{ route('hr.attendance.exceptions.resolve', $ex->Id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="Resolution" value="Resolved manually">
                                            <button class="btn btn-sm btn-success" type="submit">Resolve</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No exceptions.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $exceptions->links() }}
        </div>
    </div>
</div>
@endsection
