@extends('layouts.app')
@section('title', 'Lease Schedule Generator')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the errors below.</strong>
    </div>
@endif

@if (session('error'))
    <script>alert("{{ session('error') }}");</script>
@endif

<div class="container mt-4">
    <form action="{{ route('schedulelease.store') }}" method="POST">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Generate Billing Periods</div>

            <div class="card-body">

                <div class="row g-3 mb-3">
                    <!-- Lease Dropdown -->
                    <div class="col-md-6">
                        <label class="form-label">Select Lease Number <span class="text-danger">*</span></label>
                        <select id="lease-select" name="LeaseId" class="form-select @error('LeaseId') is-invalid @enderror" required>
                            <option value="">-- Select Lease --</option>

                            @foreach ($newleases as $lease)
                                <option value="{{ $lease->Id }}"
                                    {{ old('LeaseId') == $lease->Id ? 'selected' : '' }}
                                    data-leasenumber="{{ $lease->LeaseNumber }}"
                                    data-tenant-id="{{ $lease->Tenant }}"
                                    data-tenant-name="{{ $lease->tenant->thirdParty->ThirdPartyName ?? 'N/A' }}"
                                    data-property-id="{{ $lease->PropertyID }}"
                                    data-property-name="{{ $lease->property->PropertyName ?? 'N/A' }}"
                                    data-frequency-id="{{ $lease->PaymentFrequency }}"
                                    data-frequency-name="{{ $lease->code->Description ?? 'N/A' }}"
                                    data-baserent="{{ $lease->MonthlyRent ?? 0 }}"
                                    data-servicecharge="{{ $lease->ServiceCharge ?? 0 }}"
                                    data-parkingfee="{{ $lease->ParkingFee ?? 0 }}"
                                    data-othercharges="{{ $lease->OtherCharges ?? 0 }}"
                                >
                                    {{ $lease->LeaseNumber }}
                                </option>
                            @endforeach
                        </select>
                        @error('LeaseId')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Lease Number Display -->
                    <div class="col-md-6">
                        <label class="form-label">Lease Number<span class="text-danger">*</span></label>
                        <input type="text" id="lease-display" class="form-control" readonly>
                    </div>

                    <!-- Payment Frequency -->
                    <div class="col-md-6">
                        <label class="form-label">Payment Frequency<span class="text-danger">*</span></label>
                        <input type="text" id="frequency-display" class="form-control" readonly>
                        <input type="hidden" name="PaymentFrequency" id="frequency-id">
                        @error('PaymentFrequency')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tenant -->
                    <div class="col-md-6">
                        <label class="form-label">Tenant<span class="text-danger">*</span></label>
                        <input type="text" id="tenant-display" class="form-control" readonly>
                        <input type="hidden" name="TenantId" id="tenant-id">
                        @error('TenantId')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Property -->
                    <div class="col-md-6">
                        <label class="form-label">Property<span class="text-danger">*</span></label>
                        <input type="text" id="property-display" class="form-control" readonly>
                        <input type="hidden" name="PropertyId" id="property-id">
                        @error('PropertyId')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Dates & Charges -->
                <div class="row g-3 mb-3">

                    <div class="col-md-4">
                        <label class="form-label">Start Date<span class="text-danger">*</span></label>
                        <input type="date" name="StartDate"
                               value="{{ old('StartDate', '2025-05-01') }}"
                               class="form-control @error('StartDate') is-invalid @enderror" required>
                        @error('StartDate')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Date<span class="text-danger">*</span></label>
                        <input type="date" name="EndDate"
                               value="{{ old('EndDate', '2026-04-30') }}"
                               class="form-control @error('EndDate') is-invalid @enderror" required>
                        @error('EndDate')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Base Rent<span class="text-danger">*</span></label>
                        <input type="number" name="BaseRent"
                               class="form-control @error('BaseRent') is-invalid @enderror"
                               id="baserent" value="{{ old('BaseRent', 25000) }}" required>
                        @error('BaseRent')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Service Charge<span class="text-danger">*</span></label>
                        <input type="number" name="ServiceCharge"
                               class="form-control @error('ServiceCharge') is-invalid @enderror"
                               id="servicecharge" value="{{ old('ServiceCharge', 15000) }}" required>
                        @error('ServiceCharge')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Parking Fee<span class="text-danger">*</span></label>
                        <input type="number" name="ParkingFee"
                               class="form-control @error('ParkingFee') is-invalid @enderror"
                               id="parkingfee" value="{{ old('ParkingFee', 2000) }}" required>
                        @error('ParkingFee')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Other Charges<span class="text-danger">*</span></label>
                        <input type="number" name="OtherCharges"
                               class="form-control @error('OtherCharges') is-invalid @enderror"
                               id="othercharges" value="{{ old('OtherCharges', 0) }}" required>
                        @error('OtherCharges')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Total -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input type="number" id="total-amount" class="form-control" readonly>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="text-end">
                    <a href="{{ route('schedulelease.index') }}" class="btn btn-secondary">Cancel</a>

                    <button type="submit" class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        Generate Schedule
                    </button>
                </div>

            </div>
        </div>

    </form>
</div>

<!-- JS -->
<script>
    // Auto-fill lease details
    document.getElementById('lease-select').addEventListener('change', function() {

        const selected = this.options[this.selectedIndex];

        document.getElementById('lease-display').value = selected.dataset.leasenumber || '';
        document.getElementById('tenant-id').value = selected.dataset.tenantId || '';
        document.getElementById('tenant-display').value = selected.dataset.tenantName || '';
        document.getElementById('property-id').value = selected.dataset.propertyId || '';
        document.getElementById('property-display').value = selected.dataset.propertyName || '';
        document.getElementById('frequency-id').value = selected.dataset.frequencyId || '';
        document.getElementById('frequency-display').value = selected.dataset.frequencyName || '';

        document.getElementById('baserent').value = selected.dataset.baserent || 0;
        document.getElementById('servicecharge').value = selected.dataset.servicecharge || 0;
        document.getElementById('parkingfee').value = selected.dataset.parkingfee || 0;
        document.getElementById('othercharges').value = selected.dataset.othercharges || 0;

        calculateTotal();
    });

    // Total calculation
    function calculateTotal() {
        const baseRent = parseFloat(baserent.value) || 0;
        const serviceCharge = parseFloat(servicecharge.value) || 0;
        const parkingFee = parseFloat(parkingfee.value) || 0;
        const otherCharges = parseFloat(othercharges.value) || 0;

        total_amount = baseRent + serviceCharge + parkingFee + otherCharges;
        document.getElementById('total-amount').value = total_amount;
    }

    document.querySelectorAll("#baserent, #servicecharge, #parkingfee, #othercharges")
        .forEach(el => el.addEventListener("input", calculateTotal));

    window.addEventListener('DOMContentLoaded', calculateTotal);
</script>

@endsection
