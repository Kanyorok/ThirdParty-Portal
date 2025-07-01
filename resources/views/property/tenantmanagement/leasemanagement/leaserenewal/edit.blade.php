@extends('layouts.app')
@section('title', 'Lease Renewal')
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
    <h1>Edit Lease Renewal</h1>
    <form action="{{ route('renewlease.update', $leaserenewal->Id) }}" method="POST">
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
                            data-property-id="{{ $lease->PropertyID }}"
                            data-property-name="{{ $lease->property->PropertyName ?? 'N/A' }}"
                            data-frequency-id="{{ $lease->PaymentFrequency }}"
                            data-frequency-name="{{ $lease->code->Description ?? 'N/A' }}"
                            @if(old('LeaseId', $leaserenewal->LeaseNumber) == $lease->Id) selected @endif
                        >
                            {{ $lease->LeaseNumber }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Auto-filled Tenant -->
            <div class="col-md-6">
                <label class="form-label">Tenant</label>
                <input type="text" id="tenant-display" class="form-control" readonly value="{{ old('TenantName', $leaserenewal->lease->tenant->TenantName ?? '') }}">
                <input type="hidden" name="TenantId" id="tenant-id" value="{{ old('TenantId', $leaserenewal->lease->tenant->Id ?? '') }}">
            </div>

            <!-- Auto-filled Property -->
            <div class="col-md-6">
                <label class="form-label">Property</label>
                <input type="text" id="property-display" class="form-control" readonly value="{{ old('PropertyName', $leaserenewal->lease->property->PropertyName ?? '') }}">
                <input type="hidden" name="PropertyId" id="property-id" value="{{ old('PropertyId', $leaserenewal->lease->property->Id ?? '') }}">
            </div>

            <!-- Auto-filled Payment Frequency -->
            <div class="col-md-6">
                <label class="form-label">Payment Frequency</label>
                <input type="text" id="frequency-display" class="form-control" readonly value="{{ old('FrequencyName', $leaserenewal->lease->code->Description ?? '') }}">
                <input type="hidden" name="PaymentFrequency" id="frequency-id" value="{{ old('PaymentFrequency', $leaserenewal->lease->PaymentFrequency ?? '') }}">
            </div>
        </div>
        <!-- Financial Parameters -->
        <div class="col-md-3">
            <label class="form-label">End Date of Current Lease</label>
            <input type="date" class="form-control" name="EndDateCurrentLease" value="{{ old('EndDateCurrentLease', $leaserenewal->EndDateCurrentLease ? \Carbon\Carbon::parse($leaserenewal->EndDateCurrentLease)->format('Y-m-d') : '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">New Start Date</label>
            <input type="date" class="form-control" name="NewStartDate" value="{{ old('NewStartDate', $leaserenewal->NewStartDate ? \Carbon\Carbon::parse($leaserenewal->NewStartDate)->format('Y-m-d') : '') }}">
        </div>
        <!-- New Terms -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">New End Date</label>
                <input type="date" class="form-control" name="NewEndDate" value="{{ old('NewEndDate', $leaserenewal->NewEndDate ? \Carbon\Carbon::parse($leaserenewal->NewEndDate)->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">New Monthly Rent</label>
                <input type="number" class="form-control" name="NewMonthlyRent" value="{{ old('NewMonthlyRent', $leaserenewal->NewMonthlyRent) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Remarks or Changes</label>
                <textarea class="form-control" rows="2" placeholder="E.g. rent increased by KES 2,500" name="Remarks">{{ old('Remarks', $leaserenewal->Remarks) }}</textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Update Lease Renewal</button>
        <a href="{{ route('renewlease.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
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
    window.addEventListener('DOMContentLoaded', function() {
        var leaseSelect = document.getElementById('lease-select');
        if (leaseSelect.value) {
            leaseSelect.dispatchEvent(new Event('change'));
        }
    });
    </script>
@endsection