@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('terminatelease.create') }}" class="btn btn-primary mb-3">Terminate Lease</a>
  <h4 class="fw-bold mb-3">📋 Lease Terminations</h4>

@if($leaseterminations->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Lease</th>
        <th>Termination Date</th>
        <th>Reason</th>
        <th>Remarks</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($leaseterminations as $leasetermination)
      <tr>
        <td>{{ $loop->iteration ?? '-' }}</td>
        <td>{{ $leasetermination->LeaseID ?? '-' }}</td>
        <td>{{ $leasetermination->TerminationDate ?? '-' }}</td>
        <td>{{ $leasetermination->TerminationReason ?? '-' }}</td>
        <td>{{ $leasetermination->Remarks ?? '-' }}</td>
        <td><a href="{{ route('terminatelease.show', $leasetermination->id) }}" class="btn btn-sm btn-outline-secondary">📄 View</a></td>
      </tr>
      @endforeach
    </tbody>
  </table>
@else
  <p>No termination record registered yet.</p>
@endif
</div>
@endsection