@extends('layouts.app')
@section('title', 'Maintenance Completion')
@php use Carbon\Carbon; @endphp
@section('content')
<div class="container mt-4">
<a href="{{ route('workcompletion.create') }}" class="btn btn-primary mb-3">Log Completion</a>
  <h4 class="fw-bold mb-3">Maintenance Completion Records</h4>

    @if($workCompletions->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
          <th>Request Number</th>
          <th>Completion Date</th>
          <th>Work Done Summary</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach ($workCompletions as $workCompletion)
      <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $workCompletion->request->request->RequestNumber}}</td>
          <td>{{ Carbon::parse(time: $workCompletion->CompletionDate)->format('d/m/Y') }}</td>
          <td>{{ $workCompletion->WorkDoneSummary}}</td>
          <td>{{ $workCompletion->finalstatus->Description }}</td>
        <td>
          <a href="{{ route('workcompletion.show', $workCompletion->Id) }}" class="btn btn-sm btn-primary">view</a>
          <a href="{{ route('workcompletion.edit', $workCompletion->Id) }}" class="btn btn-info btn-sm">Edit</a>
          <form class="d-inline" action="{{ route('workcompletion.destroy', $workCompletion->Id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this work completion?');">
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
        <p>No maintenance work completion records found.</p>
    @endif
</div>
@endsection
