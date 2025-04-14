@extends('layouts.app')
@section('title','Category List')
@section('content')
    <h1>Item Categories</h1>
    <a href="{{ route('procurement.categories.create') }}" class="btn btn-primary mb-3">Add Category</a>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->description }}</td>
                    <td>
                        <a href="{{ route('procurement.categories.edit', $category) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('procurement.categories.destroy', $category) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">No categories found.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
