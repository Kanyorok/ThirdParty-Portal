@extends('layouts.app')
@section('title', 'Property Category')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

<a href="{{ route('propertycategory.create') }}" class="btn btn-primary mb-3">Add Category</a>

<p><small>This screen displays a list of property categories</small></p>
    @if($categories->count())
        <table id="propertycategory" class="table table-bordered table-striped align-middle">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th> Actions</th>
            </tr>
    </thead>
    <tbody>

    @foreach ($categories as $category)
        <tr>
            <td>{{ $loop->iteration ?? '-' }}</td>
            <td>{{ $category->Name ?? '-'}}</td>
            <td>{{ $category->Description ?? '-' }}</td>
            <td>
                <a href="{{ route('propertycategories.update', $category->Id) }}"
                   class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('propertycategory.destroy', $category->Id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('Are you sure you want to delete this category?');">Delete
                    </button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
        </table>
    @else
        <p>No property categories registered yet.</p>
    @endif
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#propertycategory').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
