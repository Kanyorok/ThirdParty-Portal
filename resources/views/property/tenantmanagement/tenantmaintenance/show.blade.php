@extends('layouts.app')
@section('title', 'Tenant Details')
@section('content')
    <div class="container mt-4" style="max-width: 1000px;">

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body">
                {{-- Tenant Information --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tenant Type</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $newtenant->type->Description ?? '-' }}" readonly>
                </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tenant Name</label>
                        <input type="text" class="form-control bg-light" value="{{ $newtenant->TenantName ?? '-' }}"
                               readonly>
                </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ID / Registration No.</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $newtenant->IDRegistrationNo ?? '-' }}" readonly>
                </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" class="form-control bg-light" value="{{ $newtenant->PhoneNumber ?? '-' }}"
                               readonly>
                </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="text" class="form-control bg-light" value="{{ $newtenant->EmailAddress ?? '-' }}"
                               readonly>
                </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nationality</label>
                        <input type="text" class="form-control bg-light" value="{{ $newtenant->Nationality ?? '-' }}"
                               readonly>
                </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Postal Address</label>
                        <input type="text" class="form-control bg-light" value="{{ $newtenant->PostalAddress ?? '-' }}"
                               readonly>
                </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <div class="form-control bg-light">
                        @if($newtenant->IsActive == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea class="form-control bg-light" rows="2"
                                  readonly>{{ $newtenant->Remarks ?? '-' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Footer with Audit Info and Actions --}}
            <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-muted">
                <div>
                    Created by: <strong>{{ $newtenant->createdByUser->Name ?? '-' }}</strong>
                    on
                    <strong>{{ $newtenant->CreatedOn ? \Carbon\Carbon::parse($newtenant->CreatedOn)->format('d/m/Y') : '-' }}</strong>
                    | Modified by: <strong>{{ $newtenant->modifiedByUser->Name ?? '-' }}</strong>
                    on
                    <strong>{{ $newtenant->ModifiedOn ? \Carbon\Carbon::parse($newtenant->ModifiedOn)->format('d/m/Y') : '-' }}</strong>
                </div>

                <div>
                    <a href="{{ route('addtenant.edit', $newtenant->Id) }}" class="btn btn-sm btn-primary">Edit</a>
                    <a href="{{ route('addtenant.index') }}" class="btn btn-sm btn-secondary">Back</a>
            </div>
            </div>
        </div>
    </div>
@endsection
