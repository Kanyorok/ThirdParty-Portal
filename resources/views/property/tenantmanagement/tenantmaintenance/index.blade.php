@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('addtenant.create') }}" class="btn btn-primary mb-3">Add Tenant</a>
  <h4 class="fw-bold mb-3">📋 Registered Tenants</h4>

@if($newtenants->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Type</th>
        <th>Name</th>
        <th>ID / Reg No.</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Nationality</th>
        <th>Postal Address</th>        
        <th>Remarks</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
       @foreach($newtenants as $newtenant)
      <tr>
        <td>{{ $loop->iteration ?? '-' }}</td>
        <td>{{ $newtenant->TenantType ?? '-' }}</td>
        <td>{{ $newtenant->TenantName ?? '-' }}</td>
        <td>{{ $newtenant->IDRegistrationNo ?? '-' }}</td>
        <td>{{ $newtenant->PhoneNumber ?? '-' }}</td>
        <td>{{ $newtenant->EmailAddress ?? '-' }}</td>
        <td>{{ $newtenant->Nationality ?? '-' }}</td>
        <td>{{ $newtenant->PostalAddress ?? '-' }}</td>        
        <td>{{ $newtenant->Remarks ?? '-' }}</td>
        <td><span class="badge bg-success">Active</span></td>
        <td>
          <a href="{{ route('addtenant.show', $newtenant->id) }}" class="btn btn-sm btn-info">👁 View</a>
          <a href="{{ route('addtenant.create') }}" class="btn btn-sm btn-outline-warning">✏️ Edit</a>
        </td>
      </tr>
       @endforeach
    </tbody>
  </table>
@else
 <p>No properties registered yet.</p>
@endif
</div>
@endsection