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
                @if ($errors->any())
                <div class="alert alert-danger">
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
                        <input type="text" class="form-control" id="ThirdPartyName" name="ThirdPartyName" value="{{ old('ThirdPartyName', $party->ThirdPartyName) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="TradingName" class="form-label">Trading Name</label>
                        <input type="text" class="form-control" id="TradingName" name="TradingName" value="{{ old('TradingName', $party->TradingName) }}">
                    </div>

                    <div class="mb-3">
                        <label for="BusinessType" class="form-label">Business Type</label>
                        <select class="form-control" id="BusinessType" name="BusinessType" required>
                            @foreach (\App\Enums\BusinessTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('BusinessType', $party->BusinessType?->value) === $type->value)>
                                {{ $type->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="RegistrationNumber" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="RegistrationNumber" name="RegistrationNumber" value="{{ old('RegistrationNumber', $party->RegistrationNumber) }}">
                    </div>

                    <div class="mb-3">
                        <label for="TaxPIN" class="form-label">Tax PIN</label>
                        <input type="text" class="form-control" id="TaxPIN" name="TaxPIN" value="{{ old('TaxPIN', $party->TaxPIN) }}">
                    </div>

                    <div class="mb-3">
                        <label for="VATNumber" class="form-label">VAT Number</label>
                        <input type="text" class="form-control" id="VATNumber" name="VATNumber" value="{{ old('VATNumber', $party->VATNumber) }}">
                    </div>

                    <div class="mb-3">
                        <label for="Country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="Country" name="Country" value="{{ old('Country', $party->Country) }}">
                    </div>

                    <div class="mb-3">
                        <label for="PhysicalAddress" class="form-label">Physical Address</label>
                        <input type="text" class="form-control" id="PhysicalAddress" name="PhysicalAddress" value="{{ old('PhysicalAddress', $party->PhysicalAddress) }}">
                    </div>

                    <div class="mb-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="Email" name="Email" value="{{ old('Email', $party->Email) }}">
                    </div>

                    <div class="mb-3">
                        <label for="Phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="Phone" name="Phone" value="{{ old('Phone', $party->Phone) }}">
                    </div>

                    <div class="mb-3">
                        <label for="Website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="Website" name="Website" value="{{ old('Website', $party->Website) }}">
                    </div>

                    <div class="mb-3">
                        <label for="ApprovalStatus" class="form-label">Approval Status</label>
                        <select class="form-control" id="ApprovalStatus" name="ApprovalStatus" required>
                            @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('ApprovalStatus', $party->ApprovalStatus?->value) === $status->value)>
                                {{ $status->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="Status" class="form-label">Status</label>
                        <select class="form-control" id="Status" name="Status" required>
                            @foreach (\App\Enums\ThirdPartyStatusEnum::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('Status', $party->Status?->value) === $status->value)>
                                {{ $status->value }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="ThirdPartyType" class="form-label">Third Party Type</label>
                        <select class="form-control" id="ThirdPartyType" name="ThirdPartyType" required>
                            @foreach (\App\Enums\ThirdPartyTypeEnum::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('ThirdPartyType', $party->ThirdPartyType?->value) === $type->value)>
                                {{ $type->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Third Party</button>
                    <a href="{{ route('web.parties.show', ['party' => $party->Id]) }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection