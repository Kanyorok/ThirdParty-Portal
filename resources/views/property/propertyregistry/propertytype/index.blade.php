@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">

<a href="{{ route('propertytype.create') }}" class="btn btn-primary mb-3">Add Type</a>

  <h4 class="fw-bold mb-3">📋 Property Types</h4>
@if($types->count())
    <table class="table table-bordered table-striped align-middle">
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
                <td>{{ $type->PropertyCategoryId }}</td>
                <td>{{ $type->Description }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
   @else
 <p>No propertytype registered yet.</p>
@endif
</div>
@endsection
