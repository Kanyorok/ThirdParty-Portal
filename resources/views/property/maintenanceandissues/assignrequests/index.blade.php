@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('title', 'Maintenance Assignments')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    /* Card styling */
    .card {
        border-radius: 0.75rem;
    }

    /* Horizontal scroll wrapper */
    .table-responsive {
        overflow-x: auto;
        white-space: nowrap;
    }

    /* Table alignment */
    table th,
    table td {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    /* Action buttons styling */
    .action-buttons {
        display: flex;
        gap: 0.35rem;
        justify-content: center;
        align-items: center;
    }

    .action-buttons .btn {
        padding: 0.25rem 0.45rem;
    }

    /* Badge sizing */
    .badge {
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <small class="text-muted">
                This table displays all assigned maintenance tasks and their details.
            </small>
        </div>

        <a href="{{ route('assignrequest.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Assign Task
        </a>
    </div>

    @if($assignments->count())
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="AssignmentsTable" class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Request No.</th>
                            <th>Assigned</th>
                            <th>Start</th>
                            <th>Completion</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">{{ $assignment->request->RequestNumber ?? '-' }}</td>
                                <td>{{ $assignment->assignmentType->Description ?? '-' }}</td>
                                <td class="text-center">
                                    {{ $assignment->ExpectedStartDate
                                        ? Carbon::parse($assignment->ExpectedStartDate)->format('d M Y')
                                        : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $assignment->ExpectedCompletion
                                        ? Carbon::parse($assignment->ExpectedCompletion)->format('d M Y')
                                        : '-' }}
                                </td>
                                <td class="text-center">
                                    @if ($assignment->priorityLevel)
                                        @php $priority = $assignment->priorityLevel->Value; @endphp
                                        @switch($priority)
                                            @case('C')
                                                <span class="badge bg-danger">Critical</span>
                                                @break
                                            @case('H')
                                                <span class="badge bg-warning text-dark">High</span>
                                                @break
                                            @case('M')
                                                <span class="badge bg-info text-dark">Medium</span>
                                                @break
                                            @case('L')
                                                <span class="badge bg-success">Low</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">
                                                    {{ $assignment->priorityLevel->Description }}
                                                </span>
                                        @endswitch
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($assignment->Status)
                                        <span class="badge bg-{{ $assignment->Status->badgeColor() }}">
                                            {{ $assignment->Status->label() }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <a href="{{ route('assignrequest.show', $assignment->Id) }}"
                                           class="btn btn-sm btn-info text-white"
                                           title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($assignment->taskcompletion()->exists())
                                            <button class="btn btn-sm btn-secondary" title="In Use" disabled>
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        @else
                                          <a href="{{ route('assignrequest.edit', $assignment->Id) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                          </a>
                                            <form action="{{ route('assignrequest.destroy', $assignment->Id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this maintenance assignment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle me-2"></i>
            No maintenance assignments found.
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#AssignmentsTable').DataTable({
        scrollX: true,           // Horizontal scroll
        autoWidth: false,
        pageLength: 10,
        ordering: true,
        searching: true,
        lengthChange: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search assignments..."
        }
    });
});
</script>
@endsection
