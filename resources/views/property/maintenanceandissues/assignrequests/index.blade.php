@extends('layouts.app')
@php use Carbon\Carbon; @endphp

@section('title', 'Maintenance Assignments')

@section('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>
      .action-buttons {
          display: flex;
          flex-wrap: wrap;
          gap: 0.4rem;
          justify-content: center;
      }
  </style>
@endsection

@section('content')
<div class="container mt-4">

  <!-- Header Actions -->
  <div class="d-flex justify-content-end align-items-center mb-3">
    <a href="{{ route('assignrequest.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i> Assign Task
    </a>
  </div>

  <p class="text-muted mb-3">
    <small>This table displays all assigned maintenance tasks and their details.</small>
  </p>

  @if($assignments->count())
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <table id="AssignmentsTable" class="table table-bordered table-striped table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Request No.</th>
              <th>Assignment Type</th>
              <th>Expected Start</th>
              <th>Expected Completion</th>
              <th>Priority</th>
              <th>Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($assignments as $assignment)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $assignment->request->RequestNumber ?? '-' }}</td>
                <td>{{ $assignment->assignmentType->Description ?? '-' }}</td>
                <td>{{ $assignment->ExpectedStartDate ? Carbon::parse($assignment->ExpectedStartDate)->format('d M Y') : '-' }}</td>
                <td>{{ $assignment->ExpectedCompletion ? Carbon::parse($assignment->ExpectedCompletion)->format('d M Y') : '-' }}</td>
                <td>
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
                        <span class="badge bg-secondary">{{ $assignment->priorityLevel->Description }}</span>
                    @endswitch
                  @else
                    <span class="badge bg-secondary">N/A</span>
                  @endif
                </td>
                <td>
                  @if($assignment->Status)
                    <span class="badge bg-{{ $assignment->Status->badgeColor() }}">
                      {{ $assignment->Status->label() }}
                    </span>
                  @else
                    <span class="badge bg-secondary">{{ $assignment->Status ?? 'N/A' }}</span>
                  @endif
                </td>
                <td>
                  <div class="action-buttons">
                    <a href="{{ route('assignrequest.show', $assignment->Id) }}" class="btn btn-sm btn-info text-white" title="View Assignment">
                      <i class="bi bi-eye"></i>
                    </a>

                    <a href="{{ route('assignrequest.edit', $assignment->Id) }}" class="btn btn-sm btn-warning" title="Edit Assignment">
                      <i class="bi bi-pencil-square"></i>
                    </a>

                    {{-- Complete Task (Optional Future Action)
                    <a href="{{ route('workcompletion.create') }}" class="btn btn-sm btn-success" title="Mark Complete">
                      <i class="bi bi-check-circle"></i>
                    </a>
                    --}}

                    @if($assignment->taskcompletion()->exists())
                      <button class="btn btn-sm btn-secondary" title="In Use">
                        <i class="bi bi-lock"></i>
                      </button>
                    @else
                      <form action="{{ route('assignrequest.destroy', $assignment->Id) }}"
                            method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Are you sure you want to delete this maintenance assignment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Assignment">
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
  @else
    <div class="alert alert-info mt-3">
      <i class="bi bi-info-circle me-2"></i> No maintenance assignments found.
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
        pageLength: 10,
        ordering: true,
        searching: true,
        lengthChange: true
      });
    });
  </script>
@endsection
