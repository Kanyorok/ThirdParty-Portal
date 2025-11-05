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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<div class="container bg-white shadow-sm rounded p-4">

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
            <form action="{{ route('itemmasterlist.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                @csrf
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ route('itemmasterlist.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Item
                    </a>
                </div>
                <div class="col-md-6">
                    <label for="file" class="form-label fw-semibold">Bulk Upload Items</label>
                    <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <div class="form-text">Accepted formats: .xlsx, .xls, .csv</div>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-md-end justify-content-start gap-2">
                    <button type="submit" class="btn btn-success d-flex align-items-center">
                        <i class="bi bi-upload me-1 text-white"></i> Upload File
                    </button>
                    <a href="{{ route('itemmasterlist.export') }}" class="btn btn-primary d-flex align-items-center">
                        <i class="bi bi-download me-1 text-white"></i> Download Template
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script>
$(document).ready(function () {
    // Initialize DataTable
    var table = $('#itemMasterListTbl').DataTable({
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
        },
        drawCallback: function() {
            // Initialize tooltips after each table draw
            initializeTooltips();
        }
    });

    // Initialize tooltips
    function initializeTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Initialize tooltips on page load
    initializeTooltips();

    // Delete button handler
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        
        var itemId = $(this).data('item-id');
        var itemName = $(this).data('item-name');
        var $deleteBtn = $(this);
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete item: " + itemName + ". This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                $deleteBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split text-white"></i>');
                
                // Submit the delete form
                $.ajax({
                    url: "{{ url('itemmasterlist') }}/" + itemId,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Item has been deleted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            // Reload the table
                            table.ajax.reload();
                        });
                    },
                    error: function(xhr) {
                        $deleteBtn.prop('disabled', false).html('<i class="bi bi-trash text-white"></i>');
                        
                        let errorMessage = 'Failed to delete item.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }
        });
    });
});
</script>

<style>
.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 0.25rem;
    padding: 0.25rem 0.5rem;
    border: none;
    transition: all 0.2s ease-in-out;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Ensure tooltips work properly */
.tooltip {
    pointer-events: none;
}

/* Solid background colors with white icons */
.btn-view {
    background-color: #5b6b79 !important;
    color: white !important;
}

.btn-edit {
    background-color: #e58a00 !important;
    color: white !important;
}

.btn-delete {
    background-color: #dc3545 !important;
    color: white !important;
}

.btn-info {
    background-color: #0b5ed7 !important;
    color: white !important;
}

/* Hover effects */
.btn-view:hover {
    background-color: #0b5ed7 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-edit:hover {
    background-color: #e0a800 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-delete:hover {
    background-color: #c82333 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-info:hover {
    background-color: #0bb5d8 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Disabled state for in-use items */
.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

/* Ensure icons are properly sized and white */
.bi {
    font-size: 0.875rem;
    color: white;
}

/* Loading state */
.btn-delete:disabled {
    background-color: #6c757d !important;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
</style>
@endsection