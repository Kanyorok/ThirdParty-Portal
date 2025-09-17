@extends('layouts.app')
@section('title', 'Tenant Maintenance')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">
<a href="{{ route('addtenant.create') }}" class="btn btn-primary mb-3">Add Tenant</a>

<p><small>The list below consists of all tenants both active and inactive</small></p>
    @if($newtenants->count())
        <table id="addtenant" class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Type</th>
          <th>Name</th>
        <th>ID / Reg No.</th>
        <th>Phone</th>
        <th>Email</th>
          <th>Remarks</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($newtenants as $newtenant)
      <tr>
          <td>{{ $loop->iteration ?? '-' }}</td>
          <td>{{ $newtenant->type->Description ?? '-' }}</td>
          <td>{{ $newtenant->thirdParty->TradingName ?? '-' }}</td>
          <td>{{ $newtenant->thirdParty->RegistrationNumber ?? '-' }}</td>
          <td>{{ $newtenant->thirdParty->Phone ?? '-' }}</td>
          <td>{{ $newtenant->thirdParty->Email ?? '-' }}</td>
          <td>{{ $newtenant->Remarks ?? '-' }}</td>
          <td>
              @if($newtenant->IsActive == 1)
                  <span class="badge bg-success">Active</span>
              @else
                  <span class="badge bg-danger">Inactive</span>
              @endif
          </td>
        <td>
            <a href="{{ route('addtenant.show', $newtenant->Id) }}" class="btn btn-sm btn-info">View</a>
            <a href="{{ route('addtenant.edit', $newtenant->Id) }}" class="btn btn-primary btn-sm">Edit</a>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No properties registered yet.</p>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#addtenant').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
