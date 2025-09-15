@extends('layouts.app')
@section('title', 'Budget Line & GL Mapping')

@section('content')
    <div class="container my-3">
        <!-- Card for Budget Lines -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="fas fa-file-invoice"></i></h5>
                <a href="{{ route('budgetlinemapping.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> New Budget Line
                </a>
            </div>

            <div class="card-body p-3">
                <!-- Budget Lines Table -->
                <div class="table-responsive">
                    <p class="text-muted small mb-3">
                        Below is a list of all existing budget lines with their departments, mapped GL accounts, and whether they are product-driven.
                    </p>
                    <table class="table table-hover table-sm align-middle table-striped1"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size:13px">
                        <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Line Name</th>
                            <th scope="col">Department</th>
                            <th scope="col">GL Type</th>
                            <th scope="col">GL Sub-Type</th>
                            <th scope="col">Product Driven</th>
                            <th scope="col">Description</th>
                            <th scope="col">CBS GLs</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($budgetLines as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-truncate" style="max-width: 180px;" title="{{ $item->LineName }}">
                                    {{ $item->LineName }}
                                </td>
                                <td>{{ $item->department->Name ?? '-' }}</td>
                                <td>{{ $item->glAccountType->Description ?? '-' }}</td>
                                <td>{{ $item->glSubType->Description ?? '-' }}</td>
                                <td>
                                    @if($item->IsProductDriven)
                                        <a href="{{ route('budgetlinemapping.show', $item->Id) }}"
                                           class="badge bg-success text-decoration-none">Yes</a>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td class="text-truncate" style="max-width: 200px;" title="{{ $item->Description }}">
                                    {{ $item->Description }}
                                </td>
                                <td class="small text-start">
                                    @forelse($item->newGlAccounts as $gl)
                                        <div>GL{{ $gl->Id }} – {{ $gl->Description }}</div>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('budgetlinemapping.edit', $item->Id) }}"
                                       class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $item->LineName }}"
                                            data-route="{{ route('budgetlinemapping.destroy', $item->Id) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No budget lines have been added yet.
                                        </p>
                                        <a href="{{ route('budgetlinemapping.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Budget Line
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


{{-- Reusable Delete Confirm Modal --}}
@include('components.modals.delete-confirm')

@section('styles')
    <style>
        /* Custom hover effect */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Compact button styling */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Compact table cells */
        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Responsive adjustments */
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
