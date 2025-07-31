@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('title', 'Maintenance Assignments')
@section('content')
<div class="container mt-4">

<a href="{{ route('assignrequest.create') }}" class="btn btn-primary mb-3">Assign Task</a>
  <h4 class="fw-bold mb-3">Maintenance Assignments</h4>

    @if($assignments->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Request Number</th>
          <th>Assignment Type</th>
          <th>Expected Start Date</th>
          <th>Expected Completion</th>
          <th>Priority Level</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($assignments as $assignment)
      <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{  $assignment->request->RequestNumber ?? 'N/A' }}</td>
          <td>{{ $assignment->assignmentType->Description }}</td>
          <td>{{ Carbon::parse($assignment->ExpectedStartDate)->format('d/m/Y') }}</td>
          <td>{{ Carbon::parse($assignment->ExpectedCompletion)->format('d/m/Y') }}</td>
          <td>{{ $assignment->priorityLevel->Description ?? 'N/A' }}</td>
          <td><span class="badge bg-{{ $assignment->Status->badgeColor() }}">{{ $assignment->Status->label() }}</span></td>
          <td>
          <a href="{{ route('assignrequest.show', $assignment->Id) }}" class="btn btn-sm btn-primary">View</a>
          <a href="{{ route('assignrequest.edit', $assignment->Id) }}" class="btn btn-info btn-sm">Edit</a>
          <a href="#" class="btn btn-success btn-sm">Complete</a>
          <form action="{{ route('assignrequest.destroy', $assignment->Id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lease?');" class="d-inline">
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
