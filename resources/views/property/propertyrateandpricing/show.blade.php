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

            <hr>

            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card bg-light border-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Amount</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h3 class="text-success mb-0">
                                        {{ number_format($pricing->Rent + $pricing->ParkingFee + $pricing->ServiceCharge + $pricing->OtherCharges + $pricing->DepositAmount, 2) }}
                                    </h3>
                                    <small class="text-muted">(Rent + Parking + Service + Other + Deposit)</small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <p class="mb-1"><strong>Monthly Charges:</strong> {{ number_format($pricing->Rent + $pricing->ParkingFee + $pricing->ServiceCharge + $pricing->OtherCharges, 2) }}</p>
                                    <p class="mb-0"><strong>Deposit:</strong> {{ number_format($pricing->DepositAmount, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
