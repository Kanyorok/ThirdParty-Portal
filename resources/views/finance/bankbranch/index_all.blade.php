@extends('layouts.app')

@section('content')
<div class="container my-3">
    <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary"><i class="fas fa-code-branch me-2"></i> All Branches</h5>
            <div>
                <a href="{{ route('finance.bankbranch.create') }}" class="btn btn-info btn-sm p-2"><i class="fas fa-plus me-1"></i> New Branch</a>
                <a href="{{ route('finance.bank.index') }}" class="btn btn-outline-secondary btn-sm p-2">Banks</a>
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
                    <th>Bank</th>
                    <th>Branch Name</th>
                    <th>Branch Code</th>
                    <th>Address1</th>
                    <th>Address2</th>
                    <th>CityID</th>
                    <th>CountryID</th>
                    <th>ZipCode</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                    <tr>
                        <td>{{ optional($branch->bank)->BankName ?? '—' }}</td>
                        <td>{{ $branch->BranchName }}</td>
                        <td>{{ $branch->BranchCode ?? '—' }}</td>
                        <td>{{ $branch->Address1 ?? '—' }}</td>
                        <td>{{ $branch->Address2 ?? '—' }}</td>
                        <td>{{ $branch->CityID ?? '—' }}</td>
                        <td>{{ $branch->CountryID ?? '—' }}</td>
                        <td>{{ $branch->ZipCode ?? '—' }}</td>
                        <td>{{ $branch->Phone ?? '—' }}</td>
                        <td>{{ $branch->EmailID ?? '—' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('finance.bankbranch.show', $branch->BranchID) }}" class="btn btn-sm btn-outline-info me-1" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('finance.bankbranch.edit', $branch->BranchID) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit"><i class="fas fa-edit"></i></a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger custom-delete-btn"
                                    title="Delete"
                                    data-bs-toggle="modal"
                                    data-bs-target="#customDeleteConfirmModal"
                                    data-name="{{ $branch->BranchName }}"
                                    data-route="{{ route('finance.bankbranch.destroy', $branch->BranchID) }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center text-muted py-4">No branches yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        </div>
        @if($branches instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3">
                <small class="text-muted mb-0">
                    Showing {{ $branches->firstItem() ?? 0 }} to {{ $branches->lastItem() ?? 0 }} of {{ $branches->total() }} branches
                </small>
                <div class="mb-0">
                    {{ $branches->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

    @include('components.modals.delete-confirm')
</div>
@endsection
