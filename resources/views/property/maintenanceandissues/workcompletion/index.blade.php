@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('workcompletion.create') }}" class="btn btn-primary mb-3">Log Completion</a>
  <h4 class="fw-bold mb-3">📋 Maintenance Completion Records</h4>

@if($workCompletions->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property</th>
        <th>Block</th>
        <th>Floor</th>
        <th>Unit</th>
        <th>Issue Description</th>
        <th>Completion Date</th>
        <th>Work Done Summary</th>
        <th>Parts Used</th>
        <th>Cost</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($workCompletions as $workCompletion)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $workCompletion->Property }}</td>
        <td>{{ $workCompletion->Block }}</td>
        <td>{{ $workCompletion->Floor }}</td>
        <td>{{ $workCompletion->Unit }}</td>
        <td>{{ $workCompletion->IssueDescription }}</td>
        <td>{{ $workCompletion->CompletionDate }}</td>
        <td>{{ $workCompletion->WorkDoneSummary}}</td>
        <td>{{ $workCompletion->PartsUsed }}</td>
        <td>{{ $workCompletion->Cost }}</td>
        <td>{{ $workCompletion->FinalStatus }}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary">⬇ Download</button>
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