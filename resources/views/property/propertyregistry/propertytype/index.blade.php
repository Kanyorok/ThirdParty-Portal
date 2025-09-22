@extends('layouts.app')
@section('title', 'Property Type')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

<a href="{{ route('propertytype.create') }}" class="btn btn-primary mb-3">Add Type</a>

    <p><small>This is a list of property types linked to specific categories</small></p>
@if($types->count())
    <table id="propertytype" class="table table-bordered table-striped align-middle">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($types as $Index => $type)
            <tr>
                <td>{{ $Index + 1 }}</td>
                <td>{{ $type->PropertyTypeName ?? '-' }}</td>
                <td>{{ $type->propertycategory->Name ?? '-' }}</td>
                <td>{{ $type->Description }}</td>
                <td>
                    <a href="{{ route('propertytype.edit', $type->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('propertytype.destroy', $type->Id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this type?');">Delete
                        </button>
                    </form>
                </td>
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
