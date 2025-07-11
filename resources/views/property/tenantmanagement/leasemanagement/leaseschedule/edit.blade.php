@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Lease Schedule')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h1>Edit Lease Schedule</h1>
    <form action="{{ route('schedulelease.update', $leaseschedules->Id) }}" method="POST">
        @csrf
        @method('PUT')
                <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                        <label class="form-label">Select Lease Number</label>
                            <select id="lease-select" name="LeaseId" class="form-select" required>
                                <option value="">-- Select Lease --</option>
                                @foreach ($newleases as $lease)
                                    <option 
                                        value="{{ $lease->Id }}"
                                        data-leasenumber="{{ $lease->LeaseNumber }}"
                                        data-tenant-id="{{ $lease->Tenant }}"
                                        data-tenant-name="{{ $lease->tenant->TenantName ?? 'N/A' }}"
                                        data-property-name="{{ $lease->property->PropertyName ?? 'N/A' }}"
                                        data-frequency-id="{{ $lease->PaymentFrequency }}"
                                        data-frequency-name="{{ $lease->code->Description ?? 'N/A' }}"
                                        @if(old('LeaseId', $leaseschedules->LeaseNumber) == $lease->Id) selected @endif
                                    >
                                        {{ $lease->LeaseNumber }}
                                    </option>
                                @endforeach
                            </select>
                    </div>
                    <!-- Auto-filled Tenant -->
                    <div class="col-md-6">
                        <label class="form-label">Tenant</label>
                        <input type="text" id="tenant-display" class="form-control" readonly value="{{ old('TenantName', $leaseschedules->lease->tenant->TenantName ?? '') }}">
                        <input type="hidden" name="TenantId" id="tenant-id" value="{{ old('TenantId', $leaseschedules->lease->tenant->Id ?? '') }}">
                    </div>

            <!-- Auto-filled Property -->
            <div class="col-md-6">
                <label class="form-label">Property</label>
                <input type="text" id="property-display" class="form-control" readonly
                       value="{{ old('PropertyName', $leaseschedules->lease->property->PropertyName ?? '') }}">
                <input type="hidden" name="PropertyId" id="property-id"
                       value="{{ old('PropertyId', $leaseschedules->lease->property->Id ?? '') }}">
            </div>

            <!-- Auto-filled Payment Frequency -->
            <div class="col-md-6">
                <label class="form-label">Payment Frequency</label>
                <input type="text" id="frequency-display" class="form-control" readonly
                       value="{{ old('FrequencyName', $leaseschedules->lease->code->Description ?? '') }}">
                <input type="hidden" name="PaymentFrequency" id="frequency-id"
                       value="{{ old('PaymentFrequency', $leaseschedules->lease->PaymentFrequency ?? '') }}">
            </div>

            <!-- Financial Parameters -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="StartDate"
                           value="{{ old('StartDate', $leaseschedules->StartDate ? Carbon::parse($leaseschedules->StartDate)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="EndDate"
                           value="{{ old('EndDate', $leaseschedules->EndDate ? Carbon::parse($leaseschedules->EndDate)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Base Rent per Period (KES)</label>
                    <input type="number" class="form-control" name="BaseRent"
                           value="{{ old('BaseRent', $leaseschedules->BaseRent) }}">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Service Charge (KES)</label>
                    <input type="number" class="form-control" name="ServiceCharge"
                           value="{{ old('ServiceCharge', $leaseschedules->ServiceCharge) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parking Fee (KES)</label>
                    <input type="number" class="form-control" name="ParkingFee"
                           value="{{ old('ParkingFee', $leaseschedules->ParkingFee) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Other Charges (KES)</label>
                    <input type="number" class="form-control" name="OtherCharges"
                           value="{{ old('OtherCharges', $leaseschedules->OtherCharges) }}">
                </div>
            </div>
            <button type="submit" class="btn btn-success">Update Lease Schedule</button>
            <a href="{{ route('schedulelease.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
    </div>
    </div>
    </div>
    <script>
        // On lease select change, update all related fields
        document.getElementById('lease-select').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            document.getElementById('tenant-id').value = selected.getAttribute('data-tenant-id');
            document.getElementById('tenant-display').value = selected.getAttribute('data-tenant-name');
            document.getElementById('property-id').value = selected.getAttribute('data-property-id');
            document.getElementById('property-display').value = selected.getAttribute('data-property-name');
            document.getElementById('frequency-id').value = selected.getAttribute('data-frequency-id');
            document.getElementById('frequency-display').value = selected.getAttribute('data-frequency-name');
        });

        // On page load, trigger change event to auto-fill fields if a lease is already selected
        window.addEventListener('DOMContentLoaded', function () {
            var leaseSelect = document.getElementById('lease-select');
            if (leaseSelect.value) {
                leaseSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection
