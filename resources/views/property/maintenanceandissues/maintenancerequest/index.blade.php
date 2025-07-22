@extends('layouts.app')
@section('title', 'Maintenance Requests')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

<a href="{{ route('maintenancerequest.create') }}" class="btn btn-primary mb-3">New Request</a>
  <h4 class="fw-bold mb-3">Maintenance Requests</h4>
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
            @csrf
              @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
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
