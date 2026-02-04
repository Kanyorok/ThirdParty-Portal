@extends('layouts.app')
@section('title', 'Stock Items')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
@endsection

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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
             style="background-color: #add8e6;">
            <h4 class="mb-0">SKU Items List</h4>
            <a href="{{ route('sku.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add New Stock Item
            </a>
        </div>

        <div class="card-body">
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

            <div class="table-responsive">
                <table id="stockitemsTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>SKU Code</th>
                            <th>Item</th>
                            <th>Branch</th>
                            <th>Store</th>
                            <th>Current Qty</th>
                            <th>Min</th>
                            <th>Reorder</th>
                            <th>Last Received</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->SKUCode }}</td>
                                <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                                <td>{{ $item->branch->Name ?? 'N/A' }}</td>
                                <td>{{ $item->store->StoreName ?? 'N/A' }}</td>
                                <td>{{ $item->CurrentQty }}</td>
                                <td>{{ $item->Min }}</td>
                                <td>{{ $item->Reorder }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->LastReceived)->format('d M Y') }}</td>                               <td>
                                    <span class="badge {{ $item->Status ? 'bg-success' : 'bg-warning' }}">
                                        {{ $item->Status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('sku.show', $item->Id) }}"
                                           class="btn btn-view btn-sm"
                                           data-bs-toggle="tooltip"
                                           title="View Item">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('sku.edit', $item->Id) }}"
                                           class="btn btn-edit btn-sm"
                                           data-bs-toggle="tooltip"
                                           title="Edit Item">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <button type="button"
                                                class="btn btn-delete btn-sm delete-btn"
                                                data-id="{{ $item->Id }}"
                                                data-name="{{ $item->SKUCode }}"
                                                title="Delete Item">
                                            <i class="bi bi-trash text-white"></i>
                                        </button>
                                    </div>

                                    <form id="delete-form-{{ $item->Id }}"
                                          action="{{ route('sku.destroy', $item->Id) }}"
                                          method="POST"
                                          style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        const table = $('#stockitemsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            language: { emptyTable: "No stock items found." },
            drawCallback: initializeTooltips
        });

        function initializeTooltips() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
        initializeTooltips();

        $(document).on('click', '.delete-btn', function () {
            const itemId = $(this).data('id');
            const itemName = $(this).data('name');

            Swal.fire({
                title: 'Confirm Deletion',
                html: `Are you sure you want to delete <strong>${itemName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('#delete-form-' + itemId);
                    form.submit();
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

.btn-group .btn:last-child { margin-right: 0; }

.btn-view { background-color: #5b6b79 !important; color: white !important; }
.btn-edit { background-color: #e58a00 !important; color: white !important; }
.btn-delete { background-color: #dc3545 !important; color: white !important; }

.btn-view:hover, .btn-edit:hover, .btn-delete:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-view:hover { background-color: #0b5ed7 !important; }
.btn-edit:hover { background-color: #e0a800 !important; }
.btn-delete:hover { background-color: #c82333 !important; }

.bi { font-size: 0.875rem; color: white; }
.badge { font-size: 0.75em; padding: 0.35em 0.65em; }
.table-responsive { border-radius: 0.375rem; }
</style>
@endsection
