@extends('layouts.app')
@section('title', 'Property Documents')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

<a href="{{ route('attachments.create') }}" class="btn btn-primary mb-3">Attach Document</a>
  <h4 class="fw-bold mb-3">📋 Property Documents</h4>
    @if($propertyattachments->count())
        <table id="propertyattachment" class="table table-bordered table-striped align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Property</th>
        <th>Document Title</th>
        <th>Type</th>
          <th>Description</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($propertyattachments as $propertyattachment)
      <tr>
          <td>{{ $propertyattachment->Id }}</td>
          <td>{{ $propertyattachment->property->PropertyName}}</td>
          <td>{{ $propertyattachment->DocumentTitle }}</td>
          <td>{{ $propertyattachment->documenttype->Description }}</td>
          <td>{{ $propertyattachment->Description}}</td>
          <td>
          <a href="{{ route('attachments.edit', $propertyattachment->Id) }}" class="btn btn-info btn-sm">Edit</a>
          <form class="d-inline" action="{{ route('attachments.destroy', $propertyattachment->Id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this property attachment?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Delete</button>
          </form>
      </td>
      </tr>
    @endforeach
    </tbody>
  </table>
    @else
        <p>No property attachments registered yet.</p>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertyattachment').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
