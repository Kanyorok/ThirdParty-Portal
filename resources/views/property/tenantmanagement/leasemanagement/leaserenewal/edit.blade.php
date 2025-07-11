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
            <!-- Lease Number (Read-only) -->
            <div class="col-md-6">
                <label class="form-label">Lease Number</label>
                <input type="text" class="form-control" value="{{ $leaserenewal->lease->LeaseNumber ?? 'N/A' }}" readonly>
                <input type="hidden" name="LeaseId" value="{{ $leaserenewal->LeaseNumber }}">
            </div>

            <!-- Auto-filled Tenant -->
            <div class="col-md-6">
                <label class="form-label">Tenant</label>
                <input type="text" class="form-control" readonly value="{{ old('TenantName', $leaserenewal->lease->tenant->TenantName ?? '') }}">
                <input type="hidden" name="TenantId" value="{{ old('TenantId', $leaserenewal->lease->tenant->Id ?? '') }}">
            </div>

            <!-- Auto-filled Property -->
            <div class="col-md-6">
                <label class="form-label">Property</label>
                <input type="text" class="form-control" readonly value="{{ old('PropertyName', $leaserenewal->lease->property->PropertyName ?? '') }}">
                <input type="hidden" name="PropertyId" value="{{ old('PropertyId', $leaserenewal->lease->property->Id ?? '') }}">
            </div>

            <!-- Auto-filled Payment Frequency -->
            <div class="col-md-6">
                <label class="form-label">Payment Frequency</label>
                <input type="text" class="form-control" readonly value="{{ old('FrequencyName', $leaserenewal->paymentFrequency->Description ?? '') }}">
                <input type="hidden" name="PaymentFrequency" value="{{ old('PaymentFrequency', $leaserenewal->PaymentFrequency ?? '') }}">
            </div>
        </div>

        <!-- Financial Parameters -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">End Date of Current Lease</label>
                <input type="date" class="form-control" name="EndDateCurrentLease"
                    value="{{ old('EndDateCurrentLease', $leaserenewal->EndDateCurrentLease ? \Carbon\Carbon::parse($leaserenewal->EndDateCurrentLease)->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">New Start Date</label>
                <input type="date" class="form-control" name="NewStartDate"
                    value="{{ old('NewStartDate', $leaserenewal->NewStartDate ? \Carbon\Carbon::parse($leaserenewal->NewStartDate)->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">New End Date</label>
                <input type="date" class="form-control" name="NewEndDate"
                    value="{{ old('NewEndDate', $leaserenewal->NewEndDate ? \Carbon\Carbon::parse($leaserenewal->NewEndDate)->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">New Monthly Rent</label>
                <input type="number" class="form-control" name="NewMonthlyRent"
                    value="{{ old('NewMonthlyRent', $leaserenewal->NewMonthlyRent) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Service Charge</label>
                <input type="number" class="form-control" name="ServiceCharge" value="{{ old('ServiceCharge', $leaserenewal->ServiceCharge) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Parking Fee</label>
                <input type="number" class="form-control" name="ParkingFee" value="{{ old('ParkingFee', $leaserenewal->ParkingFee) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Other Charges</label>
                <input type="number" class="form-control" name="OtherCharges" value="{{ old('OtherCharges', $leaserenewal->OtherCharges) }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks or Changes</label>
            <textarea class="form-control" rows="2" placeholder="E.g. rent increased by KES 2,500" name="Remarks">{{ old('Remarks', $leaserenewal->Remarks) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Lease Renewal</button>
        <a href="{{ route('renewlease.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
@endsection
