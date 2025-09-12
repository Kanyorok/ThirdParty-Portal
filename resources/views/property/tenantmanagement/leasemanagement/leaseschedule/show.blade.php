@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Schedule Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            {{-- Schedule Information --}}
            <h6 class="mb-3 text-dark">Schedule Information</h6>
            <hr>
            <div class="row g-3 text-dark">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Property</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->lease->property->PropertyName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lease Number</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->lease->LeaseNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tenant</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->lease->tenant->TenantName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Payment Frequency</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->paymentFrequency->Description ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->StartDate ? Carbon::parse($leaseschedule->StartDate)->format('d M Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->EndDate ? Carbon::parse($leaseschedule->EndDate)->format('d M Y') : '-' }}" readonly>
                </div>
            </div><br>

            {{-- Financial Information --}}
            <h6 class="mb-3 text-dark">Financial Information</h6>
            <hr>
            <div class="row g-3 text-dark">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Base Rent (KES)</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ number_format($leaseschedule->BaseRent, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Service Charge (KES)</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->ServiceCharge ? number_format($leaseschedule->ServiceCharge, 2) : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Parking Fee (KES)</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->ParkingFee ? number_format($leaseschedule->ParkingFee, 2) : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Other Charges (KES)</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leaseschedule->OtherCharges ? number_format($leaseschedule->OtherCharges, 2) : '-' }}" readonly>
                </div>
            </div>
        </div>

        {{-- Footer with Audit Info + Actions --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-dark">
            <div>
                Created by <strong>{{ $leaseschedule->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $leaseschedule->CreatedOn ? Carbon::parse($leaseschedule->CreatedOn)->format('d/m/Y') : '-' }}</strong>
                | Modified by <strong>{{ $leaseschedule->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $leaseschedule->ModifiedOn ? Carbon::parse($leaseschedule->ModifiedOn)->format('d/m/Y') : '-' }}</strong>
            </div>
            <div>
                <a href="{{ route('schedulelease.edit', $leaseschedule->Id) }}" class="btn btn-sm btn-dark">Edit</a>
                <a href="{{ route('schedulelease.index') }}" class="btn btn-sm btn-dark">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
