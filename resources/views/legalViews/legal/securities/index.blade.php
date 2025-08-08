@extends('layouts.app')
@section('title', 'Loan Security Registry')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">🔐 Loan Security / Collateral Registry</h4>
        <a href="{{ route('legal.securities.create') }}" class="btn btn-primary">➕ Register Security</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Security Type</th>
                <th>Owner Name</th>
                <th>Loan A/C</th>
                <th>Value</th>
                <th>Status</th>
                <th>Institution</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($securities as $sec)
                <tr>
                    <td>{{ $sec->SecurityType }}</td>
                    <td>{{ $sec->OwnerName }}</td>
                    <td>{{ $sec->LoanAccountNumber }}</td>
                    <td>{{ number_format($sec->Value, 2) }}</td>
                    <td>{{ $sec->SecurityStatus }}</td>
                    <td>{{ $sec->Institution }}</td>
                    <td>
                        <a href="{{ route('legal.securities.edit', $sec->ID) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-muted">No securities registered.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
