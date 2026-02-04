@extends('layouts.app')
@section('title', 'Create New Store')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [data-bs-toggle="tooltip"] {
            cursor: help;
        }
        
        .btn-danger:disabled {
            cursor: not-allowed !important;
            opacity: 0.5;
            pointer-events: auto !important;
        }
        
        .tooltip-inner {
            max-width: 300px;
            padding: 8px 12px;
            font-size: 0.875rem;
            text-align: left;
        }
        
        .alert-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #004085;
        }
        
        .alert-info .alert-heading {
            color: #003366;
        }
        
        .alert-info hr {
            border-top-color: #b3d9ff;
        }
    </style>
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
        <div class="alert alert-info alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-info-circle me-3 mt-1" style="font-size: 1.5rem;"></i>
                <div>
                    <p class="mb-2">
                        <strong>Important:</strong> Stores that contain stock items cannot be deleted to maintain data integrity and prevent inventory discrepancies.
                    </p>
                    <hr class="my-2">
                    <p class="mb-0 small">
                        <i class="fas fa-lightbulb text-warning"></i> 
                        <strong>Tip:</strong> To delete a store with existing stock items, you must first transfer or remove all stock items from that store.
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="card shadow rounded-4">
            <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
                 style="background-color: #add8e6;">
                <h4 class="mb-0">Stores List</h4>
                <a href="{{ route('stores.create') }}" class="btn btn-success">➕ Add New Store</a>
            </div>
            <div class="card-body">

                <table id="storesTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Store ID</th>
                        <th>Store Name</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stores as $key => $store)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $store->StoreID }}</td>
                            <td>{{ $store->StoreName }}</td>
                            <td>{{ $store->branch->Name }}</td>
                            <td>
                                <span class="badge {{ $store->Status ? 'bg-success' : 'bg-warning' }}">
                                    {{ $store->Status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('stores.show', $store->Id) }}"
                                       class="btn btn-sm btn-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('stores.edit', $store->Id) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @php
                                        $hasStockItems = $store->stock_items_count > 0;
                                        $tooltipMessage = $hasStockItems 
                                            ? '🚫 Cannot delete this store because it contains ' . $store->stock_items_count . ' stock item(s). Please transfer or remove all stock items before deletion.'
                                            : '🗑️ Click to delete this store';
                                    @endphp
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="confirmDelete('{{ $store->Id }}')" 
                                            title="{{ $tooltipMessage }}"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            data-bs-html="true"
                                            @if($hasStockItems) disabled @endif>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    
                                    <form id="delete-form-{{ $store->Id }}"
                                          action="{{ route('stores.destroy', $store->Id) }}" method="POST"
                                          style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#storesTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    emptyTable: "No stores found"
                }
            });
            
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        function confirmDelete(Id) {
            if (confirm('⚠️ Are you sure you want to delete this store?')) {
                document.getElementById('delete-form-' + Id).submit();
            }
        }
    </script>
@endsection