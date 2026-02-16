@extends('layouts.app')

@section('title', 'Item Master List')
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

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {!! session('warning') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error_details'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="fas fa-times-circle me-2"></i>Import Errors</h5>
                <div class="import-errors" style="max-height: 300px; overflow-y: auto;">
                    {!! session('error_details') !!}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('success_details'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Import Success</h5>
                <div class="import-success" style="max-height: 300px; overflow-y: auto;">
                    {!! session('success_details') !!}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
             style="background-color: #add8e6;">
            <h4 class="mb-0">Item Master List</h4>
            <a href="{{ route('itemmasterlist.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add New Item
            </a>
        </div>

        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('itemmasterlist.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                        @csrf
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

                    <div class="alert alert-info alert-dismissible fade show mt-3 mb-0" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill me-2 mt-1" style="font-size: 1.25rem;"></i>
                            <div class="flex-grow-1">
                                <strong>Important Information:</strong>
                                <p class="mb-2 mt-1">When updating/editing items using Bulk Upload:</p>
                                <ul class="mb-0">
                                    <li>The system <strong>skips Items that are in use</strong> (items with stock, transfers, receipts, or requisitions)</li>
                                    <li>The system <strong>skips Inactive Items</strong></li>
                                    <li>You will receive a detailed report showing which items were updated, created, or skipped</li>
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="itemsTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Bar Code</th>
                            <th>Item Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Parent Category</th>
                            <th>Item Type</th>
                            <th>Inventory Type</th>
                            <th>UOM</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                            @php
                                $isInUse = $item->inUse();
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->ItemCode }}</td>
                                <td>{{ $item->BarCode }}</td>
                                <td>{{ $item->ItemName }}</td>
                                <td>{{ optional($item->price)->ActualPrice ? number_format(optional($item->price)->ActualPrice, 2) : '—' }}</td>
                                <td>{{ optional($item->category)->Name ?? 'Uncategorized' }}</td>
                                <td>{{ optional(optional($item->category)->parent)->Name ?? '—' }}</td>
                                <td>{{ optional($item->itemType)->type->Description ?? 'N/A' }}</td>
                                <td>{{ optional($item->inventoryType)->type->Description ?? 'N/A' }}</td>
                                <td>{{ optional($item->uom)->Code ?? '—' }}</td>
                                <td>
                                    @if ($item->status && $item->status->Description)
                                        @php
                                            $desc = strtolower($item->status->Description);
                                            $badgeClass = $desc === 'active' ? 'bg-success' :
                                                        ($desc === 'inactive' ? 'bg-secondary' : 'bg-warning');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($desc) }}</span>
                                    @else
                                        <span class="badge bg-warning">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('itemmasterlist.show', $item->Id) }}" 
                                           class="btn btn-view btn-sm" 
                                           data-bs-toggle="tooltip" 
                                           title="View Item">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($isInUse)
                                            <button class="btn btn-secondary btn-sm" 
                                                    disabled
                                                    data-bs-toggle="tooltip" 
                                                    title="Cannot edit - Item is in use">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('itemmasterlist.edit', $item->Id) }}" 
                                               class="btn btn-edit btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Edit Item">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @endif

                                        @if($isInUse)
                                            <span class="badge in-use-badge" 
                                                  data-bs-toggle="tooltip" 
                                                  title="Item is in use and cannot be edited or deleted">
                                                In Use
                                            </span>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-delete btn-sm delete-btn"
                                                    data-id="{{ $item->Id }}"
                                                    data-name="{{ $item->ItemName }}"
                                                    data-bs-toggle="tooltip" 
                                                    title="Delete Item">
                                                <i class="bi bi-trash text-white"></i>
                                            </button>

                                            <form id="delete-form-{{ $item->Id }}" 
                                                  action="{{ route('itemmasterlist.destroy', $item->Id) }}" 
                                                  method="POST" 
                                                  style="display:none;">
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
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        const table = $('#itemsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            language: { emptyTable: "No items found." },
            drawCallback: function () {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

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
                    $('#delete-form-' + itemId).submit();
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
.btn-secondary { opacity: 0.6; cursor: not-allowed !important; }
.btn-view:hover, .btn-edit:hover, .btn-delete:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.btn-view:hover { background-color: #0b5ed7 !important; }
.btn-edit:hover { background-color: #e0a800 !important; }
.btn-delete:hover { background-color: #c82333 !important; }
.bi { font-size: 0.875rem; color: white; }
.badge { font-size: 0.75em; padding: 0.35em 0.65em; }

.in-use-badge {
    background-color: #4680ff !important;
    color: #ffffff !important;
    font-weight: bold;
    height: 30px;
    min-width: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    cursor: default !important;
}

.table-responsive { border-radius: 0.375rem; }

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-info .bi-info-circle-fill {
    color: #0c5460;
}

.alert-info strong {
    color: #0a3a42;
}

.alert-info ul {
    padding-left: 1.5rem;
}

.alert-info ul li {
    margin-bottom: 0.25rem;
}
</style>
@endsection