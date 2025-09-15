@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="mb-0">Banks</h1>
        <div>
            <a href="{{ route('finance.bank.create') }}" class="btn btn-success">Add Bank</a>
            <a href="{{ route('finance.bankbranch.index') }}" class="btn btn-outline-primary">All Branches</a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Bank Name</th>
                    <th>Short</th>
                    <th>Bank Code</th>
                    <th>SWIFT</th>
                    <th>Clearing</th>
                    <th>CountryID</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Website</th>
                    <th>Status</th>
                    <th>CreatedOn</th>
                    <th>CreatedBy</th>
                    <th>ModifiedOn</th>
                    <th>ModifiedBy</th>
                    <th style="width: 260px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $bank)
                    <tr>
                        <td>{{ $bank->BankName }}</td>
                        <td>{{ $bank->ShortName ?? '—' }}</td>
                        <td>{{ $bank->BankCode ?? '—' }}</td>
                        <td>{{ $bank->SwiftCode ?? '—' }}</td>
                        <td>{{ $bank->ClearingCode ?? '—' }}</td>
                        <td>{{ $bank->CountryID ?? '—' }}</td>
                        <td>{{ $bank->EmailID ?? '—' }}</td>
                        <td>{{ $bank->Phone ?? '—' }}</td>
                        <td>
                            @if($bank->Website)
                                <a href="{{ $bank->Website }}" target="_blank" rel="noopener">{{ $bank->Website }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($bank->IsActive)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $bank->CreatedOn }}</td>
                        <td>{{ $bank->CreatedBy ?? '—' }}</td>
                        <td>{{ $bank->ModifiedOn ?? '—' }}</td>
                        <td>{{ $bank->ModifiedBy ?? '—' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('finance.bank.show', $bank->BankID) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('finance.bank.edit', $bank->BankID) }}" class="btn btn-sm btn-warning">Edit</a>
                            <a href="{{ route('finance.bankbranch.bybank', $bank->BankID) }}" class="btn btn-sm btn-secondary">Branches</a>
                            <form action="{{ route('finance.bank.destroy', $bank->BankID) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this bank? This action cannot be undone.');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="15" class="text-center text-muted py-4">No banks yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- If you change controller to paginate(), this will render pager --}}
    @if($banks instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $banks->links() }}
    @endif
</div>
@endsection
