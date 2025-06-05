@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('PropertyRegistry.create') }}" class="btn btn-primary mb-3">Add Property</a>

  <h4 class="fw-bold mb-3">📋 Registered Properties</h4>

@if($properties->count())
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property Name</th>
        <th>Code</th>
        <th>Type</th>
        <th>Category</th>
        <th>Owner</th>
        <th>Location</th>
        <th>Acquisition Date</th>
        <th>Property Description</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach( $properties as $property)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $property->PropertyName?? '-' }}</td>
        <td>{{ $property->PropertyCode?? '-' }}</td>
        <td>{{ $property->PropertyType?? '-' }}</td>
        <td>{{ $property->Category?? '-' }}</td>
        <td>{{ $property->Owner?? '-' }}</td>
        <td>{{ $property->TownCity?? '-' }}, {{ $property->Country?? '-' }}, {{ $property->AreaLocality?? '-' }}, {{ $property->GPSCoordinates?? '-' }}</td>
        <td>{{ $property->AcquisitionDate?? '-' }}</td>
        <td>{{ $property->PropertyDescription?? '-' }}</td>
        <td><span class="badge bg-success">Active</span></td>
        <td>
          <a href="{{ route('PropertyRegistry.show', $property->id) }}" class="btn btn-sm btn-info">👁 View</a>
          <button class="btn btn-sm btn-outline-warning">✏️ Edit</button>
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