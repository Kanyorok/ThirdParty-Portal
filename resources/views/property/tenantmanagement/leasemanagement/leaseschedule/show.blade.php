@php 
    use Carbon\Carbon;

    $currency = $leaseschedule->lease->currency->SymbolNative ?? '';

    $baseRent = $leaseschedule->BaseRent ?? 0;
    $serviceCharge = $leaseschedule->ServiceCharge ?? 0;
    $parkingFee = $leaseschedule->ParkingFee ?? 0;
    $otherCharges = $leaseschedule->OtherCharges ?? 0;

    // Subtotal
    $subTotal = $baseRent + $serviceCharge + $parkingFee + $otherCharges;

    // VAT
    $taxRate = (($leaseschedule->lease->taxRule->Rate)/100) ?? '1';

    // Usually rent + service charge are taxable
    $taxableAmount = $baseRent + $serviceCharge;

    $taxAmount = $taxableAmount * $taxRate;

    // Final total
    $grandTotal = $subTotal + $taxAmount;
@endphp

@extends('layouts.app')

@section('title', 'Lease Schedule Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-body">

            <h6 class="fw-bold mb-3">Schedule Information</h6>
            <hr>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Property</label>
                    <input class="form-control bg-light" readonly
                        value="{{ $leaseschedule->lease->property->PropertyName ?? '-' }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Lease Number</label>
                    <input class="form-control bg-light" readonly
                        value="{{ $leaseschedule->lease->LeaseNumber ?? '-' }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tenant</label>
                    <input class="form-control bg-light" readonly
                        value="{{ $leaseschedule->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Payment Frequency</label>
                    <input class="form-control bg-light" readonly
                        value="{{ $leaseschedule->paymentFrequency->Description ?? '-' }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input class="form-control bg-light" readonly
                        value="{{ $leaseschedule->StartDate ? Carbon::parse($leaseschedule->StartDate)->format('d M Y') : '-' }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input class="form-control bg-light" readonly
                        value="{{ $leaseschedule->EndDate ? Carbon::parse($leaseschedule->EndDate)->format('d M Y') : '-' }}">
                </div>

            </div>

            <br>

            <h6 class="fw-bold mb-3">Financial Information</h6>
            <hr>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Base Rent ({{ $currency }})</label>
                    <input class="form-control bg-light" readonly
                        value="{{ number_format($baseRent, 2) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Service Charge ({{ $currency }})</label>
                    <input class="form-control bg-light" readonly
                        value="{{ number_format($serviceCharge, 2) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Parking Fee ({{ $currency }})</label>
                    <input class="form-control bg-light" readonly
                        value="{{ number_format($parkingFee, 2) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Other Charges ({{ $currency }})</label>
                    <input class="form-control bg-light" readonly
                        value="{{ number_format($otherCharges, 2) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Sub Total ({{ $currency }})</label>
                    <input class="form-control bg-light fw-bold" readonly
                        value="{{ number_format($subTotal, 2) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">VAT ({{ $leaseschedule->lease->taxRule->Rate }}%) {{ $currency }}</label>
                    <input class="form-control bg-light fw-bold" readonly
                        value="{{ number_format($taxAmount, 2) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Grand Total {{ $currency }}</label>
                    <input class="form-control bg-light fw-bold" readonly
                        value="{{ number_format($grandTotal, 2) }}">
                </div>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-between bg-light small">

            <div>
                Created by <strong>{{ $leaseschedule->createdByUser->Name ?? '-' }}</strong>
                on {{ $leaseschedule->CreatedOn ? Carbon::parse($leaseschedule->CreatedOn)->format('d M Y') : '-' }}

                | Modified by <strong>{{ $leaseschedule->modifiedByUser->Name ?? '-' }}</strong>
                on {{ $leaseschedule->ModifiedOn ? Carbon::parse($leaseschedule->ModifiedOn)->format('d M Y') : '-' }}
            </div>

            <div>
                <a href="{{ route('schedulelease.index') }}" class="btn btn-sm btn-dark">Back</a>
            </div>

        </div>

    </div>
</div>
@endsection
