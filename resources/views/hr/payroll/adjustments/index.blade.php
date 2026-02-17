@extends('layouts.app')

@section('title', 'Salary Adjustments')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Salary Adjustments</h2>
        <a class="btn btn-primary" href="{{ route('hr.payroll.adjustments.create') }}">New Adjustment</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Employee</th><th>Type</th><th>Amount</th><th>Effective</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($adjustments as $adj)
                        <tr>
                            <td>{{ $adj->employee?->FirstName }} {{ $adj->employee?->LastName }}</td>
                            <td>{{ $adj->Type }}</td>
                            <td>{{ number_format($adj->Amount, 2) }}</td>
                            <td>{{ $adj->EffectiveDate }}</td>
                            <td>{{ $adj->Status }}</td>
                            <td class="text-end">
                                @if($adj->Status === 'Pending')
                                    <form action="{{ route('hr.payroll.adjustments.approve', $adj->Id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form action="{{ route('hr.payroll.adjustments.reject', $adj->Id) }}" method="POST" class="d-inline ms-1">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No adjustments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $adjustments->links() }}
    </div>
</div>
@endsection
