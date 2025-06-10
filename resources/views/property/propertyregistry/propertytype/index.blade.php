@extends('layouts.app')
@section('title', 'Property Management')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

<a href="{{ route('propertytype.create') }}" class="btn btn-primary mb-3">Add Type</a>

  <h4 class="fw-bold mb-3">📋 Property Types</h4>
    @if($types->count())
        <table id="propertytype" class="table table-bordered table-striped align-middle">
    <thead class="table-light">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Category</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($types as $Index => $type)
        <tr>
            <td>{{ $Index + 1 }}</td>
            <td>{{ $type->PropertyTypeName }}</td>
            <td>{{ $type->propertycategory->Name }}</td>
            <td>{{ $type->Description }}</td>
        </tr>
    @endforeach
    </tbody>
        </table>
    @else
        <p>No propertytype registered yet.</p>
    @endif
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertytype').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
