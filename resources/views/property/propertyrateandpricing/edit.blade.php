@extends('layouts.app')

@section('title', 'Edit Property Rate & Pricing')

@section('content')

<div class="container py-4" style="max-width: 1100px;">

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

    <form action="{{ route('propertyrateandpricing.update', $pricing->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0">
            <div class="card-body">

                {{-- SECTION: Property Information --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                    Property Information
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Property</label>
                        <div class="form-control bg-light fw-semibold">
                            {{ $pricing->property->PropertyName ?? 'N/A' }}
                        </div>
                        <input type="hidden" name="PropertyId" value="{{ $pricing->PropertyId }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Block</label>
                        <div class="form-control bg-light fw-semibold">
                            {{ $pricing->block->BlockName ?? 'N/A' }}
                        </div>
                        <input type="hidden" name="BlockId" value="{{ $pricing->BlockId }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Floor</label>
                        <div class="form-control bg-light fw-semibold">
                            {{ $pricing->floor->FloorLabel ?? 'N/A' }}
                        </div>
                        <input type="hidden" name="FloorId" value="{{ $pricing->FloorId }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <div class="form-control bg-light fw-semibold">
                            {{ $pricing->unit->UnitCode ?? 'N/A' }}
                        </div>
                        <input type="hidden" name="UnitId" value="{{ $pricing->UnitId }}">
                    </div>
                </div>

                {{-- SECTION: Currency & Tax --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                    Currency & Tax
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                        <select class="form-select" name="CurrencyId" required>
                            @foreach ($currencies as $cur)
                                <option value="{{ $cur->Id }}" {{ $pricing->CurrencyId == $cur->Id ? 'selected' : '' }}>
                                    {{ $cur->Code }} ({{ $cur->Symbol }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tax Rule <span class="text-danger">*</span></label>
                        <select class="form-select" name="TaxId" required>
                            @foreach ($Taxes as $tax)
                                <option value="{{ $tax->Id }}" {{ $pricing->TaxId == $tax->Id ? 'selected' : '' }}>
                                    {{ $tax->taxType->TaxTypeName }} ({{ $tax->Rate }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SECTION: Pricing --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3 border-bottom pb-2">
                    Pricing Details
                </h6>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Rent <span class="text-danger">*</span></label>
                        <input type="number" name="Rent" id="Rent" class="form-control"
                               value="{{ $pricing->Rent }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Service Charge<span class="text-danger">*</span></label>
                        <input type="number" name="ServiceCharge" id="ServiceCharge"
                               class="form-control" value="{{ $pricing->ServiceCharge }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Parking Fee<span class="text-danger">*</span></label>
                        <input type="number" name="ParkingFee" id="ParkingFee"
                               class="form-control" value="{{ $pricing->ParkingFee }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Other Charges<span class="text-danger">*</span></label>
                        <input type="number" name="OtherCharges" id="OtherCharges"
                               class="form-control" value="{{ $pricing->OtherCharges }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Deposit Amount<span class="text-danger">*</span></label>
                        <input type="number" name="DepositAmount" id="DepositAmount"
                               class="form-control" value="{{ $pricing->DepositAmount }}" required>
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
                                <small class="text-muted">(Monthly Charges + Deposit)</small>
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

                {{-- ACTIONS (BOTTOM) --}}
                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <a href="{{ route('propertyrateandpricing.index') }}"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>

                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i>
                        Update Pricing
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- TOTAL CALCULATION SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const rent = document.getElementById('Rent');
    const parking = document.getElementById('ParkingFee');
    const service = document.getElementById('ServiceCharge');
    const other = document.getElementById('OtherCharges');
    const deposit = document.getElementById('DepositAmount');

    function calculateTotal() {
        const r = parseFloat(rent.value) || 0;
        const p = parseFloat(parking.value) || 0;
        const s = parseFloat(service.value) || 0;
        const o = parseFloat(other.value) || 0;
        const d = parseFloat(deposit.value) || 0;

        const monthly = r + p + s + o;
        const total = monthly + d;

        totalAmount.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2 });
        monthlyCharges.textContent = monthly.toLocaleString('en-US', { minimumFractionDigits: 2 });
        displayDeposit.textContent = d.toLocaleString('en-US', { minimumFractionDigits: 2 });
    }

    [rent, parking, service, other, deposit].forEach(i =>
        i.addEventListener('input', calculateTotal)
    );

    calculateTotal();
});
</script>

@endsection
