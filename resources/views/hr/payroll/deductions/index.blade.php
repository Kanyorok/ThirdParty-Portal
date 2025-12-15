@extends('layouts.app')

@section('title', 'Monthly Deductions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Monthly Deductions</h2>
        <a class="btn btn-primary" href="{{ route('hr.payroll.deductions.create') }}">New Deduction</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Employee</th><th>Name</th><th>Amount</th><th>Period</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($deductions as $row)
                        <tr>
                            <td>{{ $row->employee?->FirstName }} {{ $row->employee?->LastName }}</td>
                            <td>{{ $row->Name }}</td>
                            <td>{{ number_format($row->Amount, 2) }}</td>
                            <td>{{ $row->Month }}/{{ $row->Year }}</td>
                            <td>{{ $row->Status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No deductions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $deductions->links() }}
    </div>
</div>
@endsection
