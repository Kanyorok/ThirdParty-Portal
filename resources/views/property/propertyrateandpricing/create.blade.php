@extends('layouts.app')
@section('title', 'Property Rate & Pricing Setup')

@section('content')

<div class="container py-4">

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('propertyrateandpricing.store') }}" method="POST">
        @csrf

        <div class="card shadow-sm border-0">

            <div class="card-body">

                {{-- SECTION: Property Structure --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                    Property Structure
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Property <span class="text-danger">*</span></label>
                        <select name="PropertyId" id="property-select" class="form-select" required>
                            <option value="">-- Select Property --</option>
                            @foreach ($property as $item)
                                <option value="{{ $item->Id }}">{{ $item->PropertyName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Block <span class="text-danger">*</span></label>
                        <select name="BlockId" id="block-select" class="form-select" required>
                            <option value="">-- Select Block --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Floor <span class="text-danger">*</span></label>
                        <select name="FloorId" id="floor-select" class="form-select" required>
                            <option value="">-- Select Floor --</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select name="UnitId" id="unit-select" class="form-select" required>
                            <option value="">-- Select Unit --</option>
                        </select>
                    </div>
                </div>

                {{-- SECTION: Monthly Charges --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                    Monthly Charges
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Rent Amount <span class="text-danger">*</span></label>
                        <input type="number" name="Rent" class="form-control" placeholder="e.g. 50,000" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Parking Fee <span class="text-danger">*</span></label>
                        <input type="number" name="ParkingFee" class="form-control" placeholder="e.g. 3,000" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Service Charge <span class="text-danger">*</span></label>
                        <input type="number" name="ServiceCharge" class="form-control" placeholder="e.g. 2,000" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Other Charges <span class="text-danger">*</span></label>
                        <input type="number" name="OtherCharges" class="form-control" placeholder="e.g. 1,000" required>
                    </div>
                </div>

                {{-- SECTION: Deposit & Tax --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                    Deposit & Tax
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Deposit Amount <span class="text-danger">*</span></label>
                        <input type="number" name="DepositAmount" id="DepositAmount" class="form-control" placeholder="e.g. 60,000" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                        <select class="form-select" name="CurrencyId" required>
                            <option value="">-- Select Currency --</option>
                            @foreach($currencies as $cur)
                                <option value="{{ $cur->Id }}">{{ $cur->Code }} ({{ $cur->Symbol }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tax Rule <span class="text-danger">*</span></label>
                        <select class="form-select" name="TaxId" required>
                            <option value="">-- Select Tax --</option>
                            @foreach($Taxes as $tax)
                                <option value="{{ $tax->Id }}">
                                    {{ $tax->taxType->TaxTypeName }} ({{ $tax->Rate }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SECTION: Total Summary --}}
                <div class="card bg-light border-success mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <small class="text-muted text-uppercase">Total Payable</small>
                                <h3 class="fw-bold text-success mb-0">
                                    <span id="totalAmount">0.00</span>
                                </h3>
                                <small class="text-muted">
                                    (Monthly Charges + Deposit)
                                </small>
                            </div>

                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <p class="mb-1">
                                    <strong>Monthly Charges:</strong>
                                    <span id="monthlyCharges">0.00</span>
                                </p>
                                <p class="mb-0">
                                    <strong>Deposit:</strong>
                                    <span id="displayDeposit">0.00</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-outline-secondary">Back</a>
                    <button type="submit"
                            class="btn btn-success px-4"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Pricing
                    </button>
                </div>

            </div>
        </div>

    </form>
</div>

@endsection
