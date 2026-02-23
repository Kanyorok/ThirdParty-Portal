@extends('layouts.app')

@section('title', 'Terminal Dues')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Terminal Dues</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Exit No</th>
                            <th>Employee</th>
                            <th>Status</th>
                            <th>Effective Date</th>
                            <th>Final Payroll</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exits as $exit)
                            <tr>
                                <td>{{ $exit->ExitNo }}</td>
                                <td>{{ $exit->employee?->FirstName }} {{ $exit->employee?->LastName }}</td>
                                <td>{{ $exit->Status }}</td>
                                <td>{{ $exit->EffectiveExitDate?->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $exit->FinalPayrollStatus ?? '-' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.exit.terminal-dues.edit', $exit->Id) }}">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No exit records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $exits->links() }}
        </div>
    </div>
</div>
@endsection
