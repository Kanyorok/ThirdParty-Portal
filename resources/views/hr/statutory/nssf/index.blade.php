@extends('layouts.app')

@section('title', 'NSSF Rates')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">NSSF Rates</h2>
        <a class="btn btn-primary" href="{{ route('hr.statutory.nssf.create') }}">+ New Rate</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Tier</th>
                            <th>Income From</th>
                            <th>Income To</th>
                            <th>Employee</th>
                            <th>Employer</th>
                            <th>Rate Type</th>
                            <th>Effective From</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>{{ $rate->Tier }}</td>
                                <td>{{ number_format($rate->IncomeFrom,2) }}</td>
                                <td>{{ $rate->IncomeTo ? number_format($rate->IncomeTo,2) : 'No cap' }}</td>
                                <td>{{ $rate->EmployeeRate }}</td>
                                <td>{{ $rate->EmployerRate }}</td>
                                <td>{{ $rate->IsPercentage ? 'Percent' : 'Amount' }}</td>
                                <td>{{ $rate->EffectiveFrom }}</td>
                                <td>{{ $rate->IsActive ? 'Active' : 'Inactive' }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('hr.statutory.nssf.edit', $rate->Id) }}">Edit</a>
                                    <form class="d-inline" action="{{ route('hr.statutory.nssf.destroy', $rate->Id) }}" method="POST" onsubmit="return confirm('Deactivate this rate?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">No rates found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $rates->links() }}
        </div>
    </div>
</div>
@endsection
