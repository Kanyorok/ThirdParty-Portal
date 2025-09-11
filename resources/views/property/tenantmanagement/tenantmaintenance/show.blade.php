@extends('layouts.app')
@section('title', 'Tenant Details')
@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">Tenant Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Tenant Type</strong>
                    <p class="mb-1">{{ $newtenant->type->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Tenant Name</strong>
                    <p class="mb-1">{{ $newtenant->TenantName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>ID / Registration No.</strong>
                    <p class="mb-1">{{ $newtenant->IDRegistrationNo ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Phone Number</strong>
                    <p class="mb-1">{{ $newtenant->PhoneNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Email Address</strong>
                    <p class="mb-1">{{ $newtenant->EmailAddress ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Nationality</strong>
                    <p class="mb-1">{{ $newtenant->Nationality ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Postal Address</strong>
                    <p class="mb-1">{{ $newtenant->PostalAddress ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Status</strong>
                    <p class="mb-1">
                        @if($newtenant->IsActive == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </p>
                </div>

                <div class="col-12">
                    <strong>Remarks</strong>
                    <p class="mb-1">{{ $newtenant->Remarks ?? '-' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $newtenant->createdByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $newtenant->CreatedOn ? \Carbon\Carbon::parse($newtenant->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $newtenant->modifiedByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $newtenant->ModifiedOn ? \Carbon\Carbon::parse($newtenant->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('addtenant.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
            <a href="{{ route('addtenant.edit', $newtenant->Id) }}" class="btn btn-sm btn-primary">✏ Edit</a>
        </div>
    </div>
</div>
@endsection
