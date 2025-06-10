@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Patent Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $newtenant->TenantName }}</dd>

                    <dt class="col-sm-4">ID / Reg No.</dt>
                    <dd class="col-sm-8">{{ $newtenant->IDRegistrationNo?? '-' }}</dd>

                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8">{{ $newtenant->PhoneNumber ?? '-' }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $newtenant->EmailAddress ?? '-' }}</dd>

                    <dt class="col-sm-4">Nationality</dt>
                    <dd class="col-sm-8">{{ $newtenant->Nationality ?? '-' }}</dd>

                    <dt class="col-sm-4">Postal Address</dt>
                    <dd class="col-sm-8">{{ $newtenant->PostalAddress ?? '-' }}</dd>

                    <dt class="col-sm-4">Remarks</dt>
                    <dd class="col-sm-8">{{ $newtenant->Remarks ?? '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="#" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
