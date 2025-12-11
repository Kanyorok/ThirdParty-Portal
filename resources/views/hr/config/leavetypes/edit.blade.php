@extends('layouts.app')

@section('title', 'Edit Leave Type')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Leave Type</h2>
        <a class="btn btn-outline-secondary" href="{{ route('hr.config.leavetypes.index') }}">Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('hr.config.leavetypes.update', $type->Id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" value="{{ $type->Code }}" disabled>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Name *</label>
                        <input type="text" name="Name" class="form-control" value="{{ old('Name', $type->Name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Annual Entitlement Days *</label>
                        <input type="number" name="AnnualEntitlementDays" class="form-control" value="{{ old('AnnualEntitlementDays', $type->AnnualEntitlementDays) }}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="AllowCarryForward" value="1" id="AllowCarryForward" @checked(old('AllowCarryForward', $type->AllowCarryForward))>
                            <label for="AllowCarryForward" class="form-check-label">Allow Carry Forward</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Carry Forward Days</label>
                        <input type="number" name="MaxCarryForwardDays" class="form-control" value="{{ old('MaxCarryForwardDays', $type->MaxCarryForwardDays) }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="RequiresAttachment" value="1" id="RequiresAttachment" @checked(old('RequiresAttachment', $type->RequiresAttachment))>
                            <label for="RequiresAttachment" class="form-check-label">Requires Attachment</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsPaid" value="1" id="IsPaid" @checked(old('IsPaid', $type->IsPaid))>
                            <label for="IsPaid" class="form-check-label">Is Paid</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="IsActive" @checked(old('IsActive', $type->IsActive))>
                            <label for="IsActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Update Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
