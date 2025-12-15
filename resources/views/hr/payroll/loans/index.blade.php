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
                <thead><tr><th>Employee</th><th>Name</th><th>Principal</th><th>Balance</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->employee?->FirstName }} {{ $loan->employee?->LastName }}</td>
                            <td>{{ $loan->Name }}</td>
                            <td>{{ number_format($loan->Principal, 2) }}</td>
                            <td>{{ number_format($loan->Balance, 2) }}</td>
                            <td>{{ $loan->Status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No loans yet.</td></tr>
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
