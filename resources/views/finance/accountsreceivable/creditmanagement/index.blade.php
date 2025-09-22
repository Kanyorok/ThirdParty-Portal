@extends('layouts.app')
@section('title','Credit Management')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header border bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-user-shield text-info me-2"></i> Credit Profiles
                </h6>
                <a href="{{ route('creditmanagement.create') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-plus me-1"></i> New Credit Profile
                </a>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                        <tr class="text-center">
                            <th>#</th>
                            <th>Customer</th>
                            <th>ID Number</th>
                            <th>Credit Limit</th>
                            <th>Status</th>
                            <th>Last Review</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="text-center">
                        @if($credits->count())
                            @foreach($credits as $credit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $credit->customer->TenantName ?? '-'}}</td>
                                    <td>{{ $credit->customer->IDRegistrationNo  ?? '-'}}</td>
                                    <td>{{ number_format($credit->CreditLimit,2) ?? '-' }}</td>
                                    <td>
                                        @if($credit->Status === 'Active')
                                            <span class="badge bg-success">{{ $credit->Status }}</span>
                                        @elseif($credit->Status === 'Inactive')
                                            <span class="badge bg-secondary">{{ $credit->Status }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $credit->Status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $credit->EffectiveFrom ??'-'}}</td>
                                    <td>
                                        <a href="{{ route('creditmanagement.show', $credit->Id) }}"
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('creditmanagement.edit', $credit->Id) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger custom-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                            {{-- data-name="{{$note->CDNumber}}" }}
                                            {{-- data-route="{{ route('creditnote.destroy', $note->Id) }}" --}}
                                        >
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle me-2"></i> No credit profiles found.
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination placeholder (static demo) --}}
                <nav class="mt-2">
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <li class="page-item disabled"><span class="page-link">«</span></li>
                        <li class="page-item active"><span class="page-link">1</span></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">»</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }

        body, .card, .table {
            font-family: var(--font-sans);
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color .2s;
        }

        .card {
            border: none;
            border-radius: .5rem;
        }

        .btn-sm {
            padding: .25rem .5rem;
        }
    </style>
@endsection
