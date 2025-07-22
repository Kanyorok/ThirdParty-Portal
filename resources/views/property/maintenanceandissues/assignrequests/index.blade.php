@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('assignrequest.create') }}" class="btn btn-primary mb-3">Assign Task</a>
  <h4 class="fw-bold mb-3">📋 Maintenance Assignments</h4>

    @if($assignments->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Request Number</th>
          <th>Property</th>
          <th>Block</th>
          <th>Floor</th>
          <th>Unit</th>
          <th>Assignment Date</th>
          <th>Assignment Type</th>
          <th>Internal Technician</th>
          <th>Prequalified Vendor</th>
          <th>Expected Start Date</th>
          <th>Expected Completion</th>
          <th>Priority Level</th>
          <th>Instruction Notes</th>
        <th>Status</th>
        <th>Priority</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($assignments as $assignment)
      <!-- Assignment to Internal Staff -->
      <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{  $assignment->request->RequestNumber ?? 'N/A' }}</td>
          <td>{{ $assignment->Property ?? 'N/A' }}</td>
          <td>{{ $assignment->Block ?? 'N/A' }}</td>
          <td>{{ $assignment->Floor ?? 'N/A' }}</td>
          <td>{{ $assignment->Unit ?? 'N/A' }}</td>
          <td>{{ $assignment->AssignmentDate }}</td>
          <td>{{ $assignment->assignmentType->Description }}</td>
          <td>{{ $assignment->internalTechnician->JobTitle ?? 'N/A' }}</td>
          <td>{{ $assignment->prequalifiedVendor->SupplierName ?? 'N/A' }}</td>
          <td>{{ $assignment->ExpectedStartDate }}</td>
          <td>{{ $assignment->ExpectedCompletion }}</td>
          <td>{{ $assignment->priorityLevel->Description ?? 'N/A' }}</td>
          <td>{{ $assignment->InstructionNotes }}</td>
        <td><span class="badge bg-warning text-dark">In Progress</span></td>
        <td><span class="badge bg-danger">High</span></td>
        <td>
            <a href="{{ route('assignrequest.show', $assignment->Id) }}" class="btn btn-sm btn-outline-primary">👁
                View</a>
        <a href="{{ route('workcompletion.index', $assignment->Id) }}" class="btn btn-info btn-sm">Completion</a>
        <a href="{{ route('assignrequest.edit', $assignment->Id) }}" class="btn btn-info btn-sm">Edit</a>
          <form action="{{ route('assignrequest.destroy', $assignment->Id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lease?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
          </form>
      </td>
      </tr>
    </tbody>
      @endforeach
  </table>
    @else
        <p>No maintenance assignment requests found.</p>
    @endif
</div>
@endsection
