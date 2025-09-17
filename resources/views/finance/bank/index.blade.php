@extends('layouts.app')
@section('title', 'Banks')

@section('content')
<div class="container my-3">
    <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">🏦 List</h5>
            <div>
                <a href="{{ route('finance.bank.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> New Bank
                </a>
                <a href="{{ route('finance.bankbranch.index') }}" class="btn btn-outline-primary btn-sm p-2">All Branches</a>
            </div>
        </div>
        <div class="card-body p-3">
        @if(session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
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
                    <th>View Branches</th>
                    <th>Website</th>
                    {{-- <th>Status</th> --}}
                    {{-- <th>CreatedOn</th>
                    <th>CreatedBy</th>
                    <th>ModifiedOn</th>
                    <th>ModifiedBy</th> --}}
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
                            <a href="{{ route('finance.bankbranch.bybank', $bank->BankID) }}" class="btn btn-sm btn-outline-secondary me-1">Branches</a>
                        </td>
                        <td>
                            @if($bank->Website)
                                <a href="{{ $bank->Website }}" target="_blank" rel="noopener">{{ $bank->Website }}</a>
                            @else
                                —
                            @endif
                        </td>
                        {{-- <td>
                            @if($bank->IsActive)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td> --}}
                        {{-- <td>{{ $bank->CreatedOn }}</td>
                        <td>{{ $bank->CreatedBy ?? '—' }}</td>
                        <td>{{ $bank->ModifiedOn ?? '—' }}</td>
                        <td>{{ $bank->ModifiedBy ?? '—' }}</td> --}}
                        <td class="text-nowrap">
                            <a href="{{ route('finance.bank.show', $bank->BankID) }}"
                                class="btn btn-sm btn-outline-info me-1"
                                title="View More {{ $bank->BankName }}  Details">
                                 <i class="fas fa-eye"></i>
                             </a>
                             
                             <a href="{{ route('finance.bank.edit', $bank->BankID) }}"
                                class="btn btn-sm btn-outline-primary me-1"
                                title="Edit  {{ $bank->BankName }}">
                                 <i class="fas fa-edit"></i>
                             </a>
                             
                             <button type="button"
                                     class="btn btn-sm btn-outline-danger custom-delete-btn"
                                     title="Delete {{ $bank->BankName }}"
                                     data-bs-toggle="modal"
                                     data-bs-target="#customDeleteConfirmModal"
                                     data-name="{{ $bank->BankName }}"
                                     data-route="{{ route('finance.bank.destroy', $bank->BankID) }}">
                                 <i class="fas fa-trash-alt"></i>
                             </button>
                             
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="15" class="text-center text-muted py-4">No banks yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        </div>
        @if($banks instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3">
                <small class="text-muted mb-0">
                    Showing {{ $banks->firstItem() ?? 0 }} to {{ $banks->lastItem() ?? 0 }} of {{ $banks->total() }} banks
                </small>
                <div class="mb-0">
                    {{ $banks->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

    @include('components.modals.delete-confirm')
</div>
@endsection
