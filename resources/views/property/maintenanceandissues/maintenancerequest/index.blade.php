@extends('layouts.app')
@section('title', 'Maintenance Requests')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

<a href="{{ route('maintenancerequest.create') }}" class="btn btn-primary mb-3">New Request</a>
    <p><small>This is a list of raised maintenance requests</small></p>
  <table id='Maintenancerequest' class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Request Number</th>
        <th>Property</th>
        <th>Reported By</th>
        <th>Issue Type</th>
        <th>Priority</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($maintenancerequests as $maintenancerequest)
      <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $maintenancerequest->RequestNumber ?? '-' }}</td>
          <td>{{ $maintenancerequest->property->PropertyName ?? '_' }}</td>
          <td>{{ $maintenancerequest->ReportedBy ?? '_'}}</td>
          <td>{{ $maintenancerequest->issueType->Description ?? '_'}}</td>
          <td>{{ $maintenancerequest->priority->Description ?? '_'}}</td>
          <td>
          <a href="{{ route('maintenancerequest.show', $maintenancerequest->Id) }}" class="btn btn-success btn-sm">View</a>
          <a href="{{ route('maintenancerequest.edit', $maintenancerequest->Id) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('maintenancerequest.destroy', $maintenancerequest->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this lease?');">
          @if($maintenancerequest->requestId()->exists())
              <button class="btn btn-sm btn-secondary" disabled>
                  <i class="bi bi-lock"></i> In Use
              </button>
          @else
              <form action="{{ route('maintenancerequest.destroy', $maintenancerequest->Id) }}" 
                    method="POST" 
                    onsubmit="return confirm('Are you sure you want to delete this Request?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger">
                      <i class="bi bi-trash"></i> Delete
                  </button>
              </form>
          @endif
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#Maintenancerequest').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
