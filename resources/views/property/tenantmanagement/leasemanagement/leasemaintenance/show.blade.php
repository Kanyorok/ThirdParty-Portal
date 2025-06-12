@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Patent Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Tenant</dt>
                    <dd class="col-sm-8">{{ $newlease->Tenant }}</dd>

                    <dt class="col-sm-4">Property ID</dt>
                    <dd class="col-sm-8">{{ $newlease->PropertyID ?? '-' }}</dd>

                    <dt class="col-sm-4">Block ID</dt>
                    <dd class="col-sm-8">{{ $newlease->BlockID ?? '-' }}</dd>

                    <dt class="col-sm-4">Floor ID</dt>
                    <dd class="col-sm-8">{{ $newlease->FloorID ?? '-' }}</dd>

                    <dt class="col-sm-4">Unit</dt>
                    <dd class="col-sm-8">{{ $newlease->Unit ?? '-' }}</dd>

                    <dt class="col-sm-4">Start date</dt>
                    <dd class="col-sm-8">{{ $newlease->StartDate ?? '-' }}</dd>

                    <dt class="col-sm-4">End Date</dt>
                    <dd class="col-sm-8">{{ $newlease->EndDate ?? '-' }}</dd>

                    <dt class="col-sm-4">Payment Frequency</dt>
                    <dd class="col-sm-8">{{ $newlease->PaymentFrequency ?? '-' }}</dd>

                    <dt class="col-sm-4">Monthly Rent</dt>
                    <dd class="col-sm-8">{{ $newlease->MonthlyRent ?? '-' }}</dd>

                    <dt class="col-sm-4">Deposit</dt>
                    <dd class="col-sm-8">{{ $newlease->Deposit ?? '-' }}</dd>

                    <dt class="col-sm-4">Due Day</dt>
                    <dd class="col-sm-8">{{ $newlease->DueDay ?? '-' }}</dd>

                    <dt class="col-sm-4">Special Terms</dt>
                    <dd class="col-sm-8">{{ $newlease->SpecialTerms ?? '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="#" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
