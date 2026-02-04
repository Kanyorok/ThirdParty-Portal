@extends('layouts.app')
@section('title', 'Edit Store')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        
        .form-check-input:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        [data-bs-toggle="tooltip"] {
            cursor: help;
        }
    </style>
@endsection
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
    <body class="bg-light">

    <div class="container mt-5">
        @if($hasStockItems && $store->Status)
            <div class="alert alert-warning alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle me-3 mt-1" style="font-size: 1.5rem;"></i>
                    <div>
                        <p class="mb-2">
                            <strong>Notice:</strong> This store currently contains <strong>{{ $stockItemsCount }}</strong> stock item(s) and cannot be deactivated.
                        </p>
                        <hr class="my-2">
                        <p class="mb-0 small">
                            <i class="fas fa-lightbulb text-warning"></i> 
                            <strong>Tip:</strong> To deactivate this store, you must first transfer or remove all stock items from it.
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow rounded-4">
            <div class="card-header text-dark rounded-top-4" style="background-color: #add8e6;">
                <h4 class="mb-0">Edit Store</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('stores.update', $store->Id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="StoreID" class="form-label">Store ID</label>
                            <input type="text" name="StoreID" class="form-control" id="StoreID"
                                   value="{{ $store->StoreID }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="StoreName" class="form-label">Store Name <span class="text-danger">*</span></label>
                            <input type="text" name="StoreName" class="form-control" id="StoreName"
                                   value="{{ old('StoreName', $store->StoreName) }}" required>
                            @error('StoreName')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="{{ $branch->Name }}" readonly>
                            <input type="hidden" name="BranchID" value="{{ $branch->Id }}">
                            @error('BranchID')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input type="hidden" name="IsMainStore" value="0">
                                <input type="checkbox" name="IsMainStore" id="IsMainStore" 
                                       value="1" class="form-check-input" 
                                       {{ old('IsMainStore', $store->IsMainStore) ? 'checked' : '' }}
                                       {{ !$store->IsMainStore && $mainStoreExists ? 'disabled' : '' }}>
                                <label class="form-check-label fw-bold" for="IsMainStore">
                                    Is Main Store
                                </label>
                            </div>
                            @if(!$store->IsMainStore && $mainStoreExists)
                                <div class="text-warning small mt-1">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    A main store already exists for this branch. Only one main store is allowed.
                                </div>
                            @elseif($store->IsMainStore)
                                <div class="text-success small mt-1">
                                    <i class="fas fa-check-circle"></i>
                                    This is the main store for the branch.
                                </div>
                            @else
                                <div class="text-muted small mt-1">
                                    Check to set as the main store for this branch.
                                </div>
                            @endif
                            @error('IsMainStore')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input type="hidden" name="Status" value="0">
                                @php
                                    $canDeactivate = !($hasStockItems && $store->Status);
                                    $statusTooltip = ($hasStockItems && $store->Status) 
                                        ? '🚫 Cannot deactivate this store because it contains ' . $stockItemsCount . ' stock item(s). Please transfer or remove all stock items before deactivation.'
                                        : 'Check to activate the store';
                                @endphp
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="Status" 
                                       value="1"
                                       id="Status" 
                                       {{ old('Status', $store->Status) ? 'checked' : '' }}
                                       {{ !$canDeactivate ? 'disabled' : '' }}
                                       data-bs-toggle="tooltip"
                                       data-bs-placement="top"
                                       title="{{ $statusTooltip }}">
                                <label class="form-check-label" for="Status">Is Active</label>
                            </div>
                            @if(!$canDeactivate)
                                <div class="text-warning small ms-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Cannot deactivate: Store has {{ $stockItemsCount }} stock item(s)
                                </div>
                            @endif
                            @error('Status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Update
                            Store
                        </button>
                        <a href="{{ route('stores.index') }}" class="btn btn-danger px-4 ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            document.querySelector('form').addEventListener('submit', function(e) {
                const isMainStore = document.getElementById('IsMainStore');
                const wasMainStore = {{ $store->IsMainStore ? 'true' : 'false' }};
                
                if (isMainStore.checked && !wasMainStore && !isMainStore.disabled) {
                    if (!confirm('Are you sure you want to set this as the main store? This will remove the main store status from any existing main store in this branch.')) {
                        e.preventDefault();
                        return;
                    }
                }
                
                if (!isMainStore.checked && wasMainStore) {
                    if (!confirm('Are you sure you want to remove the main store status? This branch will no longer have a designated main store.')) {
                        e.preventDefault();
                        return;
                    }
                }
            });
        </script>
    @endpush
@endsection