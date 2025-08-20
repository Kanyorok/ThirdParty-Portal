@extends('layouts.app')
@section('title', 'Products Rates')
@section('content')
    <style>
        .modal {
            z-index: 1055; /* Ensure modal is above backdrop */
        }

        .modal-backdrop {
            z-index: 1040; /* Ensure backdrop is below modal */
        }
    </style>
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some errors with your submission:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="mb-0 d-flex justify-content-between">
        <a href="javascript:void(0)" class="btn btn-success btn-sm" data-bs-toggle="modal"
           data-bs-target="#addRateModal">
            + New Rate
        </a>
    </div>
    <div class="card mt-2">

{{--        <div class="card-header bg-secondary text-white">📈Product Rates</div>--}}
        <p class="text-muted">
            This form allows you to configure and manage financial rates for products imported from the Core Banking
            System (CBS).
            Specify the rate type (e.g., interest, tax, discount), applicable product and source of the rate. These
            settings will directly impact budget drivers and projections tied to each product.
        </p>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Rate Type</th>
                    <th>Rate Value</th>
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($driverRates as $item)
                    <tr>
                        <td>{{ $loop->iteration }}.</td>
                        <td>{{ $item->productType->Description }}</td>
                        <td>{{ $item->rateType->RateTypeName }}</td>
                        <td>{{ $item->RateValue }}</td>
                        <td>{{ $item->Source }}</td>
                        <td>
                            <a href="{{route('yieldexpenserate.edit', $item->Id)}}"
                               class="btn btn-sm btn-secondary">✏️</a>
                            <button type="button"
                                    class="btn btn-sm btn-danger custom-delete-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#customDeleteConfirmModal"
                                    data-name="{{ $item->productType->Name }}"
                                    data-route="{{route('yieldexpenserate.destroy', $item->Id)}}">
                                🗑️
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div class="modal fade" id="addRateModal" tabindex="-1" aria-labelledby="addSectionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSectionLabel">Add Driver Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('yieldexpenserate.store') }}">
                        @csrf
                        <div class="row">
                            <!-- Product -->
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Product</label>
                                <select class="form-select" name="ProductTypeID" required>
                                    <option value="">-- Select Product --</option>
                                    @foreach($productTypes as $product)
                                        <option value="{{ $product->Id }}">{{ $product->Name }}</option>
                                    @endforeach
                                </select>
                                @error('ProductTypeID')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Rate Type -->
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Rate Type</label>
                                <select class="form-select" name="RateTypeID" required>
                                    <option value="">-- Select Rate Type --</option>
                                    @foreach($rateTypes as $rate)
                                        <option value="{{ $rate->Id }}">{{ $rate->RateTypeName }}</option>
                                    @endforeach
                                </select>
                                @error('RateTypeID')
                                <div class="text-danger">{{ $message }}</div>
                                @endif
                            </div>

                            <!-- Rate Value -->
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Rate Value (%)</label>
                                <input type="number" step="0.01" class="form-control" name="RateValue"
                                       placeholder="e.g. 10.5" required>
                                @error('RateValue')
                                <div class="text-danger">{{ $message }}</div>
                                @endif
                            </div>

                            <!-- Source -->
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Source</label>
                                <input type="text" class="form-control" name="Source" placeholder="e.g. CBS, Manual"
                                       required>
                                @error('Source')
                                <div class="text-danger">{{ $message }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                            </button>
                            <button type="submit" class="btn btn-success"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                                💾 Save Driver Rate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('components.modals.delete-confirm')

    <script>
        // Fallback to manually trigger modal if data-bs-toggle fails
        document.addEventListener('DOMContentLoaded', function () {
            const newRateButton = document.querySelector('a[data-bs-target="#addRateModal"]');
            if (newRateButton) {
                newRateButton.addEventListener('click', function () {
                    const modal = new bootstrap.Modal(document.getElementById('addRateModal'));
                    modal.show();
                });
            }
        });
    </script>

@endsection
