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
          <th>Property</th>
          <th>Block</th>
          <th>Floor</th>
          <th>Unit</th>
          <th>Issue Description</th>
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
          <td>{{ $assignment->Property }}</td>
          <td>{{ $assignment->Block }}</td>
          <td>{{ $assignment->Floor }}</td>
          <td>{{ $assignment->Unit }}</td>
          <td>{{ $assignment->IssueDescription }}</td>
          <td>{{ $assignment->AssignmentDate }}</td>
          <td>{{ $assignment->AssignmentType }}</td>
          <td>{{ $assignment->InternalTechnician }}</td>
          <td>{{ $assignment->PrequalifiedVendor }}</td>
          <td>{{ $assignment->ExpectedStartDate }}</td>
          <td>{{ $assignment->ExpectedCompletion }}</td>
          <td>{{ $assignment->PriorityLevel }}</td>
          <td>{{ $assignment->InstructionNotes }}</td>
        <td><span class="badge bg-warning text-dark">In Progress</span></td>
        <td><span class="badge bg-danger">High</span></td>
        <td>
            <a href="{{ route('assignrequest.show', $assignment->id) }}" class="btn btn-sm btn-outline-primary">👁
                View</a>
          <button class="btn btn-sm btn-outline-success">✔ Complete</button>
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
