@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('propertycategory.create') }}" class="btn btn-primary mb-3">Add Category</a>

  <h4 class="fw-bold mb-3">📋 Property Categories</h4>
@if($categories->count())
    <table class="table table-bordered table-striped align-middle">
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
                <td>{{ $loop->iteration }}</td>
                <td>{{ $category->Name }}</td>
                <td>{{ $category->Description }}</td>
                <td>
                    <a href="{{ route('propertycategories.update', $category->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('propertycategory.destroy', $category->Id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">Delete</button>
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
@endsection