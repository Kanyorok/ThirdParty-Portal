@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('maintenancerequest.create') }}" class="btn btn-primary mb-3">New Request</a>
  <h4 class="fw-bold mb-3">📋 Maintenance Requests</h4>
    @if($maintenancerequests->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
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
          <td>{{ $maintenancerequest->Property }}</td>
          <td>{{ $maintenancerequest->Block }}</td>
          <td>{{ $maintenancerequest->Floor }}</td>
          <td>{{ $maintenancerequest->Unit }}</td>
          <td>{{ $maintenancerequest->ReportedBy}}</td>
          <td>{{ $maintenancerequest->IssueType }}</td>
          <td>{{ $maintenancerequest->Priority }}</td>
          <td>{{ $maintenancerequest->IssueDescription }}</td>
          <td>
          <button class="btn btn-sm btn-outline-success">🛠 Assign</button>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No maintenance requests found.</p>
    @endif
</div>
@endsection
