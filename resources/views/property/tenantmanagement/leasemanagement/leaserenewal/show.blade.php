@extends('layouts.app')
@section('title', 'Lease Renewal Details')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <h3 class="mb-4">Lease Renewal Details</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form>
                @csrf

                <div class="mb-3">
                    <label for="lease_number" class="form-label">Lease Number</label>
                    <input type="text" id="lease_number" class="form-control" value="{{ $leaserenewal->lease->LeaseNumber ?? '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="tenant_name" class="form-label">Tenant Name</label>
                    <input type="text" id="tenant_name" class="form-control" value="{{ $leaserenewal->lease->tenant->TenantName ?? '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="property" class="form-label">Property Leased</label>
                    <input type="text" id="property" class="form-control" value="{{ $leaserenewal->lease->property->PropertyName ?? '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="payment_frequency" class="form-label">Payment Frequency</label>
                    <input type="text" id="payment_frequency" class="form-control" value="{{ $leaserenewal->paymentFrequency->Description ?? '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="end_date_current" class="form-label">End Date of Current Lease</label>
                    <input type="text" id="end_date_current" class="form-control" value="{{ $leaserenewal->EndDateCurrentLease ? \Carbon\Carbon::parse($leaserenewal->EndDateCurrentLease)->format('d m Y') : '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="new_start_date" class="form-label">New Start Date</label>
                    <input type="text" id="new_start_date" class="form-control" value="{{ $leaserenewal->NewStartDate ? \Carbon\Carbon::parse($leaserenewal->NewStartDate)->format('d/m/Y') : '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="new_end_date" class="form-label">New End Date</label>
                    <input type="text" id="new_end_date" class="form-control" value="{{ $leaserenewal->NewEndDate ? \Carbon\Carbon::parse($leaserenewal->NewEndDate)->format('d/m/Y') : '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="new_monthly_rent" class="form-label">New Monthly Rent</label>
                    <input type="text" id="new_monthly_rent" class="form-control" value="{{ number_format($leaserenewal->NewMonthlyRent, 2) ?? '-' }}" disabled>
                </div>
                <div class="mb-3">
                        <label class="form-label">Service Charge</label>
                        <input type="number" class="form-control" name="ServiceCharge" value="{{ old('ServiceCharge', $leaserenewal->ServiceCharge) }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parking Fee</label>
                        <input type="number" class="form-control" name="ParkingFee" value="{{ old('ParkingFee', $leaserenewal->ParkingFee) }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Other Charges</label>
                        <input type="number" class="form-control" name="OtherCharges" value="{{ old('OtherCharges', $leaserenewal->OtherCharges) }}" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea id="remarks" class="form-control" rows="3" disabled>{{ $leaserenewal->Remarks ?? '-' }}</textarea>
                </div>
            </form>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('renewlease.edit', $leaserenewal->Id) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('renewlease.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
