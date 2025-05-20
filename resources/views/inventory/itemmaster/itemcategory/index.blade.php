@extends('layouts.app')

@section('title', 'Item Categories')

@section('content')
<div class="container-fluid bg-white shadow-sm rounded p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>🗂️ Item Categories</h4>
        <a href="{{ route('itemcategory.create') }}" class="btn btn-success">➕ Add Category</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <input type="text" class="form-control" placeholder="🔍 Search by Category Code or Name">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="itemCategoryTbl">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Category Code</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loaded via DataTables --}}
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let itemCategoryTbl = null;

    $(function () {
        $.fn.dataTable.ext.errMode = 'none';
        loadItemCategoryTable();
    });

    function loadItemCategoryTable() {
        if (itemCategoryTbl === null) {
            itemCategoryTbl = $('#itemCategoryTbl').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('itemcategory.index') }}",
                    error: function (jqXHR) {
                        console.warn("DataTable error", jqXHR);
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'CategoryCode', name: 'CategoryCode' },
                    { data: 'Name', name: 'Name' },
                    { data: 'Description', name: 'Description' },
                    { data: 'Action', name: 'Action', orderable: false, searchable: false },
                ],
                language: {
                    emptyTable: "No categories found."
                }
            });
        } else {
            itemCategoryTbl.ajax.reload();
        }
    }
</script>
<script>
    // CSRF setup for all AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    // Delete handler
    $(document).on('click', '.delete-category', function () {
        const id = $(this).data('Id');

        if (!confirm('Are you sure you want to delete this category?')) return;

        $.ajax({
            url: '/itemcategory/' + Id,
            type: 'DELETE',
            success: function (response) {
                alert(response.success);
                $('#itemCategoryTbl').DataTable().ajax.reload();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error deleting category.');
            }
        });
    });
</script>

@endsection
