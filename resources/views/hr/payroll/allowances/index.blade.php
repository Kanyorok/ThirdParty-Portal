@extends('layouts.app')

@section('title', 'Monthly Allowances')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Monthly Allowances</h2>
        <a class="btn btn-primary" href="{{ route('hr.payroll.allowances.create') }}">New Allowance</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Employee</th><th>Allowance</th><th>Amount</th><th>Period</th><th>Taxable</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($allowances as $row)
                        <tr>
                            <td>{{ $row->employee?->FirstName }} {{ $row->employee?->LastName }}</td>
                            <td>{{ $row->allowance?->Name ?? $row->Name }}</td>
                            <td>{{ number_format($row->Amount, 2) }}</td>
                            <td>{{ $row->Month }}/{{ $row->Year }}</td>
                            <td>{{ $row->IsTaxable ? 'Yes' : 'No' }}</td>
                            <td>{{ $row->Status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No allowances yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $allowances->links() }}
    </div>
</div>
@endsection
