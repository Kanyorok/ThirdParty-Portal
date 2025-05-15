@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Categories</h2>
    <a href="{{ route('itemcategory.create') }}" class="btn btn-primary mb-3">Add New Category</a>

    <table class="table table-bordered" id="categoryTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Category Code</th>
                <th>Name</th>
                <th>Parent Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>

<script>
    let categoryTable = null;
    $(document).ready(function () {
        $.fn.dataTable.ext.errMode = 'none';
        fetchCategoryTable();
    });

    function fetchCategoryTable() {
        if (categoryTable === null) {
            categoryTable = $('#categoryTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('itemcategory.index') }}",
                    error: function (jqXHR) {
                        console.error("Error loading categories: ", jqXHR.status);
                    }
                },
                columns: [
                    { data: "DT_RowIndex", name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'CategoryCode', name: 'CategoryCode' },
                    { data: 'CategoryName', name: 'CategoryName' },
                    { 
                        data: 'parent.CategoryName', 
                        name: 'ParentId', 
                        render: function (data) {
                            return data ? data : 'None (Top-Level)';
                        }
                    },
                    { 
                        data: 'Action', 
                        name: 'Action', 
                        orderable: false, 
                        searchable: false, 
                        render: function (data, type, row) {
                            return `
                                <a href="{{ route('itemcategory.show', '') }}/${row.Id}" class="btn btn-sm btn-primary">View</a>
                                <a href="{{ route('itemcategory.edit', '') }}/${row.Id}" class="btn btn-sm btn-warning">Edit</a>
                                <a href="#" onclick="confirmDelete(${row.Id})" class="btn btn-sm btn-danger">Delete</a>
                            `;
                        }
                    }
                ],
                language: {
                    emptyTable: "No categories found."
                }
            });

            categoryTable.on('error', function (error) {
                console.warn("Issue loading categories.");
                console.log(error);
            });
        } else {
            categoryTable.ajax.reload();
        }
    }

    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this category?")) {
            $.ajax({
                url: "{{ route('itemcategory.destroy', '') }}/" + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(response) {
                    alert('Category deleted successfully.');
                    categoryTable.ajax.reload();
                },
                error: function(error) {
                    alert('Error deleting category.');
                }
            });
        }
    }
</script>
@endsection
