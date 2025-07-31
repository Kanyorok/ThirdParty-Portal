@extends('layouts.app')

@section('title', 'Edit Third Party - ' . ($party->ThirdPartyName ?? 'N/A'))

@section('styles')
<style>
    /* Modern, Clean Palette */
    :root {
        --primary: #4F46E5;
        /* Indigo 600 */
        --primary-hover: #4338CA;
        /* Indigo 700 */
        --accent: #10B981;
        /* Emerald 500 */
        --text-dark: #1F2937;
        /* Gray 900 */
        --text-medium: #4B5563;
        /* Gray 700 */
        --text-light: #6B7280;
        /* Gray 500 */
        --bg-light: #F9FAFB;
        /* Gray 50 */
        --bg-content: #FFFFFF;
        /* White background for the main content area */
        --border-light: #E5E7EB;
        /* Gray 200 */
        --border-medium: #D1D5DB;
        /* Gray 300 */
        --danger-text: #DC2626;
        /* Red 600 */
        --danger-bg: #FEF2F2;
        /* Red 50 */
    }

    body {
        background-color: var(--bg-light);
        color: var(--text-dark);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .container-fluid-custom {
        width: 100%;
        padding-right: 3rem;
        /* Increased side padding */
        padding-left: 3rem;
        /* Increased side padding */
        margin-right: auto;
        margin-left: auto;
    }

    @media (min-width: 1600px) {
        .container-fluid-custom {
            max-width: 1600px;
            /* Wider for large desktops */
        }
    }

    /* Main content area styling (replaces card) */
    .main-content-area {
        background-color: var(--bg-content);
        border-radius: 0.75rem;
        /* Slightly rounded corners for the content block */
        border: 1px solid var(--border-light);
        /* Subtle border */
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
        /* Light shadow */
    }

    .content-header {
        background-color: var(--bg-content);
        border-bottom: 1px solid var(--border-light);
        padding: 1.75rem 2.5rem;
        /* More generous padding */
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .content-body {
        padding: 2.5rem;
        /* More generous padding */
    }

    .content-footer {
        border-top: 1px solid var(--border-light);
        padding: 1.75rem 2.5rem;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--text-medium);
        margin-bottom: 0.6rem;
        /* More space below labels */
        display: block;
        font-size: 0.95rem;
        /* Slightly larger label font */
    }

    .form-control,
    .form-select {
        border-radius: 0.6rem;
        /* Slightly more rounded inputs */
        border: 1px solid var(--border-medium);
        padding: 0.85rem 1.1rem;
        /* More padding inside inputs */
        font-size: 1rem;
        /* Standard input font size */
        color: var(--text-dark);
        background-color: white;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        /* Clearer focus shadow */
        outline: none;
    }

    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: var(--danger-text);
    }

    .invalid-feedback {
        color: var(--danger-text);
        font-size: 0.875rem;
        /* Slightly larger error text */
        margin-top: 0.4rem;
        /* More space above error message */
    }

    .btn {
        border-radius: 0.6rem;
        /* Consistent button rounding */
        padding: 0.85rem 1.5rem;
        /* More generous button padding */
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        /* More space between icon and text */
        cursor: pointer;
        text-decoration: none;
        font-size: 0.95rem;
        /* Slightly larger button text */
    }

    .btn-primary {
        background-color: var(--primary);
        border: 1px solid var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-hover);
        border-color: var(--primary-hover);
    }

    .btn-secondary {
        border: 1px solid var(--border-medium);
        color: var(--text-medium);
        background-color: var(--bg-content);
    }

    .btn-secondary:hover {
        background-color: var(--bg-light);
        color: var(--text-dark);
        border-color: var(--text-light);
    }

    .form-check {
        padding-left: 0;
        margin-bottom: 1.5rem;
        /* More space for checkbox */
    }

    .form-check-input {
        width: 1.3em;
        /* Slightly larger checkbox */
        height: 1.3em;
        margin-top: 0.15em;
        vertical-align: top;
        background-color: #fff;
        border: 1px solid var(--border-medium);
        border-radius: 0.3em;
        /* More rounded checkbox */
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        float: left;
        margin-right: 0.6em;
        /* More space next to checkbox */
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
        background-size: 100% 100%;
        background-position: center;
        background-repeat: no-repeat;
    }

    .form-check-label {
        cursor: pointer;
        color: var(--text-dark);
        font-weight: 500;
        font-size: 1rem;
        /* Consistent with input font size */
    }

    .alert-danger {
        background-color: var(--danger-bg);
        color: var(--danger-text);
        border: 1px solid var(--danger-text);
        border-radius: 0.6rem;
        padding: 1.25rem 2rem;
        /* More padding for alerts */
    }

    .alert-danger ul {
        margin-bottom: 0;
        padding-left: 1.5rem;
    }

    .alert-danger li {
        margin-bottom: 0.4rem;
    }

    .alert-danger li:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 768px) {

        .content-header,
        .content-body,
        .content-footer {
            padding: 1.5rem;
            /* Adjusted padding for smaller screens */
        }

        .content-footer {
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn {
            width: 100%;
        }

        .container-fluid-custom {
            padding-right: 1.5rem;
            padding-left: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid-custom py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <div class="main-content-area">
                <div class="content-header">
                    <h4 class="mb-0 fw-bold" style="color: var(--text-dark);">Edit Third Party: {{ $party->ThirdPartyName ?? 'N/A' }}</h4>
                </div>
                <div class="content-body">
                    @if ($errors->any() && !$errors->hasAny(['ThirdPartyName', 'TradingName', 'BusinessType', 'RegistrationNumber', 'TaxPIN', 'VATNumber', 'Country', 'PhysicalAddress', 'Email', 'Phone', 'Website', 'ApprovalStatus', 'Status', 'ThirdPartyType', 'IsPrequalified']))
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

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="ThirdPartyName" class="form-label">Company Name</label>
                                <input type="text" class="form-control @error('ThirdPartyName') is-invalid @enderror" id="ThirdPartyName" name="ThirdPartyName" value="{{ old('ThirdPartyName', $party->ThirdPartyName) }}" required>
                                @error('ThirdPartyName')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="TradingName" class="form-label">Trading Name</label>
                                <input type="text" class="form-control @error('TradingName') is-invalid @enderror" id="TradingName" name="TradingName" value="{{ old('TradingName', $party->TradingName) }}">
                                @error('TradingName')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="BusinessType" class="form-label">Business Type</label>
                                <select class="form-select @error('BusinessType') is-invalid @enderror" id="BusinessType" name="BusinessType" required>
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

                            <div class="col-md-6">
                                <label for="RegistrationNumber" class="form-label">Registration Number</label>
                                <input type="text" class="form-control @error('RegistrationNumber') is-invalid @enderror" id="RegistrationNumber" name="RegistrationNumber" value="{{ old('RegistrationNumber', $party->RegistrationNumber) }}">
                                @error('RegistrationNumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="TaxPIN" class="form-label">Tax PIN</label>
                                <input type="text" class="form-control @error('TaxPIN') is-invalid @enderror" id="TaxPIN" name="TaxPIN" value="{{ old('TaxPIN', $party->TaxPIN) }}">
                                @error('TaxPIN')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="VATNumber" class="form-label">VAT Number</label>
                                <input type="text" class="form-control @error('VATNumber') is-invalid @enderror" id="VATNumber" name="VATNumber" value="{{ old('VATNumber', $party->VATNumber) }}">
                                @error('VATNumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="Country" class="form-label">Country</label>
                                <input type="text" class="form-control @error('Country') is-invalid @enderror" id="Country" name="Country" value="{{ old('Country', $party->Country) }}">
                                @error('Country')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="PhysicalAddress" class="form-label">Physical Address</label>
                                <input type="text" class="form-control @error('PhysicalAddress') is-invalid @enderror" id="PhysicalAddress" name="PhysicalAddress" value="{{ old('PhysicalAddress', $party->PhysicalAddress) }}">
                                @error('PhysicalAddress')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="Email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('Email') is-invalid @enderror" id="Email" name="Email" value="{{ old('Email', $party->Email) }}">
                                @error('Email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="Phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('Phone') is-invalid @enderror" id="Phone" name="Phone" value="{{ old('Phone', $party->Phone) }}">
                                @error('Phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="Website" class="form-label">Website</label>
                                <input type="url" class="form-control @error('Website') is-invalid @enderror" id="Website" name="Website" value="{{ old('Website', $party->Website) }}">
                                @error('Website')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="ApprovalStatus" class="form-label">Approval Status</label>
                                <select class="form-select @error('ApprovalStatus') is-invalid @enderror" id="ApprovalStatus" name="ApprovalStatus" required>
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

                            <div class="col-md-6">
                                <label for="Status" class="form-label">Status</label>
                                <select class="form-select @error('Status') is-invalid @enderror" id="Status" name="Status" required>
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

                            <div class="col-md-6">
                                <label for="ThirdPartyType" class="form-label">Third Party Type</label>
                                <select class="form-select @error('ThirdPartyType') is-invalid @enderror" id="ThirdPartyType" name="ThirdPartyType" required>
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

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input @error('IsPrequalified') is-invalid @enderror" type="checkbox" id="IsPrequalified" name="IsPrequalified" value="1" @checked(old('IsPrequalified', $party->IsPrequalified))>
                                    <label class="form-check-label" for="IsPrequalified">
                                        Is Prequalified
                                    </label>
                                    @error('IsPrequalified')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-5">
                            <button type="submit" class="btn btn-primary">Update Third Party</button>
                            <a href="{{ route('web.parties.show', ['party' => $party->Id]) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection