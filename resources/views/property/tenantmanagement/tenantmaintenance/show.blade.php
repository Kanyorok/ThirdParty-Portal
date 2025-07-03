@extends('layouts.app')
@section('title', 'Property Management')

@section('content')
<div class="container mt-5" style="max-width: 720px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Tenant Details</h3>
        <a href="{{ route('addtenant.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    <form>
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label">Tenant Name</label>
                    <input type="text" class="form-control" value="{{ $newtenant->TenantName ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">ID / Registration No.</label>
                    <input type="text" class="form-control" value="{{ $newtenant->IDRegistrationNo ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" value="{{ $newtenant->PhoneNumber ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" class="form-control" value="{{ $newtenant->EmailAddress ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nationality</label>
                    <input type="text" class="form-control" value="{{ $newtenant->Nationality ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Postal Address</label>
                    <input type="text" class="form-control" value="{{ $newtenant->PostalAddress ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" rows="3" readonly>{{ $newtenant->Remarks ?? '—' }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div>
                        @if($newtenant->IsActive == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light d-flex justify-content-between">
                <a href="{{ route('addtenant.edit', $newtenant->Id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="{{ route('addtenant.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </form>
</div>
@endsection
