@extends('layouts.app')
@section('title', 'Customer Profile View')

@section('content')
<div class="container mt-5" style="max-width: 700px;">
    <h3 class="mb-4">Customer Profile & KYC</h3>

    <div class="card">
        <div class="card-body">
            <dl class="row">

                <dt class="col-sm-4">Full Name</dt>
                <dd class="col-sm-8">{{ $customer->FullName ?? '-' }}</dd>

                <dt class="col-sm-4">Referred By</dt>
                <dd class="col-sm-8">{{ $customer->referrals->RefferedBy ?? '-' }}</dd>

                <dt class="col-sm-4">National ID</dt>
                <dd class="col-sm-8">{{ $customer->NationalID ?? '-' }}</dd>

                <dt class="col-sm-4">KRA PIN</dt>
                <dd class="col-sm-8">{{ $customer->KRAPIN ?? '-' }}</dd>

                <dt class="col-sm-4">Date of Birth</dt>
                <dd class="col-sm-8">{{ $customer->DateOfBirth ?? '-' }}</dd>

                <dt class="col-sm-4">Gender</dt>
                <dd class="col-sm-8">{{ $customer->genders->Description ?? '-' }}</dd>

                <dt class="col-sm-4">Marital Status</dt>
                <dd class="col-sm-8">{{ $customer->maritalstatus->Description ?? '-' }}</dd>

                <dt class="col-sm-4">Occupation</dt>
                <dd class="col-sm-8">{{ $customer->occupations->Description ?? '-' }}</dd>

                <dt class="col-sm-4">Phone</dt>
                <dd class="col-sm-8">{{ $customer->PhoneNumber ?? '-' }}</dd>

                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8">{{ $customer->Email ?? '-' }}</dd>

                <dt class="col-sm-4">Address</dt>
                <dd class="col-sm-8">{{ $customer->Address ?? '-' }}</dd>

            </dl>
        </div>

        <div class="card-footer">
            <a href="{{ route('bancassurance.customers.check') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>
@endsection
