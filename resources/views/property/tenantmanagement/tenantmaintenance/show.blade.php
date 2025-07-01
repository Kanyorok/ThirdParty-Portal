@extends('layouts.app')
@section('title', 'Property Management')

@section('content')
    <div class="container mt-5" style="max-width: 720px;">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tenant Details</h5>
                <span class="badge bg-light text-dark">{{ $newtenant->TenantName }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">ID / Reg No.</dt>
                    <dd class="col-sm-8 text-muted">{{ $newtenant->IDRegistrationNo ?? '-' }}</dd>

                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8 text-muted">{{ $newtenant->PhoneNumber ?? '-' }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8 text-muted">{{ $newtenant->EmailAddress ?? '-' }}</dd>

                    <dt class="col-sm-4">Nationality</dt>
                    <dd class="col-sm-8 text-muted">{{ $newtenant->Nationality ?? '-' }}</dd>

                    <dt class="col-sm-4">Postal Address</dt>
                    <dd class="col-sm-8 text-muted">{{ $newtenant->PostalAddress ?? '-' }}</dd>

                    <dt class="col-sm-4">Remarks</dt>
                    <dd class="col-sm-8 text-muted">{{ $newtenant->Remarks ?? '-' }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($newtenant->IsActive == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </dd>

                </dl>
            </div>
            <div class="card-footer bg-light d-flex justify-content-end gap-2">
                <a href="{{ route('addtenant.edit', $newtenant->Id) }}" class="btn btn-outline-primary btn-sm">✏️
                    Edit</a>
                <a href="{{ route('addtenant.index') }}" class="btn btn-outline-secondary btn-sm">↩️ Back</a>
            </div>
        </div>
    </div>
@endsection
