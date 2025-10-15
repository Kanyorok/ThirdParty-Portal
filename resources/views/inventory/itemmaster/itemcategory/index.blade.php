@extends('layouts.app')

@section('title', 'Item Categories')

@section('content')
    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
                 style="background-color: #add8e6;">
                <h4 class="mb-0">🗂️ Item Categories</h4>
                <a href="{{ route('itemcategory.create') }}" class="btn btn-success">➕ Add Category</a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="itemCategoryTbl" class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Category Code</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $key => $category)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $category->CategoryCode }}</td>
                                    <td>{{ $category->Name }}</td>
                                    <td>{{ $category->Description }}</td>

                                    <td>
                                        @php $desc = $category->status->Description ?? null; @endphp
                                        @if($desc === 'Active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($desc === 'Inactive')
                                            <span class="badge bg-warning text-dark">Inactive</span>
                                        @else
                                            <span class="badge bg-secondary">Unknown</span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('itemcategory.show', $category->Id) }}" class="btn btn-secondary btn-sm">View</a>
                                        <a href="{{ route('itemcategory.edit', $category->Id) }}" class="btn btn-warning btn-sm">Edit</a>

                                        @if($category->inUse())
                                            <span class="badge bg-info text-light">
                                                <i class="fas fa-link me-1"></i> In Use
                                            </span>
                                        @else
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmDelete('{{ $category->Id }}', this)">
                                                Delete
                                            </button>
                                            <form id="delete-form-{{ $category->Id }}"
                                                  action="{{ route('itemcategory.destroy', $category->Id) }}"
                                                  method="POST" style="display:none;">
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
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Script --}}
    <script>
        function confirmDelete(id, button) {
            if (confirm('⚠️ Are you sure you want to delete this category?')) {
                button.disabled = true;
                button.innerText = 'Submitting...';
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
@endsection
