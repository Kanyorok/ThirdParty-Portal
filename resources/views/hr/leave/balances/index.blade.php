<div>
    <!-- You must be the change you wish to see in the world. - Mahatma Gandhi -->
</div>
@extends('layouts.app')

@section('title', 'Leave Balances')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Leave Balances</h2>
        <form method="POST" action="{{ route('hr.leave.balances.accrue') }}">
            @csrf
            <button class="btn btn-outline-primary" type="submit">Run Monthly Accrual</button>
        </form>
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
                            <th>Leave Type</th>
                            <th>Entitlement</th>
                            <th>Accrued</th>
                            <th>Taken</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balances as $bal)
                            <tr>
                                <td>{{ $bal->employee->FirstName ?? '' }} {{ $bal->employee->LastName ?? '' }}</td>
                                <td>{{ $bal->type->Name ?? '' }}</td>
                                <td>{{ $bal->Entitlement }}</td>
                                <td>{{ $bal->Accrued }}</td>
                                <td>{{ $bal->Taken }}</td>
                                <td>{{ $bal->Balance }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No balances.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $balances->links() }}
        </div>
    </div>
</div>
@endsection
