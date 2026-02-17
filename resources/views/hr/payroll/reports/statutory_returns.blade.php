@extends('layouts.app')

@section('title', 'Statutory Returns')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Statutory Returns</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.runs.show', $run->Id) }}">Back</a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap gap-4">
            <div>
                <div class="text-muted small">Cycle</div>
                <div class="h6 mb-0">{{ $run->cycle?->Month }}/{{ $run->cycle?->Year }}</div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Deduction</th>
                        <th>Code</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deductions as $deduction)
                        <tr>
                            <td>{{ $deduction->Name }}</td>
                            <td>{{ $deduction->Code }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.payroll.returns.show', [$run->Id, $deduction->Code]) }}">View Return</a>
                                <a class="btn btn-sm btn-outline-secondary ms-1" href="{{ route('hr.payroll.returns.export', [$run->Id, $deduction->Code]) }}">Download Excel</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No deductions configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
