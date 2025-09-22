@extends('layouts.app')
@section('title', 'Chart of Accounts')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">📚 General Ledgers</h5>
                <a href="{{ route('chartofaccounts.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> Add New Account
                </a>
            </div>

            <div class="card-body p-3">
                <p class="text-muted">
                    The Chart of Accounts organizes all GL accounts for assets, liabilities, income, expenses, and
                    equity to support accurate reporting.
                </p>

                @if($charts->count())
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle table-striped table-striped1"
                               style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                            <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">GL Name</th>
                                <th scope="col">Account Code</th>
                                <th scope="col">GL Type</th>
                                <th scope="col">GL Type Group</th>
                                <th scope="col">GL Sub-Type</th>
                                <th scope="col">Description</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($charts as $item)
                                <tr>
                                    <td>{{ $charts->firstItem() + $loop->index }}</td>
                                    <td>{{ $item->GLName ?? '-' }}</td>
                                    <td>{{ $item->GLCode ?? '-' }}</td>
                                    <td>{{ $item->GLAccountTypeID ?? '-' }}</td>
                                    <td>{{ $item->typeGroup->Description ?? '-' }}</td>
                                    <td>{{ $item->subAccount->Description ?? '-' }}</td>
                                    <td>{{ $item->Description ?? '-' }}</td>
                                    <td>
                                        @php
                                            $isActive = (bool) $item->IsActive;
                                            $badge = $isActive ? 'bg-success' : 'bg-danger';
                                            $label = $isActive ? 'Active' : 'Inactive';
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $label }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('chartofaccounts.edit', $item->Id) }}"
                                           class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                title="Delete"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{ $item->GLName }}"
                                                data-route="{{ route('chartofaccounts.destroy', $item->Id) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center p-4 border rounded-3 bg-light">
                        <p class="mb-3 text-muted fs-5">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            <i>No GL accounts found.</i>
                        </p>
                        <a href="{{ route('chartofaccounts.create') }}" class="btn btn-info px-4 py-2">
                            <i class="fas fa-plus-circle me-2"></i> Add Account
                        </a>
                    </div>
                @endif
            </div>

            @if($charts->hasPages())
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2 px-3">
                    <small class="text-muted mb-0">
                        Showing {{ $charts->firstItem() ?? 0 }} to {{ $charts->lastItem() ?? 0 }}
                        of {{ $charts->total() }} accounts
                    </small>
                    <div class="mb-0">
                        {{ $charts->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
        }

        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
@endsection
