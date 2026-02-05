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

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover" id="loansTable">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Ref</th>
                        <th>Name</th>
                        <th>Principal</th>
                        <th>Monthly Repay</th>
                        <th>Tenure</th>
                        <th>Start</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loans as $loan)
                        <tr>
                            <td>{{ $loan->employee?->FirstName }} {{ $loan->employee?->LastName }}</td>
                            <td>{{ $loan->LoanRef ?? '-' }}</td>
                            <td>{{ $loan->Name }}</td>
                            <td>{{ number_format($loan->Principal, 2) }}</td>
                            <td>{{ number_format($loan->InstallmentAmount, 2) }}</td>
                            <td>{{ (int)$loan->TenureMonths }}</td>
                            <td>{{ $loan->StartDate ? $loan->StartDate->format('Y-m-d') : '-' }}</td>
                            <td>
                                @if($loan->Status === 'Approved')
                                    <span class="badge bg-success">{{ $loan->Status }}</span>
                                @elseif($loan->Status === 'Pending')
                                    <span class="badge bg-warning">{{ $loan->Status }}</span>
                                @elseif($loan->Status === 'Cancelled')
                                    <span class="badge bg-secondary">{{ $loan->Status }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $loan->Status }}</span>
                                @endif
                            </td>
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
                                @elseif($loan->Status === 'Approved' && $loan->Balance >= $loan->Principal)
                                    <form method="POST" action="{{ route('hr.payroll.loans.cancel', $loan->Id) }}" class="d-inline ms-1" onsubmit="return confirm('Cancel this loan? All pending repayment deductions will be removed.');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-warning">Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#loansTable').DataTable({
        pageLength: 25,
        order: [[6, 'desc']], // Sort by Start Date descending
        language: {
            search: "Search loans:",
            lengthMenu: "Show _MENU_ loans per page",
            info: "Showing _START_ to _END_ of _TOTAL_ loans",
            infoEmpty: "No loans available",
            infoFiltered: "(filtered from _MAX_ total loans)",
            zeroRecords: "No matching loans found"
        },
        columnDefs: [
            { orderable: false, targets: -1 } // Disable sorting on Actions column
        ]
    });
});
</script>
@endpush
@endsection
