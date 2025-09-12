@extends('layouts.app')

@section('title', 'Update Third Party - ' . ($party->ThirdPartyName ?? 'N/A'))

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    :root {
        --primary-50: #eff6ff;
        --primary-100: #dbeafe;
        --primary-500: #3b82f6;
        --primary-600: #2563eb;
        --primary-700: #1d4ed8;
        --red-50: #fef2f2;
        --red-100: #fee2e2;
        --red-500: #ef4444;
        --red-600: #dc2626;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;

        --ring-primary: 0 0 0 3px rgb(59 130 246 / 0.1);
        --ring-red: 0 0 0 3px rgb(239 68 68 / 0.1);
    }

    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        line-height: 1.6;
        color: var(--gray-900);
        background-color: var(--gray-50);
    }

    .form-field {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        line-height: 1.25rem;
    }

    .form-label .required {
        color: var(--red-500);
        margin-left: 2px;
    }

    .form-control {
        width: 100%;
        padding: 0.875rem 1.125rem;
        font-size: 0.9375rem;
        line-height: 1.375rem;
        color: var(--gray-900);
        background-color: white;
        border: 1.5px solid var(--gray-300);
        border-radius: 0.5rem;
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }

    .form-control:focus {
        border-color: var(--primary-500);
        box-shadow: var(--ring-primary);
        background-color: var(--primary-50);
    }

    .form-control:hover:not(:focus) {
        border-color: var(--gray-400);
    }

    .form-control.error {
        border-color: var(--red-500);
        box-shadow: var(--ring-red);
        animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97);
    }

    .form-control.error:focus {
        border-color: var(--red-500);
        box-shadow: var(--ring-red);
    }

    .form-error {
        font-size: 0.75rem;
        color: var(--red-600);
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-checkbox {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
    }

    .form-checkbox input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        color: var(--primary-600);
        background-color: white;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
    }

    .form-checkbox input[type="checkbox"]:checked {
        background-color: var(--primary-600);
        border-color: var(--primary-600);
    }

    .form-checkbox input[type="checkbox"]:focus {
        box-shadow: var(--ring-primary);
    }

    .form-checkbox label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        cursor: pointer;
        user-select: none;
    }

    .card {
        background-color: white;
        border-radius: 0.75rem;
        border: 1.5px solid var(--gray-200);
        overflow: hidden;
        transition: border 0.2s ease-in-out;
    }

    .card:hover {
        border-color: var(--gray-300);
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary-50) 0%, white 100%);
        padding: 2rem 2.5rem;
        border-bottom: 1.5px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .card-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: var(--gray-900);
        line-height: 1.2;
        letter-spacing: -0.025em;
    }

    .card-title .highlight {
        color: var(--primary-600);
    }

    .card-body {
        padding: 3rem 2.5rem;
    }

    .section {
        padding-bottom: 2.5rem;
        border-bottom: 1.5px solid var(--gray-100);
        margin-bottom: 2.5rem;
    }

    .section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title i {
        color: var(--primary-600);
        font-size: 1.125rem;
    }

    .form-grid {
        display: grid;
        gap: 2rem;
    }

    .form-grid.cols-1 {
        grid-template-columns: 1fr;
    }

    .form-grid.cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .form-grid.cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .col-span-2 {
        grid-column: span 2;
    }

    .col-span-3 {
        grid-column: span 3;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.25rem;
        border-radius: 0.5rem;
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease-in-out;
        white-space: nowrap;
        outline: none;
    }

    .btn:focus {
        outline: 2px solid transparent;
        outline-offset: 2px;
    }

    .btn-primary {
        color: white;
        background-color: var(--primary-600);
        border-color: var(--primary-600);
    }

    .btn-primary:hover {
        background-color: var(--primary-700);
        border-color: var(--primary-700);
        transform: translateY(-1px);
    }

    .btn-primary:focus {
        box-shadow: var(--ring-primary);
    }

    .btn-secondary {
        color: var(--gray-700);
        background-color: white;
        border-color: var(--gray-300);
    }

    .btn-secondary:hover {
        background-color: var(--gray-50);
        border-color: var(--gray-400);
        transform: translateY(-1px);
    }

    .btn-secondary:focus {
        box-shadow: var(--ring-primary);
    }

    .alert {
        padding: 1.25rem;
        border-radius: 0.75rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .alert-error {
        background-color: var(--red-50);
        border: 1px solid var(--red-200);
        color: var(--red-800);
        border-left: 4px solid var(--red-500);
    }

    .alert-icon {
        flex-shrink: 0;
        font-size: 1.25rem;
        margin-top: 0.125rem;
        color: var(--red-500);
    }

    .alert-content {
        flex: 1;
    }

    .alert-title {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .alert-description {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-bottom: 0.75rem;
    }

    .alert-list {
        list-style: disc;
        list-style-position: inside;
        margin-left: 1.25rem;
        font-size: 0.875rem;
    }

    .alert-list li {
        margin-bottom: 0.5rem;
    }

    .form-actions {
        padding-top: 2rem;
        border-top: 1.5px solid var(--gray-100);
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        20%,
        60% {
            transform: translateX(-4px);
        }

        40%,
        80% {
            transform: translateX(4px);
        }
    }

    @media (max-width: 640px) {
        .card-header {
            padding: 1.5rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .card-title {
            font-size: 1.5rem;
        }

        .card-body {
            padding: 2rem 1.5rem;
        }

        .form-grid.cols-2,
        .form-grid.cols-3 {
            grid-template-columns: 1fr;
        }

        .col-span-2,
        .col-span-3 {
            grid-column: span 1;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            justify-content: center;
        }
    }

    @media (min-width: 641px) and (max-width: 1024px) {
        .form-grid.cols-3 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1025px) {
        .form-grid.cols-3 {
            grid-template-columns: repeat(4, 1fr);
        }

        .form-grid.cols-2 {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>
@endsection

@section('content')
<div class="min-h-screen" style="padding: 1rem;">
    <div style="max-width: 100%; margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">
                    <span class="highlight">{{ $party->ThirdPartyName ?? 'N/A' }}</span>
                </h1>
                <a href="{{ route('thirdparty.parties.show', ['party' => $party->Id]) }}" class="btn btn-outline-info">
                    <i class="fas fa-arrow-left"></i> Back to Details
                </a>
            </div>

            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-error" role="alert" aria-live="assertive">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <div class="alert-content">
                        <div class="alert-title">Validation Error</div>
                        <div class="alert-description">Please correct the following issues:</div>
                        <ul class="alert-list">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <form action="{{ route('thirdparty.parties.update', ['party' => $party->Id]) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="section">
                        <h3 class="section-title">
                            <i class="fas fa-building"></i>
                            Company Details
                        </h3>
                        <div class="form-grid cols-3">
                            <div class="form-field">
                                <label for="ThirdPartyName" class="form-label">
                                    Company Name <span class="required">*</span>
                                </label>
                                <input type="text"
                                    class="form-control @error('ThirdPartyName') error @enderror"
                                    id="ThirdPartyName"
                                    name="ThirdPartyName"
                                    value="{{ old('ThirdPartyName', $party->ThirdPartyName) }}"
                                    required
                                    aria-describedby="ThirdPartyName-error">
                                @error('ThirdPartyName')
                                <div class="form-error" id="ThirdPartyName-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="TradingName" class="form-label">Trading Name</label>
                                <input type="text"
                                    class="form-control @error('TradingName') error @enderror"
                                    id="TradingName"
                                    name="TradingName"
                                    value="{{ old('TradingName', $party->TradingName) }}"
                                    aria-describedby="TradingName-error">
                                @error('TradingName')
                                <div class="form-error" id="TradingName-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="BusinessType" class="form-label">
                                    Business Type <span class="required">*</span>
                                </label>
                                <select class="form-control @error('BusinessType') error @enderror"
                                    id="BusinessType"
                                    name="BusinessType"
                                    required
                                    aria-describedby="BusinessType-error">
                                    <option value="">Select Business Type</option>
                                    @foreach (\App\Enums\BusinessTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}" @selected(old('BusinessType', $party->BusinessType?->value) === $type->value)>
                                        {{ $type->label() }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('BusinessType')
                                <div class="form-error" id="BusinessType-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="Country" class="form-label">Country</label>
                                <input type="text"
                                    class="form-control @error('Country') error @enderror"
                                    id="Country"
                                    name="Country"
                                    value="{{ old('Country', $party->Country) }}"
                                    aria-describedby="Country-error">
                                @error('Country')
                                <div class="form-error" id="Country-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-title">
                            <i class="fas fa-file-contract"></i>
                            Legal & Tax Information
                        </h3>
                        <div class="form-grid cols-3">
                            <div class="form-field">
                                <label for="RegistrationNumber" class="form-label">Registration Number</label>
                                <input type="text"
                                    class="form-control @error('RegistrationNumber') error @enderror"
                                    id="RegistrationNumber"
                                    name="RegistrationNumber"
                                    value="{{ old('RegistrationNumber', $party->RegistrationNumber) }}"
                                    aria-describedby="RegistrationNumber-error">
                                @error('RegistrationNumber')
                                <div class="form-error" id="RegistrationNumber-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="TaxPIN" class="form-label">Tax PIN</label>
                                <input type="text"
                                    class="form-control @error('TaxPIN') error @enderror"
                                    id="TaxPIN"
                                    name="TaxPIN"
                                    value="{{ old('TaxPIN', $party->TaxPIN) }}"
                                    aria-describedby="TaxPIN-error">
                                @error('TaxPIN')
                                <div class="form-error" id="TaxPIN-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="VATNumber" class="form-label">VAT Number</label>
                                <input type="text"
                                    class="form-control @error('VATNumber') error @enderror"
                                    id="VATNumber"
                                    name="VATNumber"
                                    value="{{ old('VATNumber', $party->VATNumber) }}"
                                    aria-describedby="VATNumber-error">
                                @error('VATNumber')
                                <div class="form-error" id="VATNumber-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-title">
                            <i class="fas fa-address-book"></i>
                            Contact & Address
                        </h3>
                        <div class="form-grid cols-2">
                            <div class="form-field">
                                <label for="Email" class="form-label">Email</label>
                                <input type="email"
                                    class="form-control @error('Email') error @enderror"
                                    id="Email"
                                    name="Email"
                                    value="{{ old('Email', $party->Email) }}"
                                    aria-describedby="Email-error">
                                @error('Email')
                                <div class="form-error" id="Email-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="Phone" class="form-label">Phone</label>
                                <input type="tel"
                                    class="form-control @error('Phone') error @enderror"
                                    id="Phone"
                                    name="Phone"
                                    value="{{ old('Phone', $party->Phone) }}"
                                    aria-describedby="Phone-error">
                                @error('Phone')
                                <div class="form-error" id="Phone-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field col-span-2">
                                <label for="Website" class="form-label">Website</label>
                                <input type="url"
                                    class="form-control @error('Website') error @enderror"
                                    id="Website"
                                    name="Website"
                                    value="{{ old('Website', $party->Website) }}"
                                    aria-describedby="Website-error">
                                @error('Website')
                                <div class="form-error" id="Website-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field col-span-2">
                                <label for="PhysicalAddress" class="form-label">Physical Address</label>
                                <textarea class="form-control form-textarea @error('PhysicalAddress') error @enderror"
                                    id="PhysicalAddress"
                                    name="PhysicalAddress"
                                    aria-describedby="PhysicalAddress-error">{{ old('PhysicalAddress', $party->PhysicalAddress) }}</textarea>
                                @error('PhysicalAddress')
                                <div class="form-error" id="PhysicalAddress-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h3 class="section-title">
                            <i class="fas fa-cog"></i>
                            System Information
                        </h3>
                        <div class="form-grid cols-3">
                            <div class="form-field">
                                <label class="form-label">Third Party Types</label>
                                <div class="form-control" style="background:#f8f9fa;cursor:not-allowed;opacity:.85;">
                                    @php $typeCodes = $party->types->pluck('Code')->filter()->unique(); @endphp
                                    {{ $typeCodes->isNotEmpty() ? $typeCodes->join(', ') : 'N/A' }}
                                </div>
                            </div>

                            <div class="form-field">
                                <label for="ApprovalStatus" class="form-label">
                                    Approval Status <span class="required">*</span>
                                </label>
                                <select class="form-control @error('ApprovalStatus') error @enderror"
                                    id="ApprovalStatus"
                                    name="ApprovalStatus"
                                    required
                                    aria-describedby="ApprovalStatus-error">
                                    <option value="">Select Approval Status</option>
                                    @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('ApprovalStatus', $party->ApprovalStatus?->value) === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('ApprovalStatus')
                                <div class="form-error" id="ApprovalStatus-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="Status" class="form-label">
                                    Status <span class="required">*</span>
                                </label>
                                <select class="form-control @error('Status') error @enderror"
                                    id="Status"
                                    name="Status"
                                    required
                                    aria-describedby="Status-error">
                                    <option value="">Select Status</option>
                                    @foreach (\App\Enums\ThirdPartyStatusEnum::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('Status', $party->Status?->value) === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('Status')
                                <div class="form-error" id="Status-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('thirdparty.parties.show', ['party' => $party->Id]) }}" class="btn btn-outline-dark">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-save"></i> Save Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection