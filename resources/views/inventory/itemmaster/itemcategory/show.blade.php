@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Category Details</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $item->CategoryName }}</h5>
            <p class="card-text"><strong>Category Code:</strong> {{ $item->CategoryCode }}</p>
            <p class="card-text"><strong>Description:</strong> {{ $item->Description }}</p>

            <p class="card-text">
                <strong>Parent Category:</strong> 
                {{ $item->parent ? $item->parent->CategoryName : 'None (Top-Level Category)' }}
            </p>

            <p class="card-text"><strong>Status:</strong> {{ $item->Status ? 'Active' : 'Inactive' }}</p>
            
            <a href="{{ route('itemcategory.edit', $item->Id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('itemcategory.destroy', $item->Id) }}" class="btn btn-danger">Delete</a>
            <a href="{{ route('itemcategory.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    @if($item->children->count() > 0)
        <h3 class="mt-4">Subcategories</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subcategory Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item->children as $subcategory)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $subcategory->CategoryCode }}</td>
                        <td>{{ $subcategory->CategoryName }}</td>
                        <td>{{ $subcategory->Description ?? 'No description available' }}</td>
                        <td>
                            <a href="{{ route('itemcategory.show', $subcategory->Id) }}" class="btn btn-sm btn-primary">View</a>
                            <a href="{{ route('itemcategory.edit', $subcategory->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <a href="{{ route('itemcategory.destroy', $subcategory->Id) }}" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="mt-3 text-muted">No subcategories available.</p>
    @endif
</div>
@endsection
