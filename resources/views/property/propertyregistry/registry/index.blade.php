@extends('layouts.app')
@section('title', 'Property Management')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')

<a href="{{ route('PropertyRegistry.create') }}" class="btn btn-primary mb-3">Add Property</a>

<p><small>This screen displays a list of all registered properties</small></p>

@if($properties->count())
    <div class="container mt-4">
        <table id="propertyregistry" class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property Name</th>
        <th>Type</th>
        <th>Category</th>
        <th>Country</th>
          <th>Town/City</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach( $properties as $property)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $property->PropertyName?? '-' }}</td>
          <td>{{ $property->type->PropertyTypeName ?? '-' }}</td>
          <td>{{ $property->propertyCategory->Name?? '-' }}</td>
        <td>{{ $property->Country?? '-' }}</td>
          <td>{{ $property->propertyLocality->Name?? '-' }}</td>
        <td>
            @if($property->IsActive)
                <span class="badge bg-success">Active</span>
            @else
                <span class="badge bg-secondary">Inactive</span>
            @endif
        </td>
          <td>
              <a href="{{ route('PropertyRegistry.show', $property->Id) }}" class="btn btn-sm btn-info">View</a>
              <a href="{{ route('PropertyRegistry.edit', $property->Id) }}" class="btn btn-sm btn-warning">Edit</a>
              <form action="{{ route('PropertyRegistry.destroy', $property->Id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger"
                          onclick="return confirm('Are you sure you want to delete this property?');">Delete
                  </button>
              </form>
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
        $('#propertyregistry').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
