@extends('layouts.app')

@section('title', 'Item Master List')

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                    <th>Item Price</th>
                    <th>Category</th>
                    <th>Parent Category</th>
                    <th>Item Type</th>
                    <th>Inventory Type</th>
                    <th>UOM</th>
                    <th>Status</th>
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
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            { data: 'ItemCode', name: 'ItemCode' },
            { data: 'BarCode', name: 'BarCode' },
            { data: 'ItemName', name: 'ItemName' },
            {data: 'ItemPrice', name: 'ItemPrice'},
            {data: 'Category', name: 'Category', defaultContent: 'Uncategorized'},
            {data: 'ParentCategory', name: 'ParentCategory', defaultContent: '—'},
            {data: 'ItemType', name: 'ItemType'},
            {data: 'InventoryType', name: 'InventoryType'},
            {data: 'UOM', name: 'UOM'},
            {
                data: 'Status',
                name: 'Status',
                orderable: false,
                searchable: false
            },
            {
                data: 'Action',
                name: 'Action',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            emptyTable: "No items found."
        }
    });
});

function confirmDelete(Id) {
    if (confirm('⚠️ Are you sure you want to delete this unit?')) {
        document.getElementById('delete-form-' + Id).submit();
    }
}
</script>
@endsection
