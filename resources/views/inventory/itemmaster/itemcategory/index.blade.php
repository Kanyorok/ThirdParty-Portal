@extends('layouts.app')

@section('title', 'Item Categories')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

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
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $category->CategoryCode }}</td>
                                    <td>{{ $category->Name }}</td>
                                    <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $category->Description }}</td>

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
                                        <div class="d-flex gap-1 align-items-center">
                                            <a href="{{ route('itemcategory.show', $category->Id ) }}" 
                                               class="btn btn-sm btn-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('itemcategory.edit', $category->Id ) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            @if($category->inUse())
                                                <span class="badge bg-info text-light">
                                                    <i class="fas fa-link me-1"></i> In Use
                                                </span>
                                            @else
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="confirmDelete('{{ $category->Id }}', this)"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <form id="delete-form-{{ $category->Id }}"
                                                      action="{{ route('itemcategory.destroy', $category->Id ) }}"
                                                      method="POST" style="display:none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id, button) {
            if (confirm('⚠️ Are you sure you want to delete this category?')) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
    $(document).ready(function () {
        $('#itemCategoryTbl').DataTable({
            paging: true,
            pageLength: 10,
            lengthMenu: [5, 10, 20, 50],
            ordering: true,
            searching: true,
            info: true,
            language: {
                emptyTable: "No categories found",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ categories",
            }
        });
    });
</script>
@endsection