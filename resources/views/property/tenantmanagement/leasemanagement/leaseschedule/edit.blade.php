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

    <form action="{{ route('schedulelease.update', $leaseschedules->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">Update Lease Schedule</div>
            <div class="card-body">
        <div class="row g-3 mb-3">
            <!-- Lease Number (Read-only) -->
            <div class="col-md-6">
                <label class="form-label">Lease Number</label>
                <input type="text" class="form-control" value="{{ $leaseschedules->lease->LeaseNumber ?? '' }}"
                       readonly>
                <input type="hidden" name="LeaseId" value="{{ $leaseschedules->LeaseNumber }}">
            </div>

            <!-- Tenant -->
            <div class="col-md-6">
                <label class="form-label">Tenant</label>
                <input type="text" class="form-control"
                       value="{{ $leaseschedules->lease->tenant->thirdParty->ThirdPartyName ?? '' }}" readonly>
                <input type="hidden" name="TenantId" value="{{ $leaseschedules->lease->tenant->Id ?? '' }}">
            </div>

            <!-- Property -->
            <div class="col-md-6">
                <label class="form-label">Property</label>
                <input type="text" class="form-control"
                       value="{{ $leaseschedules->lease->property->PropertyName ?? '' }}" readonly>
                <input type="hidden" name="PropertyId" value="{{ $leaseschedules->lease->property->Id ?? '' }}">
            </div>

            <!-- Payment Frequency -->
            <div class="col-md-6">
                <label class="form-label">Payment Frequency</label>
                <input type="text" class="form-control" value="{{ $leaseschedules->lease->code->Description ?? '' }}"
                       readonly>
                <input type="hidden" name="PaymentFrequency"
                       value="{{ $leaseschedules->lease->PaymentFrequency ?? '' }}">
            </div>
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
                        <label class="form-label">Base Rent per {{ $leaseschedules->lease->code->Description }}</label>
                        <input type="number" class="form-control charge-input" name="BaseRent"
                               value="{{ old('BaseRent', $leaseschedules->BaseRent) }}">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Service Charge</label>
                        <input type="number" class="form-control charge-input" name="ServiceCharge"
                               value="{{ old('ServiceCharge', $leaseschedules->ServiceCharge) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Parking Fee</label>
                        <input type="number" class="form-control charge-input" name="ParkingFee"
                               value="{{ old('ParkingFee', $leaseschedules->ParkingFee) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Other Charges</label>
                        <input type="number" class="form-control charge-input" name="OtherCharges"
                               value="{{ old('OtherCharges', $leaseschedules->OtherCharges) }}">
                    </div>
                </div>

                <!-- Total -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Total Amount</label>
                        <input type="number" class="form-control" id="total-amount"
                               value="{{ old('TotalAmount', ($leaseschedules->BaseRent + $leaseschedules->ServiceCharge + $leaseschedules->ParkingFee + $leaseschedules->OtherCharges)) }}"
                               readonly>
                        <input type="hidden" name="TotalAmount" id="total-amount-hidden"
                               value="{{ old('TotalAmount', ($leaseschedules->BaseRent + $leaseschedules->ServiceCharge + $leaseschedules->ParkingFee + $leaseschedules->OtherCharges)) }}">
                    </div>
                </div>

                <!-- Actions -->
                <div class="text-end">
                    <a href="{{ route('schedulelease.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Update Lease Schedule</button>
                </div>
            </div>
    </div>
    </form>

    <script>
        function calculateTotal() {
            const baseRent = parseFloat(document.querySelector('[name="BaseRent"]').value) || 0;
            const serviceCharge = parseFloat(document.querySelector('[name="ServiceCharge"]').value) || 0;
            const parkingFee = parseFloat(document.querySelector('[name="ParkingFee"]').value) || 0;
            const otherCharges = parseFloat(document.querySelector('[name="OtherCharges"]').value) || 0;

            const total = baseRent + serviceCharge + parkingFee + otherCharges;
            document.getElementById('total-amount').value = total;
            document.getElementById('total-amount-hidden').value = total;
        }

        document.querySelectorAll('.charge-input').forEach(input => {
            input.addEventListener('input', calculateTotal);
        });

        window.addEventListener('DOMContentLoaded', calculateTotal);
    </script>
@endsection
