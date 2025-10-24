@extends('layouts.app')
@section('title', 'Bank Accounts')
@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary"><i class="fas fa-list"></i></h5>
                <div>
                    <a href="{{ route('finance.bankaccountsetup.create') }}" class="btn btn-info btn-sm p-2">
                        <i class="fas fa-plus me-1"></i> New Account
                    </a>
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
                            <th>#</th>
                            <th>Bank</th>
                            <th>Branch</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            {{-- <th>IBAN</th> --}}
                            {{-- <th>Currency</th> --}}
                            <th>GL Account</th>
                            <th>Opening</th>
                            <th>Current</th>
                            {{-- <th>Default</th> --}}
                            <th>Status</th>
                            <th class="text-nowrap" style="width:230px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($accounts as $acc)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{ optional($acc->bank)->BankName ?? '—' }}</td>
                                <td>{{ optional($acc->branch)->BranchName ?? '—' }}</td>
                                <td>{{ $acc->AccountNumber }}</td>
                                <td>{{ $acc->AccountName ?? '—' }}</td>
                                {{-- <td>{{ $acc->IBAN ?? '—' }}</td> --}}
                                {{-- <td>{{ optional($acc->currency)->Code ?? '—' }}</td> --}}
                                <td>{{ $acc->glAccount?->GLCode ?? '—' }}</td>
                                <td>{{ number_format($acc->OpeningBalance,2) }}</td>
                                <td>{{ number_format($acc->CurrentBalance,2) }}</td>
                                {{-- <td>{!! $acc->IsDefault ? '<span class="badge bg-info">Yes</span>' : '—' !!}</td> --}}
                                <td>{!! $acc->IsActive ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('finance.bankaccountsetup.show', $acc->AccountID) }}"
                                       class="btn btn-sm btn-outline-info me-1" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('finance.bankaccountsetup.edit', $acc->AccountID) }}"
                                       class="btn btn-sm btn-outline-primary me-1" title="Edit"><i
                                            class="fas fa-edit"></i></a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $acc->AccountName ?? ('Account #' . $acc->AccountID) }}"
                                            data-route="{{ route('finance.bankaccountsetup.destroy', $acc->AccountID) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">No bank accounts yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($accounts instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3">
                    <small class="text-muted mb-0">
                        Showing {{ $accounts->firstItem() ?? 0 }} to {{ $accounts->lastItem() ?? 0 }}
                        of {{ $accounts->total() }} accounts
                    </small>
                    <div class="mb-0">
                        {{ $accounts->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>

        @include('components.modals.delete-confirm')
    </div>
@endsection
