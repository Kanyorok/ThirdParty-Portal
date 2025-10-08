@extends('layouts.app')
@section('title', 'Budget Activity Master')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <!-- Header -->
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-tasks me-2"></i>
                </h5>
                <a href="{{ route('activitymaster.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> Add New Activity
                </a>
            </div>

            <!-- Body -->
            <div class="card-body p-3">
                <p class="text-muted small mb-3">
                    Manage your budget activities here. Each activity is linked to a budget line and can be marked as active or inactive.
                </p>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped text-center"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size:13px">
                        <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Activity Name</th>
                            <th scope="col">Budget Line</th>
                            <th scope="col">Description</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($activities as $index => $activity)
                            <tr>
                                <td>{{ $activities->firstItem() + $index }}</td>
                                <td>{{ $activity->ActivityName ?? '-' }}</td>
                                <td>{{ $activity->budgetLine->LineName ?? '-' }}</td>
                                <td style="white-space: normal; word-break: break-word; max-width: 300px;">
                                    {{ $activity->Description ?? '-' }}
                                </td>
                                <td>
                                    @if($activity->IsActive)
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-ban me-1"></i> Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('activitymaster.edit', $activity->Id) }}"
                                       class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $activity->ActivityName }}"
                                            data-route="{{ route('activitymaster.destroy', $activity->Id) }}"
                                            title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No budget activities found.
                                        </p>
                                        <a href="{{ route('activitymaster.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Activity
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $activities->links() }}
                </div>
            </div>
        </div>
    </div>

    @include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        /* Hover effect */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Compact buttons */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Compact table cells */
        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        /* Card look */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Empty state responsive */
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
