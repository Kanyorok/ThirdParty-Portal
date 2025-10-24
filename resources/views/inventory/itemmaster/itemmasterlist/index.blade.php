@extends('layouts.app')

@section('title', 'Item Master List')

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

<div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Item Master List</h4>
        <div>
            <a href="{{ route('itemmasterlist.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Item
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Bulk Upload Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-3">📤 Bulk Upload Items</h5>
            <form action="{{ route('itemmasterlist.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                @csrf
                <div class="col-md-6">
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Accepted formats: .xlsx, .xls, .csv</div>
                </div>
                <div class="col-md-6 text-md-start text-end">
                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-upload"></i> Upload File
                    </button>
                    <a href="{{ route('itemmasterlist.export') }}" class="btn btn-sm btn-outline-primary mt-2">
                        📥 Download Template
                    </a>

                </div>
            </form>
        </div>
    </div>

    <!-- Item Table -->
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
            {data: 'ItemCode', name: 'ItemCode'},
            {data: 'BarCode', name: 'BarCode'},
            {data: 'ItemName', name: 'ItemName'},
            {data: 'ItemPrice', name: 'ItemPrice'},
            {data: 'Category', name: 'Category', defaultContent: 'Uncategorized'},
            {data: 'ParentCategory', name: 'ParentCategory', defaultContent: '—'},
            {data: 'ItemType', name: 'ItemType'},
            {data: 'InventoryType', name: 'InventoryType'},
            {data: 'UOM', name: 'UOM'},
            {data: 'Status', name: 'Status', orderable: false, searchable: false},
            {data: 'Action', name: 'Action', orderable: false, searchable: false}
        ],
        language: {
            emptyTable: "No items found."
        }
    });
});
</script>
@endsection
