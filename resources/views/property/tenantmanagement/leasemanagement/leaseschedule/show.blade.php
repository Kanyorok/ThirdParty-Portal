@extends('layouts.app')
@section('title', 'Lease Schedule Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Lease Schedule Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Lease Name</dt>
                    <dd class="col-sm-8">{{ $leaseschedule->leaseID }}</dd>

                    <dt class="col-sm-4">Payment Frequency</dt>
                    <dd class="col-sm-8">{{ $leaseschedule->PaymentFrequency ?? '-' }}</dd>

                    <dt class="col-sm-4">Start Date</dt>
                    <dd class="col-sm-8">{{ $leaseschedule->StartDate ?? '-' }}</dd>

                    <dt class="col-sm-4">End Date</dt>
                    <dd class="col-sm-8">{{ $leaseschedule->EndDate ?? '-' }}</dd>

                    <dt class="col-sm-4">Base Rent</dt>
                    <dd class="col-sm-8">{{ $leaseschedule->BaseRent }}</dd>

                    <dt class="col-sm-4">Service Charge</dt>
                    <dd class="col-sm-8">{{ $leaseschedule->ServiceCharge ?? '-' }}</dd>

                    <dt class="col-sm-4">Parking Fee</dt>
                    <dd class="col-sm-8">{{ $leaseschedule->ParkingFee ?? '-' }}</dd>

                    <dt class="col-sm-4">Other Charges</dt>
                    <dd class="col-sm-8">{{ $leaseschedule->OtherCharges ?? '-' }}</dd>

                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="#" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
