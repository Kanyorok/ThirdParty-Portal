@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Renewal Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            {{-- Lease Renewal Information --}}
            <h6 class="mb-3 text-dark">Lease Renewal Information</h6>
            <hr>
            <div class="row g-3 text-dark">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lease Number</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->lease->LeaseNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tenant</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->lease->tenant->thirdParty->ThirdPartyName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Property</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->lease->property->PropertyName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Payment Frequency</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->paymentFrequency->Description ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">End Date of Current Lease</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->EndDateCurrentLease ? Carbon::parse($leaserenewal->EndDateCurrentLease)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">New Start Date</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->NewStartDate ? Carbon::parse($leaserenewal->NewStartDate)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">New End Date</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->NewEndDate ? Carbon::parse($leaserenewal->NewEndDate)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">New Monthly Rent</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->NewMonthlyRent ? number_format($leaserenewal->NewMonthlyRent, 2) : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Service Charge</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->ServiceCharge ? number_format($leaserenewal->ServiceCharge, 2) : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Parking Fee</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->ParkingFee ? number_format($leaserenewal->ParkingFee, 2) : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Other Charges</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaserenewal->OtherCharges ? number_format($leaserenewal->OtherCharges, 2) : '-' }}" readonly>
                </div>
            </div>

            {{-- Remarks --}}
            <h6 class="mt-4 mb-2 text-dark">Remarks</h6>
            <div class="mb-3">
                <textarea class="form-control bg-light text-dark" rows="3" readonly>
                    {{ $leaserenewal->Remarks ?? '—' }}
                </textarea>
            </div>
        </div>

        {{-- Footer with Audit Info + Actions --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-dark">
            <div>
                Created by <strong>{{ $leaserenewal->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $leaserenewal->CreatedOn ? Carbon::parse($leaserenewal->CreatedOn)->format('d/m/Y') : '-' }}</strong>
                | Modified by <strong>{{ $leaserenewal->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $leaserenewal->ModifiedOn ? Carbon::parse($leaserenewal->ModifiedOn)->format('d/m/Y') : '-' }}</strong>
            </div>
            <div>
                <a href="{{ route('renewlease.edit', $leaserenewal->Id) }}" class="btn btn-sm btn-dark">Edit</a>
                <a href="{{ route('renewlease.index') }}" class="btn btn-sm btn-dark">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
