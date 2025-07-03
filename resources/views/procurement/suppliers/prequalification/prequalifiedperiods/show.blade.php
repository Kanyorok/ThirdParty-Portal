@extends('layouts.app')

@section('title', 'View Prequalification Period')

@section('content')
<div class="card shadow rounded-3">
    <div class="card-header bg-info text-white fw-bold fs-5">
        📄 Prequalification Period Details
    </div>

    <div class="card-body">
        <form>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Round Name</label>
                    <div class="form-control bg-light">{{ $period->Title }}</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted">Max Vendors</label>
                    <div class="form-control bg-light">{{ $period->MaxVendors }}</div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Start Date</label>
                    <div class="form-control bg-light">{{ \Carbon\Carbon::parse($period->StartDate)->format('d/m/Y') }}</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted">End Date</label>
                    <div class="form-control bg-light">{{ \Carbon\Carbon::parse($period->EndDate)->format('d/m/Y') }}</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Description / Notes</label>
                <div class="form-control bg-light" style="min-height: 80px;">{{ $period->Description }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Status</label>
                <div class="d-flex align-items-center gap-2">
                    <div class="form-control bg-light mb-0 w-auto">{{ $period->Status->label() }}</div>
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="{{ route('preqrounds.edit', $period->Id) }}" class="btn btn-primary me-2">
                    Edit
                </a>
                <a href="{{ route('preqrounds.index') }}" class="btn btn-secondary">
                    Back
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
