@extends('layouts.app')
@section('title', 'Activities Overview')
@section('content')
    <div class="container mt-2">
        <div class="card shadow-sm rounded-3">
            <div class="card-body p-3">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <p class="text-muted mb-3" style="font-size: 14px;">
                    Overview of budget activities linked to their lines, with periods and allocations.
                </p>

                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-tasks me-2"></i> Budget Activities
                    </h6>
                    <a href="{{ route('budgetactivities.create') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-plus me-1"></i> New Activity
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle text-center activities-table">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Budget</th>
                            <th>Frequency</th>
                            <th>Activities</th>
                            <th>Total Allocation</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $i = 1; @endphp
                        @forelse ($groupedActivities as $budgetId => $activities)
                            @php $budget = $activities->first()->budget; @endphp
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $budget->Name }}</td>
                                <td>{{ $budget->From }} - {{ $budget->To }}</td>
                                <td>
                                    <a href="{{ route('budgetactivities.show', $budget->Id) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> View Activities
                                    </a>
                                </td>
                                <td>{{ number_format($activities->sum('FullAllocation'), 2) }}</td>
                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $budget->Name }}"
                                            data-route="{{ route('budgetactivities.destroy', $budgetId) }}">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-2 text-muted">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No budget activities found.
                                        </p>
                                        <a href="{{ route('budgetactivities.create') }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-plus-circle me-1"></i> Add Activity
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

    @include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        .activities-table {
            font-size: 13px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .activities-table th,
        .activities-table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
@endsection
