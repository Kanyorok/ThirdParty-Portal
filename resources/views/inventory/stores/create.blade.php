@extends('layouts.app')
@section('title', 'Create New Store')
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
                <h4 class="mb-0">Create Store</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('stores.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="StoreName" class="form-label">Store Name <span class="text-danger">*</span></label>
                            <input type="text" name="StoreName" class="form-control" id="StoreName"
                                   value="{{ old('StoreName') }}" required>
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
                            <div class="form-check mt-3">
                                <input type="checkbox" name="IsMainStore" id="IsMainStore" 
                                       value="1" class="form-check-input" 
                                       {{ old('IsMainStore') ? 'checked' : '' }}
                                       {{ $mainStoreExists ? 'disabled' : '' }}>
                                <label class="form-check-label fw-bold" for="IsMainStore">
                                    Set as Main Store
                                </label>
                            </div>
                            @if($mainStoreExists)
                                <div class="text-warning small mt-1">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    A main store already exists for this branch. Only one main store is allowed.
                                </div>
                            @else
                                <div class="text-muted small mt-1">
                                    This will be the primary store for this branch.
                                </div>
                            @endif
                            @error('IsMainStore')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status field automatically set to active (hidden) -->
                        <input type="hidden" name="Status" value="1">
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Create Store</button>
                        <a href="{{ route('stores.index') }}" class="btn btn-danger px-4 ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Optional: Add confirmation if setting as main store
            document.querySelector('form').addEventListener('submit', function(e) {
                const isMainStore = document.getElementById('IsMainStore').checked;
                if (isMainStore && !document.getElementById('IsMainStore').disabled) {
                    if (!confirm('Are you sure you want to set this as the main store? This cannot be changed easily later.')) {
                        e.preventDefault();
                    }
                }
            });

            // Enable/disable main store checkbox based on existing main store
            @if($mainStoreExists)
                document.getElementById('IsMainStore').disabled = true;
            @endif
        </script>
    @endpush
@endsection