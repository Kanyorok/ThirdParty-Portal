@extends('layouts.app')

@section('title', 'View Property Rate & Pricing')

@section('content')
<div class="container mt-4" style="max-width: 900px;">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Property:</strong>
                    <p>{{ $pricing->property->PropertyName ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Block:</strong>
                    <p>{{ $pricing->block->BlockName ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Floor:</strong>
                    <p>{{ $pricing->floor->FloorLabel ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Unit:</strong>
                    <p>{{ $pricing->unit->UnitCode ?? 'N/A' }}</p>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Currency:</strong>
                    <p>{{ $pricing->currency->Code ?? 'N/A' }} ({{ $pricing->currency->Symbol ?? '' }})</p>
                </div>
                <div class="col-md-6">
                    <strong>Tax Rule:</strong>
                    <p>
                        {{ $pricing->tax->taxType->TaxTypeName ?? 'N/A' }}
                        — {{ $pricing->tax->Rate ?? '' }}%
                    </p>
                </div>
            </div>

            <hr>

            <h5 class="mt-3">Pricing</h5>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Rent:</strong>
                    <p>{{ number_format($pricing->Rent) }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Service Charge:</strong>
                    <p>{{ number_format($pricing->ServiceCharge) }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Parking Fee:</strong>
                    <p>{{ number_format($pricing->ParkingFee) }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Other Charges:</strong>
                    <p>{{ number_format($pricing->OtherCharges) }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Deposit Amount:</strong>
                    <p>{{ number_format($pricing->DepositAmount) }}</p>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('propertyrateandpricing.edit', $pricing->Id) }}" class="btn btn-primary">
                    Edit
                </a>
                <a href="{{ route('propertyrateandpricing.index') }}" class="btn btn-secondary">
                    Back
                </a>
            </div>

        </div>
    </div>

</div>
@endsection
