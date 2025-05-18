@extends('layouts.app')

@section('title', 'Item Master List')

@section('content')

<div class="container bg-white shadow-sm rounded p-4">
    <h4 class="mb-4">📦 Item Master List</h4>

    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('itemmasterlist.create') }}" class="btn btn-success">➕ Add New Item</a>
        <input type="text" class="form-control w-25" id="searchBox" placeholder="🔍 Search...">
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="itemMasterListTbl">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item Code</th>
                    <th>Bar Code</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Parent Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {
    const table = $('#itemMasterListTbl').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('itemmaster.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, visible: true },
            { data: 'ItemCode', name: 'ItemCode' },
            { data: 'BarCode', name: 'BarCode' },
            { data: 'ItemName', name: 'ItemName' },
            { data: 'Category', name: 'Category' },
            { data: 'ParentCategory', name: 'ParentCategory' },

            {
                data: 'Action',
                name: 'Action',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                        <a href="{{ url('inventory/itemmasterlist') }}/${row.Id}" class="btn btn-sm btn-primary">View</a>
                        <a href="{{ url('inventory/itemmasterlist') }}/${row.Id}/edit" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" onclick="confirmDelete(${row.Id})" class="btn btn-sm btn-danger">Delete</a>
                    `;
                }
            }
        ],
        language: {
            emptyTable: "No items found."
        }
    });
});

function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this item?")) {
        $.ajax({
            url: "{{ url('inventory/itemmasterlist') }}/" + id,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                alert('Item deleted successfully.');
                $('#itemMasterListTbl').DataTable().ajax.reload();
            },
            error: function(error) {
                alert('Error deleting item.');
            }
        });
    }
}
</script>
@endsection
