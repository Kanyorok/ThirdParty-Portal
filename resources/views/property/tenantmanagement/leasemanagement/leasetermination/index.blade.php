@extends('layouts.app')
@section('title', 'Lease Terminations')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
<a href="{{ route('terminatelease.create') }}" class="btn btn-primary mb-3">Terminate Lease</a>
    <h4 class="fw-bold mb-3">Lease Terminations</h4>

    @if($leaseterminations->count())
        <table id="leasetermination" class="table table-bordered table-striped align-middle">
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
          <td>{{ $leasetermination->lease->LeaseNumber  ?? '-' }}</td>
          <td>{{ $leasetermination->TerminationDate ? \Carbon\Carbon::parse($leasetermination->TerminationDate)->format('d/m/Y') : '-' }}</td>
          <td>{{ $leasetermination->code->Description ?? '-' }}</td>
          <td>{{ $leasetermination->Remarks ?? '-' }}</td>
          <td><a href="{{ route('terminatelease.show', $leasetermination->Id) }}"
                 class="btn btn-sm btn-outline-secondary">View</a></td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No termination record registered yet.</p>
    @endif
</div>

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#leasetermination').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
@endsection
