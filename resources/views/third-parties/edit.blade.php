@extends('layouts.app')

@section('title', 'Edit Third Party - ' . ($party->ThirdPartyName ?? 'N/A'))

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="mb-0">Edit Third Party: {{ $party->ThirdPartyName ?? 'N/A' }}</h4>
            </div>
            <div class="card-body">
                @if ($errors->any() && !$errors->hasAny(['ThirdPartyName', 'TradingName', 'BusinessType', 'RegistrationNumber', 'TaxPIN', 'VATNumber', 'Country', 'PhysicalAddress', 'Email', 'Phone', 'Website', 'ApprovalStatus', 'Status', 'ThirdPartyType']))
                <div class="alert alert-danger mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('web.parties.update', ['party' => $party->Id]) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="ThirdPartyName" class="form-label">Company Name</label>
                        <input type="text" class="form-control @error('ThirdPartyName') is-invalid @enderror" id="ThirdPartyName" name="ThirdPartyName" value="{{ old('ThirdPartyName', $party->ThirdPartyName) }}" required>
                        @error('ThirdPartyName')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="TradingName" class="form-label">Trading Name</label>
                        <input type="text" class="form-control @error('TradingName') is-invalid @enderror" id="TradingName" name="TradingName" value="{{ old('TradingName', $party->TradingName) }}">
                        @error('TradingName')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="BusinessType" class="form-label">Business Type</label>
                        <select class="form-control @error('BusinessType') is-invalid @enderror" id="BusinessType" name="BusinessType" required>
                            <option value="">Select Business Type</option>
                            @foreach (\App\Enums\BusinessTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('BusinessType', $party->BusinessType?->value) === $type->value)>
                                {{ $type->label() }}
                            </option>
                            @endforeach
                        </select>
                        @error('BusinessType')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="RegistrationNumber" class="form-label">Registration Number</label>
                        <input type="text" class="form-control @error('RegistrationNumber') is-invalid @enderror" id="RegistrationNumber" name="RegistrationNumber" value="{{ old('RegistrationNumber', $party->RegistrationNumber) }}">
                        @error('RegistrationNumber')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="TaxPIN" class="form-label">Tax PIN</label>
                        <input type="text" class="form-control @error('TaxPIN') is-invalid @enderror" id="TaxPIN" name="TaxPIN" value="{{ old('TaxPIN', $party->TaxPIN) }}">
                        @error('TaxPIN')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="VATNumber" class="form-label">VAT Number</label>
                        <input type="text" class="form-control @error('VATNumber') is-invalid @enderror" id="VATNumber" name="VATNumber" value="{{ old('VATNumber', $party->VATNumber) }}">
                        @error('VATNumber')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="Country" class="form-label">Country</label>
                        <input type="text" class="form-control @error('Country') is-invalid @enderror" id="Country" name="Country" value="{{ old('Country', $party->Country) }}">
                        @error('Country')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="PhysicalAddress" class="form-label">Physical Address</label>
                        <input type="text" class="form-control @error('PhysicalAddress') is-invalid @enderror" id="PhysicalAddress" name="PhysicalAddress" value="{{ old('PhysicalAddress', $party->PhysicalAddress) }}">
                        @error('PhysicalAddress')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('Email') is-invalid @enderror" id="Email" name="Email" value="{{ old('Email', $party->Email) }}">
                        @error('Email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="Phone" class="form-label">Phone</label>
                        <input type="text" class="form-control @error('Phone') is-invalid @enderror" id="Phone" name="Phone" value="{{ old('Phone', $party->Phone) }}">
                        @error('Phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="Website" class="form-label">Website</label>
                        <input type="url" class="form-control @error('Website') is-invalid @enderror" id="Website" name="Website" value="{{ old('Website', $party->Website) }}">
                        @error('Website')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ApprovalStatus" class="form-label">Approval Status</label>
                        <select class="form-control @error('ApprovalStatus') is-invalid @enderror" id="ApprovalStatus" name="ApprovalStatus" required>
                            <option value="">Select Approval Status</option>
                            @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('ApprovalStatus', $party->ApprovalStatus?->value) === $status->value)>
                                {{ $status->label() }}
                            </option>
                            @endforeach
                        </select>
                        @error('ApprovalStatus')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="Status" class="form-label">Status</label>
                        <select class="form-control @error('Status') is-invalid @enderror" id="Status" name="Status" required>
                            <option value="">Select Status</option>
                            @foreach (\App\Enums\ThirdPartyStatusEnum::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('Status', $party->Status?->value) === $status->value)>
                                {{ $status->label() }}
                            </option>
                            @endforeach
                        </select>
                        @error('Status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ThirdPartyType" class="form-label">Third Party Type</label>
                        <select class="form-control @error('ThirdPartyType') is-invalid @enderror" id="ThirdPartyType" name="ThirdPartyType" required>
                            <option value="">Select Third Party Type</option>
                            @foreach (\App\Enums\ThirdPartyTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('ThirdPartyType', $party->ThirdPartyType?->value) === $type->value)>
                                {{ $type->label() }}
                            </option>
                            @endforeach
                        </select>
                        @error('ThirdPartyType')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update Third Party</button>
                    <a href="{{ route('web.parties.show', ['party' => $party->Id]) }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection