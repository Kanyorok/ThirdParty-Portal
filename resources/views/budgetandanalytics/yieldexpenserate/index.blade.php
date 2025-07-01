@extends('layouts.app')
@section('title', 'Products Rates')
@section('content')
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
    <div class="card mt-4">

        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('yieldexpenserate.create') }}" class="btn btn-success btn-sm" data-bs-toggle="modal"
               data-bs-target="#addRateModal">
                + New Rate
            </a>
        </div>
        <div class="card-header bg-secondary text-white">📈Product Rates</div>
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
                    {{-- <th>Period</th> --}}
                    <th>Product</th>
                    <th>Rate Type</th>
                    <th>Rate Value</th>
                    {{-- <th>Effective Date</th> --}}
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($driverRates as $item)
                    <tr>
                        <td>{{ $loop->iteration }}.</td>
                        {{-- <td>{{ $item->periodType->PeriodType }}</td> --}}
                        <td>{{ $item->productType->Name }}</td>
                        <td>{{ $item->rateType->RateTypeName }}</td>
                        <td>{{ $item->RateValue }}</td>
                        {{-- <td>{{ \Carbon\Carbon::parse($item->EffectiveDate)->format('d-m-y') }}</td> --}}
                        <td>{{ $item->Source }}</td>
                        <td>
                            <a href="{{route('yieldexpenserate.edit', $item->Id)}}"
                               class="btn btn-sm btn-secondary">✏️</a>
                            <form method="POST" action="{{ route('yieldexpenserate.destroy', $item->Id) }}"
                                  class="delete-form d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger delete-btn">🗑</button>
                            </form>
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
                    <h5 class="modal-title" id="addItemModalLabel">Add Driver Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('yieldexpenserate.store') }}">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <!-- Budget Period -->
                            {{-- <div class="mb-4 col-md-6">
                                <label class="form-label">Budget Period</label>
                                <select class="form-select" name="PeriodTypeID" required>
                                    <option value="">-- Select Period --</option>
                                    @foreach($periodTypes as $period)
                                        <option value="{{ $period->Id }}">{{ $period->PeriodType }}</option>
                                    @endforeach
                                </select>
                                @error('PeriodTypeID')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div> --}}

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
                                @enderror
                            </div>

                            <!-- Rate Value -->
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Rate Value (%)</label>
                                <input type="number" step="0.01" class="form-control" name="RateValue"
                                       placeholder="e.g. 10.5" required>
                                @error('RateValue')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Effective Date -->
                            {{-- <div class="mb-4 col-md-6">
                                <label class="form-label">Effective Date</label>
                                <input type="date" class="form-control" name="EffectiveDate" required>
                                @error('EffectiveDate')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div> --}}

                            <!-- Source -->
                            <div class="mb-4 col-md-6">
                                <label class="form-label">Source</label>
                                <input type="text" class="form-control" name="Source" placeholder="e.g. CBS, Manual"
                                       required>
                                @error('Source')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
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
@endsection
