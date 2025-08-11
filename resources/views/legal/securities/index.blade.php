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
            <tr>
        <td>Title Deed</td>
        <td>John Mwangi</td>
        <td>LN001245</td>
        <td>5,000,000</td>
        <td>Active</td>
        <td>Equity Bank</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Motor Vehicle Logbook</td>
        <td>Mary Wanjiku</td>
        <td>LN001378</td>
        <td>1,200,000</td>
        <td>Released</td>
        <td>Co-operative Bank</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Shares Certificate</td>
        <td>Ali Hassan</td>
        <td>LN001459</td>
        <td>800,000</td>
        <td>Active</td>
        <td>NCBA Bank</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Fixed Deposit</td>
        <td>Lucy Kariuki</td>
        <td>LN001567</td>
        <td>2,500,000</td>
        <td>Pending</td>
        <td>KCB Bank</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Insurance Policy</td>
        <td>David Otieno</td>
        <td>LN001678</td>
        <td>1,000,000</td>
        <td>Active</td>
        <td>Stanbic Bank</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
            {{-- @forelse ($securities as $sec)
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
            @endforelse --}}
        </tbody>
    </table>
</div>
@endsection
