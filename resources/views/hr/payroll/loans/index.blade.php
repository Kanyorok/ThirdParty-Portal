@extends('layouts.app')

@section('title', 'Staff Loans')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Staff Loans</h2>
        <a class="btn btn-primary" href="{{ route('hr.payroll.loans.create') }}">New Loan</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Employee</th><th>Ref</th><th>Name</th><th>Principal</th><th>Monthly Repay</th><th>Tenure</th><th>Start</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->employee?->FirstName }} {{ $loan->employee?->LastName }}</td>
                            <td>{{ $loan->LoanRef ?? '-' }}</td>
                            <td>{{ $loan->Name }}</td>
                            <td>{{ number_format($loan->Principal, 2) }}</td>
                            <td>{{ number_format($loan->InstallmentAmount, 2) }}</td>
                            <td>{{ (int)$loan->TenureMonths }}</td>
                            <td>{{ $loan->StartDate ? $loan->StartDate->format('Y-m-d') : '-' }}</td>
                            <td>{{ $loan->Status }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.payroll.loans.show', $loan->Id) }}">View</a>
                                @if($loan->Status === 'Pending')
                                    <form method="POST" action="{{ route('hr.payroll.loans.approve', $loan->Id) }}" class="d-inline ms-1">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('hr.payroll.loans.reject', $loan->Id) }}" class="d-inline ms-1">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">Reject</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-3">No loans yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $loans->links() }}
    </div>
</div>
@endsection
