@extends('layouts.app')

@section('title', 'Statutory Return')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">{{ $deduction->Name }} Return</h2>
            <div class="text-muted">Code: {{ $deduction->Code }}</div>
        </div>
        <div class="d-flex gap-2 d-print-none">
            <a class="btn btn-outline-primary" href="{{ route('hr.payroll.returns.export', [$run->Id, $deduction->Code]) }}">Download Excel</a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">Print</button>
            <a class="btn btn-outline-secondary" href="{{ route('hr.payroll.returns.index', $run->Id) }}">Back</a>
        </div>
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
            @if(!empty($returnPayload))
                <table class="table mb-0">
                    <thead>
                        <tr>
                            @foreach($returnPayload['columns'] as $column)
                                <th class="{{ ($column['align'] ?? '') === 'end' ? 'text-end' : '' }}">{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnPayload['rows'] as $row)
                            <tr>
                                @foreach($returnPayload['columns'] as $column)
                                    @php($value = $row[$column['key']] ?? '')
                                    <td class="{{ ($column['align'] ?? '') === 'end' ? 'text-end' : '' }}">
                                        @if(is_numeric($value) && ($column['align'] ?? '') === 'end')
                                            {{ number_format((float)$value, 2) }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($returnPayload['columns']) }}" class="text-center text-muted py-3">No deductions captured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
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
            @endif
        </div>
    </div>
</div>
@endsection
