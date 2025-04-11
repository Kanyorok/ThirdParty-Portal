@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Items Catalogue</h1>
    <a href="{{ route('procurement.items.create') }}" class="btn btn-primary mb-3">Add Item</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ ucfirst($item->type) }}</td>
                    <td>{{ optional($item->category)->name }}</td>
                    <td>
                        <a href="{{ route('procurement.items.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('procurement.items.destroy', $item) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
