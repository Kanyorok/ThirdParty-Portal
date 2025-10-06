@extends('layouts.app')
@section('title', 'Product Rates')

@section('content')
    <div class="container my-3">
        <!-- Action Bar -->
        <div class="d-flex justify-content-between mb-2">
            <h5 class="mb-0 text-primary">
                <i class="fas fa-percentage me-2"></i>  Rates
            </h5>
            <a href="javascript:void(0)" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#addRateModal">
                <i class="fas fa-plus me-1"></i> New Rate
            </a>
        </div>
        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>There were some errors with your submission:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Card -->
        <div class="card shadow-sm rounded-3">
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Configure and manage financial rates for products imported from the Core Banking System (CBS).
                    These settings directly impact budget drivers and projections tied to each product.
                </p>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;font-size:13px">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Rate Type</th>
                            <th>Rate Value (%)</th>
                            <th>Source</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($driverRates as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item['Description']['Description'] ?? '-' }}</td>
                                <td>{{ $item['RateTypeName'] ?? '-' }}</td>
                                <td>{{ number_format($item['RateValue'], 2) }}</td>
                                <td>{{ $item['Source'] ?? '-' }}</td>
                                <td class="text-center">
                                    <a href="javascript:void(0);"
                                       class="btn btn-sm btn-outline-primary me-1 edit-btn"
                                       data-id="{{ $item['Id'] }}"
                                       data-product="{{ $item['Description']['Description'] ?? '-' }}"
                                       data-ratetype="{{ $item['RateTypeName'] ?? '-' }}"
                                       data-ratevalue="{{ $item['RateValue'] }}"
                                       data-source="{{ $item['Source'] ?? '-' }}"
                                       title="Edit"
                                       data-bs-toggle="modal"
                                       data-bs-target="#editRateModal-{{$item['Id']}}">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{ $item['Description']['Description']??'-' }}"
                                            data-route="{{ route('yieldexpenserate.destroy', $item['Id']) }}"
                                            title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-0">
                                    <div class="text-center p-4 bg-light border rounded-3">
                                        <p class="mb-2 text-muted">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No product rates defined yet.
                                        </p>
                                        <button class="btn btn-info px-3" data-bs-toggle="modal"
                                                data-bs-target="#addRateModal">
                                            <i class="fas fa-plus-circle me-1"></i> Add New Rate
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Rate Modal -->
    <div class="modal fade" id="addRateModal" tabindex="-1" aria-labelledby="addRateLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title text-primary" id="addRateLabel">
                        <i class="fas fa-plus-circle me-2"></i> Add Product Rate
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('yieldexpenserate.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Product -->
                            <div class="col-md-6">
                                <label class="form-label">Product</label>
                                <select class="form-select" name="ProductTypeID" required>
                                    <option value="">-- Select Product --</option>
                                    @foreach($productTypes as $product)
                                        <option value="{{ $product->Id }}">{{ $product->Name }}</option>
                                    @endforeach
                                </select>
                                @error('ProductTypeID') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <!-- Rate Type -->
                            <div class="col-md-6">
                                <label class="form-label">Rate Type</label>
                                <select class="form-select" name="RateTypeID" required>
                                    <option value="">-- Select Rate Type --</option>
                                    @foreach($rateTypes as $rate)
                                        <option value="{{ $rate->Id }}">{{ $rate->RateTypeName }}</option>
                                    @endforeach
                                </select>
                                @error('RateTypeID') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <!-- Rate Value -->
                            <div class="col-md-6">
                                <label class="form-label">Rate Value (%)</label>
                                <input type="number" step="0.01" class="form-control" name="RateValue"
                                       placeholder="e.g. 10.50" required>
                                @error('RateValue') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <!-- Source -->
                            <div class="col-md-6">
                                <label class="form-label">Source</label>
                                <input type="text" class="form-control" name="Source" placeholder="e.g. CBS, Manual"
                                       required>
                                @error('Source') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerHTML='<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Saving...'; this.form.submit(); }">
                            <i class="fas fa-save me-1"></i> Save Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @foreach($driverRates as $item)
        <!-- Edit Rate Modal -->
        <div class="modal fade" id="editRateModal-{{ $item['Id'] }}" tabindex="-1" aria-labelledby="editRateLabel-{{ $item['Id'] }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-3 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title text-primary" id="editRateLabel-{{ $item['Id'] }}">
                            <i class="fas fa-edit me-2"></i> Edit Product Rate
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form method="POST" action="{{ route('yieldexpenserate.update', $item['Id']) }}">
                        @csrf
                        @method('PATCH')

                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Product (readonly text) -->
                                <div class="col-md-6">
                                    <label class="form-label">Product</label>
                                    <input type="text" class="form-control" value="{{ $item['Description']['Description'] }}" readonly>
                                    <!-- Hidden field to still hold ProductTypeID -->
                                    <input type="hidden" name="ProductTypeID" value="{{ $item['Description']['Id'] }}">
                                </div>

                                <!-- Rate Type -->
                                <div class="col-md-6">
                                    <label class="form-label">Rate Type</label>
                                    <select class="form-select" name="RateTypeID" required>
                                        <option value="">-- Select Rate Type --</option>
                                        @foreach($rateTypes as $rate)
                                            <option value="{{ $rate->Id }}" {{ $rate->RateTypeName === $item['RateTypeName'] ? 'selected' : '' }}>
                                                {{ $rate->RateTypeName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Rate Value -->
                                <div class="col-md-6">
                                    <label class="form-label">Rate Value (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="RateValue"
                                           value="{{ $item['RateValue'] }}" required>
                                </div>

                                <!-- Source -->
                                <div class="col-md-6">
                                    <label class="form-label">Source</label>
                                    <input type="text" class="form-control" name="Source"
                                           value="{{ $item['Source'] }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-success"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerHTML='<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Updating...'; this.form.submit(); }">
                                <i class="fas fa-save me-1"></i> Update Rate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach



    @include('components.modals.delete-confirm')

@endsection
