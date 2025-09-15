@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mb-0">Branches – {{ $bank->BankName }}</h1>
            <div class="text-muted">
                Code: {{ $bank->BankCode ?? '—' }} · SWIFT: {{ $bank->SwiftCode ?? '—' }} · CountryID: {{ $bank->CountryID ?? '—' }}
            </div>
        </div>
        <div>
            <a href="{{ route('finance.bankbranch.create', ['bankId' => $bank->BankID]) }}" class="btn btn-success">Add Branch</a>
            <a href="{{ route('finance.bank.index') }}" class="btn btn-outline-secondary">Back to Banks</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Branch Name</th>
                    <th>Branch Code</th>
                    <th>Address1</th>
                    <th>Address2</th>
                    <th>CityID</th>
                    <th>CountryID</th>
                    <th>ZipCode</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                    <tr>
                        <td>{{ $branch->BranchName }}</td>
                        <td>{{ $branch->BranchCode ?? '—' }}</td>
                        <td>{{ $branch->Address1 ?? '—' }}</td>
                        <td>{{ $branch->Address2 ?? '—' }}</td>
                        <td>{{ $branch->CityID ?? '—' }}</td>
                        <td>{{ $branch->CountryID ?? '—' }}</td>
                        <td>{{ $branch->ZipCode ?? '—' }}</td>
                        <td>{{ $branch->Phone ?? '—' }}</td>
                        <td>{{ $branch->EmailID ?? '—' }}</td>
                        <td>
                            @if($branch->IsActive)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('finance.bankbranch.show', $branch->BranchID) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('finance.bankbranch.edit', $branch->BranchID) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('finance.bankbranch.destroy', $branch->BranchID) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this branch?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">No branches yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add pagination if you switch controller to paginate() --}}
    @if($branches instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $branches->links() }}
    @endif
</div>
@endsection
