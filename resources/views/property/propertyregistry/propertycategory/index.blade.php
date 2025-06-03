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
        </tr>
    </thead>
    <tbody>
        @foreach ($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>{{ $category->PropertyCategoryName }}</td>
                <td>{{ $category->Description }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
   @else
<p>No property categories registered yet.</p>
@endif
</div>
@endsection