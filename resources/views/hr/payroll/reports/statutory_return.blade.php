@extends('layouts.app')

@section('title', 'Statutory Return')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">{{ $deduction->Name }} Return</h2>
            <div class="text-muted">Code: {{ $deduction->Code }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.returns.index', $run->Id) }}">Back</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-4">
            <div>
                <div class="text-muted small">Cycle</div>
                <div class="h6 mb-0">{{ $run->cycle?->Month }}/{{ $run->cycle?->Year }}</div>
            </div>
            <div>
                <div class="text-muted small">Total</div>
                <div class="h6 mb-0">{{ number_format($total, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Employee No</th>
                        <th>KRA PIN</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->employee?->FirstName }} {{ $row->employee?->LastName }}</td>
                            <td>{{ $row->employee?->EmployeeNo }}</td>
                            <td>{{ $row->employee?->KRAPIN ?? '-' }}</td>
                            <td class="text-end">{{ number_format((float)$row->Amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No deductions captured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
