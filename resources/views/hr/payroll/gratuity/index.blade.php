@extends('layouts.app')

@section('title', 'Gratuity')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Gratuity</h2>
        <a class="btn btn-primary" href="{{ route('hr.payroll.gratuity.create') }}">Compute Gratuity</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control" value="{{ request('year') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Accrued" @selected(request('status') === 'Accrued')>Accrued</option>
                        <option value="Pending" @selected(request('status') === 'Pending')>Pending</option>
                        <option value="Paid" @selected(request('status') === 'Paid')>Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Year</th>
                        <th class="text-end">Rate %</th>
                        <th class="text-end">Gross Pay</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->employee?->FirstName }} {{ $row->employee?->LastName }}</td>
                            <td>{{ $row->Year }}</td>
                            <td class="text-end">{{ number_format((float)$row->RatePercent, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$row->GrossPay, 2) }}</td>
                            <td class="text-end">{{ number_format((float)$row->Amount, 2) }}</td>
                            <td>{{ $row->Status }}</td>
                            <td class="text-end">
                                @if($row->Status !== 'Paid')
                                    <form method="POST" action="{{ route('hr.payroll.gratuity.pay', $row->Id) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success" type="submit">Mark Paid</button>
                                    </form>
                                @else
                                    <span class="text-muted">Paid</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No gratuity records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $rows->links() }}
    </div>
</div>
@endsection
