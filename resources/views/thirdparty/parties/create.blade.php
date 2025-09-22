@extends('layouts.app')

@section('title', 'Create New Third Party')

@section('content')
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card border-0 rounded-0">

                    <div
                        class="card-header bg-light p-5 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="card-title h3 fw-bold mb-1">Create New Third Party</h1>
                            <p class="text-muted mb-0">Fill out the form below to add a new third-party company to your
                                system.</p>
                        </div>
                        <a href="{{ route('thirdparty.parties.index') }}"
                           class="btn btn-outline-secondary rounded-pill">
                            <i class="fas fa-arrow-left me-2"></i> Back to List
                        </a>
                    </div>

                    <form action="{{ route('thirdparty.parties.store') }}" method="POST">
                        @csrf
                        <div class="card-body p-5">

                            <div class="bg-light p-5 mb-5 border rounded-3">
                                <h2 class="h5 fw-bold mb-3">Company Information</h2>
                                <p class="text-muted mb-4">Provide the official and trading names, and registration
                                    details.</p>
                                <div class="row g-4">
                                    <div class="col-md-6 col-lg-4">
                                        <label for="ThirdPartyName" class="form-label">Company Legal Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('ThirdPartyName') is-invalid @enderror"
                                               id="ThirdPartyName" name="ThirdPartyName"
                                               value="{{ old('ThirdPartyName') }}" required>
                                        @error('ThirdPartyName')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label for="TradingName" class="form-label">Trading Name</label>
                                        <input type="text"
                                               class="form-control @error('TradingName') is-invalid @enderror"
                                               id="TradingName" name="TradingName" value="{{ old('TradingName') }}">
                                        @error('TradingName')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label for="BusinessType" class="form-label">Business Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('BusinessType') is-invalid @enderror"
                                                id="BusinessType" name="BusinessType" required>
                                            <option value="">Select Business Type</option>
                                            @foreach (\App\Enums\BusinessTypeEnum::cases() as $type)
                                                <option
                                                    value="{{ $type->value }}" {{ old('BusinessType') == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                            @endforeach
                                        </select>
                                        @error('BusinessType')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label for="RegistrationNumber" class="form-label">Registration Number</label>
                                        <input type="text"
                                               class="form-control @error('RegistrationNumber') is-invalid @enderror"
                                               id="RegistrationNumber" name="RegistrationNumber"
                                               value="{{ old('RegistrationNumber') }}">
                                        @error('RegistrationNumber')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label for="TaxPIN" class="form-label">Tax PIN</label>
                                        <input type="text" class="form-control @error('TaxPIN') is-invalid @enderror"
                                               id="TaxPIN" name="TaxPIN" value="{{ old('TaxPIN') }}">
                                        @error('TaxPIN')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label for="VATNumber" class="form-label">VAT Number</label>
                                        <input type="text" class="form-control @error('VATNumber') is-invalid @enderror"
                                               id="VATNumber" name="VATNumber" value="{{ old('VATNumber') }}">
                                        @error('VATNumber')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light p-5 mb-5 border rounded-3">
                                <h2 class="h5 fw-bold mb-3">Contact Details</h2>
                                <p class="text-muted mb-4">Provide the company's contact information and physical
                                    address.</p>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="Country" class="form-label">Country</label>
                                        <input type="text" class="form-control @error('Country') is-invalid @enderror"
                                               id="Country" name="Country" value="{{ old('Country') }}">
                                        @error('Country')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="Website" class="form-label">Website</label>
                                        <input type="url" class="form-control @error('Website') is-invalid @enderror"
                                               id="Website" name="Website" value="{{ old('Website') }}">
                                        @error('Website')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="Email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('Email') is-invalid @enderror"
                                               id="Email" name="Email" value="{{ old('Email') }}">
                                        @error('Email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="Phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control @error('Phone') is-invalid @enderror"
                                               id="Phone" name="Phone" value="{{ old('Phone') }}">
                                        @error('Phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="PhysicalAddress" class="form-label">Physical Address</label>
                                        <textarea class="form-control @error('PhysicalAddress') is-invalid @enderror"
                                                  id="PhysicalAddress" name="PhysicalAddress"
                                                  rows="3">{{ old('PhysicalAddress') }}</textarea>
                                        @error('PhysicalAddress')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light p-5 border rounded-3">
                                <h2 class="h5 fw-bold mb-3">Status & Classification</h2>
                                <p class="text-muted mb-4">Set the approval and operational status for this third
                                    party.</p>
                                <div class="row g-4">
                                    <div class="col-md-6 col-lg-4">
                                        <label for="ThirdPartyType" class="form-label">Third Party Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('ThirdPartyType') is-invalid @enderror"
                                                id="ThirdPartyType" name="ThirdPartyType" required>
                                            <option value="">Select Third Party Type</option>
                                            @foreach (\App\Enums\ThirdPartyTypeEnum::cases() as $type)
                                                <option
                                                    value="{{ $type->value }}" {{ old('ThirdPartyType') == $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                            @endforeach
                                        </select>
                                        @error('ThirdPartyType')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label for="ApprovalStatus" class="form-label">Approval Status <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('ApprovalStatus') is-invalid @enderror"
                                                id="ApprovalStatus" name="ApprovalStatus" required>
                                            <option value="">Select Approval Status</option>
                                            @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                                                <option
                                                    value="{{ $status->value }}" {{ old('ApprovalStatus') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                            @endforeach
                                        </select>
                                        @error('ApprovalStatus')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <label for="Status" class="form-label">Operational Status <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('Status') is-invalid @enderror" id="Status"
                                                name="Status" required>
                                            <option value="">Select Status</option>
                                            @foreach (\App\Enums\ThirdPartyStatusEnum::cases() as $status)
                                                <option
                                                    value="{{ $status->value }}" {{ old('Status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                            @endforeach
                                        </select>
                                        @error('Status')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light p-5 border-top d-flex flex-column flex-sm-row-reverse gap-3">
                            <button type="submit"
                                    class="btn btn-primary rounded-pill px-5 py-3 flex-grow-1 flex-sm-grow-0">
                                <i class="fas fa-plus me-2"></i> Save Third Party
                            </button>
                            <a href="{{ route('thirdparty.parties.index') }}"
                               class="btn btn-outline-secondary rounded-pill px-5 py-3 flex-grow-1 flex-sm-grow-0">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
