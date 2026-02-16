@extends('layouts.app')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <h2 class="mb-4">Category Details</h2>

    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <p><strong>Category Code:</strong> {{ $category->CategoryCode }}</p>
            <p><strong>Category Name:</strong> {{ $category->Name }}</p>
            <p><strong>Description:</strong> {{ $category->Description }}</p>

            @if($category->parent)
                <p><strong>Parent Category:</strong> {{ $category->parent->Name }}</p>
            @endif

            <p><strong>Status:</strong> {{ $category->status->Description ?? 'N/A' }}</p>

            <a href="{{ route('itemcategory.edit', $category->Id) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('itemcategory.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    @if($category->children->count() > 0)
        <h3 class="mt-5 mb-3">Subcategories</h3>
        <div class="table-responsive">
            <table class="table table-bordered align-middle shadow-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Subcategory Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($category->children as $subcategory)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $subcategory->CategoryCode }}</td>
                        <td>{{ $subcategory->Name }}</td>
                        <td>{{ $subcategory->Description ?? 'No description available' }}</td>

                        <td>
                            <a href="{{ route('itemcategory.show', $subcategory->Id) }}" class="btn btn-sm btn-primary">View</a>
                            <a href="{{ route('itemcategory.edit', $subcategory->Id) }}" class="btn btn-sm btn-warning">Edit</a>

                            @if($subcategory->hasItems())
                                <span class="badge bg-info text-light">In Use ({{ $subcategory->items->count() }})</span>
                            @else
                                <a href="#" class="btn btn-danger btn-sm"
                                   onclick="confirmDelete('{{ $subcategory->Id }}')">Delete</a>
                                <form id="delete-form-{{ $subcategory->Id }}"
                                      action="{{ route('itemcategory.destroy', $subcategory->Id) }}" method="POST"
                                      style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <script>
            function confirmDelete(Id) {
                if (confirm('⚠️ Are you sure you want to delete this subcategory?')) {
                    document.getElementById('delete-form-' + Id).submit();
                }
            }
        </script>
    @else
        <p class="mt-3 text-muted">No subcategories available.</p>
    @endif
</div>
@endsection
