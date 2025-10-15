@extends('layouts.app')
@section('title', 'Edit Store')
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

                        <!-- Main Store Checkbox -->
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <!-- Hidden input to ensure value is always submitted -->
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
                                <input class="form-check-input" type="checkbox" name="Status" value="1"
                                       id="Status" {{ old('Status', $store->Status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="Status">Is Active</label>
                            </div>
                            @error('Status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Update Store</button>
                        <a href="{{ route('stores.index') }}" class="btn btn-danger px-4 ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Confirmation when changing main store status
            document.querySelector('form').addEventListener('submit', function(e) {
                const isMainStore = document.getElementById('IsMainStore');
                const wasMainStore = {{ $store->IsMainStore ? 'true' : 'false' }};
                
                // If changing from non-main to main store
                if (isMainStore.checked && !wasMainStore && !isMainStore.disabled) {
                    if (!confirm('Are you sure you want to set this as the main store? This will remove the main store status from any existing main store in this branch.')) {
                        e.preventDefault();
                    }
                }
                
                // If removing main store status
                if (!isMainStore.checked && wasMainStore) {
                    if (!confirm('Are you sure you want to remove the main store status? This branch will no longer have a designated main store.')) {
                        e.preventDefault();
                    }
                }
            });
        </script>
    @endpush
@endsection