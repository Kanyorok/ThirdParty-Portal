@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

<a href="{{ route('maintenancerequest.create') }}" class="btn btn-primary mb-3">New Request</a>
  <h4 class="fw-bold mb-3">📋 Maintenance Requests</h4>
    @if($maintenancerequests->count())
  <table id='Maintenancerequest' class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Request Number</th>
        <th>Property</th>
        <th>Block</th>
        <th>Floor</th>
        <th>Unit</th>
        <th>Reported By</th>
        <th>Issue Type</th>
        <th>Priority</th>
        <th>Issue Description</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($maintenancerequests as $maintenancerequest)
      <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $maintenancerequest->RequestNumber ?? '-' }}</td>
          <td>{{ $maintenancerequest->property->PropertyName ?? '_' }}</td>
          <td>{{ $maintenancerequest->block->BlockName ?? '_' }}</td>
          <td>{{ $maintenancerequest->floor->FloorLabel ?? '_'}}</td>
          <td>{{ $maintenancerequest->unit->UnitCode ?? '_'}}</td>
          <td>{{ $maintenancerequest->ReportedBy ?? '_'}}</td>
          <td>{{ $maintenancerequest->IssueType ?? '_'}}</td>
          <td>{{ $maintenancerequest->Priority ?? '_'}}</td>
          <td>{{ $maintenancerequest->IssueDescription ?? '_'}}</td>
          <td>
          <button class="btn btn-sm btn-outline-success">🛠 Assign</button>
                         <a href="{{ route('maintenancerequest.show', $maintenancerequest->Id) }}" class="btn btn-success btn-sm">View</a>
                        <a href="{{ route('maintenancerequest.edit', $maintenancerequest->Id) }}" class="btn btn-info btn-sm">Edit</a>
                        <form action="{{ route('maintenancerequest.destroy', $maintenancerequest->Id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lease?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No maintenance requests found.</p>
    @endif
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
