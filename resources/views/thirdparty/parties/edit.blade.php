@extends('layouts.app')

@section('title', 'Edit Third Party')

@section('content')
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card border-0 rounded-0">

                    {{-- Header --}}
                    <div class="card-header bg-light p-5 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="card-title h3 fw-bold mb-1">Edit Third Party</h1>
                            <p class="text-muted mb-0">Update the details of the selected third-party record.</p>
                        </div>
                        <a href="{{ route('thirdparty.parties.index') }}"
                           class="btn btn-outline-secondary rounded-pill">
                            <i class="fas fa-arrow-left me-2"></i> Back to List
                        </a>
                    </div>

                    {{-- Form --}}
                    <form action="{{ route('thirdparty.parties.update', $thirdParty->Id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- PARTY TYPE SELECTION --}}
                        <div class="bg-light p-5 mb-5 border rounded-3">
                            <h2 class="h5 fw-bold mb-3">Select Party Type</h2>
                            <p class="text-muted mb-4">Choose whether this third party is an Individual or
                                Corporate/Organisation.</p>

                            <div class="row g-4">
                                <div class="col-md-6 col-lg-4">
                                    <label for="PartyType" class="form-label">Party Type <span
                                                class="text-danger">*</span></label>
                                    <select class="form-select @error('PartyType') is-invalid @enderror"
                                            id="PartyType"
                                            name="PartyType"
                                            required>
                                        <option value="">Select Party Type</option>
                                        @foreach ($partyTypes as $type)
                                            <option value="{{ $type->Value }}"
                                                    data-code="{{ $type->CodeID }}"
                                                    {{ old('PartyType', $thirdParty->ThirdPartyType) == $type->Value ? 'selected' : '' }}>
                                                {{ $type->Description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('PartyType')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- PRIMARY USER --}}
                        <div class="bg-light p-5 mb-5 border rounded-3" id="primary-user-section" style="display:none;">
                            <h2 class="h5 fw-bold mb-3">Primary User Information</h2>
                            <p class="text-muted mb-4">Edit the primary user details for this third party.</p>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="FirstName" class="form-label">First Name <span
                                                class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('FirstName') is-invalid @enderror"
                                           id="FirstName" name="FirstName"
                                           value="{{ old('FirstName', $primaryUser->FirstName ?? '') }}" required>
                                    @error('FirstName')
                                    <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="LastName" class="form-label">Last Name <span
                                                class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('LastName') is-invalid @enderror"
                                           id="LastName" name="LastName"
                                           value="{{ old('LastName', $primaryUser->LastName ?? '') }}" required>
                                    @error('LastName')
                                    <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="UserEmail" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('UserEmail') is-invalid @enderror"
                                           id="UserEmail" name="UserEmail"
                                           value="{{ old('UserEmail', $primaryUser->Email ?? '') }}" required>
                                    @error('UserEmail')
                                    <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="UserPhone" class="form-label">Phone <span
                                                class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('UserPhone') is-invalid @enderror"
                                           id="UserPhone" name="UserPhone"
                                           value="{{ old('UserPhone', $primaryUser->Phone ?? '') }}"
                                           pattern="^\+[1-9]\d{7,14}$" inputmode="tel"
                                           placeholder="e.g., +254712345678">
                                    @error('UserPhone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="Gender" class="form-label">Gender</label>
                                    <select class="form-select @error('Gender') is-invalid @enderror" id="Gender"
                                            name="Gender">
                                        <option value="">Select Gender</option>
                                        @foreach (\App\Enums\Employee\GenderEnum::cases() as $gender)
                                            <option value="{{ $gender->value }}" {{ old('Gender', $primaryUser->Gender ?? '') == $gender->value ? 'selected' : '' }}>
                                                {{ $gender->value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('Gender')
                                    <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- CORPORATE FIELDS --}}
                        <div class="bg-light p-5 mb-5 border rounded-3" id="corporate-fields" style="display:none;">
                            <h2 class="h5 fw-bold mb-3">Company Information (Corporate)</h2>
                            <div class="row g-4">
                                <div class="col-md-6 col-lg-4">
                                    <label for="ThirdPartyName" class="form-label">Company Legal Name</label>
                                    <input type="text" class="form-control" id="ThirdPartyName" name="ThirdPartyName"
                                           value="{{ old('ThirdPartyName', $thirdParty->ThirdPartyName) }}">
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="TradingName" class="form-label">Trading Name</label>
                                    <input type="text" class="form-control" id="TradingName" name="TradingName"
                                           value="{{ old('TradingName', $thirdParty->TradingName) }}">
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="BusinessType" class="form-label">Business Type</label>
                                    <select class="form-select" id="BusinessType" name="BusinessType">
                                        <option value="">Select Business Type</option>
                                        @foreach (\App\Enums\BusinessTypeEnum::cases() as $type)
                                            <option value="{{ $type->value }}" {{ old('BusinessType', $thirdParty->BusinessType->value ?? '') == $type->value ? 'selected' : '' }}>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="RegistrationNumber" class="form-label">Registration Number</label>
                                    <input type="text" class="form-control" id="RegistrationNumber"
                                           name="RegistrationNumber"
                                           value="{{ old('RegistrationNumber', $thirdParty->RegistrationNumber) }}">
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="TaxPIN" class="form-label">Tax PIN</label>
                                    <input type="text" class="form-control" id="TaxPIN" name="TaxPIN"
                                           value="{{ old('TaxPIN', $thirdParty->TaxPIN) }}">
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="VATNumber" class="form-label">VAT Number</label>
                                    <input type="text" class="form-control" id="VATNumber" name="VATNumber"
                                           value="{{ old('VATNumber', $thirdParty->VATNumber) }}">
                                </div>
                            </div>
                        </div>

                        {{-- INDIVIDUAL FIELDS --}}
                        <div class="bg-light p-5 mb-5 border rounded-3" id="individual-fields" style="display:none;">
                            <h2 class="h5 fw-bold mb-3">Individual Third Party</h2>
                            <div class="row g-4">
                                <div class="col-md-6 col-lg-4">
                                    <label for="ThirdPartyName_ind" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="ThirdPartyName_ind"
                                           name="ThirdPartyName"
                                           value="{{ old('ThirdPartyName', $thirdParty->ThirdPartyName) }}">
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="IDNumber" class="form-label">ID Number</label>
                                    <input type="text" class="form-control" id="IDNumber" name="IDNumber"
                                           value="{{ old('IDNumber', $thirdParty->IDNumber) }}">
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="PassportNo" class="form-label">Passport No</label>
                                    <input type="text" class="form-control" id="PassportNo" name="PassportNo"
                                           value="{{ old('PassportNo', $thirdParty->PassportNo) }}">
                                </div>
                            </div>
                        </div>

                        {{-- CONTACT DETAILS --}}
                        <div class="bg-light p-5 mb-5 border rounded-3" id="contact-section">
                            <h2 class="h5 fw-bold mb-3">Contact Details</h2>
                            <div class="row g-4">
                                <div class="col-md-6 col-lg-4">
                                    <label for="Country" class="form-label">Country</label>
                                    <select class="form-select" id="Country" name="CountryId">
                                        <option value="">Select Country</option>
                                        @foreach ($country as $c)
                                            <option value="{{ $c->Id }}" {{ old('CountryId', $thirdParty->CountryId) == $c->Id ? 'selected' : '' }}>
                                                {{ $c->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="Website" class="form-label">Website</label>
                                    <input type="url" class="form-control" id="Website" name="Website"
                                           value="{{ old('Website', $thirdParty->Website) }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="Email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="Email" name="Email"
                                           value="{{ old('Email', $thirdParty->Email) }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="Phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="Phone" name="Phone"
                                           value="{{ old('Phone', $thirdParty->Phone) }}"
                                           pattern="^\+[1-9]\d{7,14}$" inputmode="tel">
                                </div>

                                <div class="col-12">
                                    <label for="PhysicalAddress" class="form-label">Physical Address</label>
                                    <textarea class="form-control" id="PhysicalAddress" name="PhysicalAddress"
                                              rows="3">{{ old('PhysicalAddress', $thirdParty->PhysicalAddress) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- STATUS --}}
                        <div class="bg-light p-5 border rounded-3">
                            <h2 class="h5 fw-bold mb-3">Status & Classification</h2>
                            <div class="row g-4">
                                <div class="col-md-6 col-lg-4">
                                    <label class="form-label">Third Party Type</label>
                                    @foreach($thirdPartyTypes as $type)
                                        <div class="form-check mb-2">
                                            <input type="checkbox"
                                                   class="form-check-input type-checkbox"
                                                   id="type_{{ $type->TypeId }}"
                                                   name="ThirdPartyType[]"
                                                   value="{{ $type->TypeId }}"
                                                    {{ collect(old('ThirdPartyType', $thirdParty->types->pluck('TypeId') ?? []))->contains($type->TypeId) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="type_{{ $type->TypeId }}">
                                                {{ $type->Description ?? $type->Code ?? 'Unknown Type' }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="ApprovalStatus" class="form-label">Approval Status</label>
                                    <select class="form-select" id="ApprovalStatus" name="ApprovalStatus">
                                        <option value="">Select Approval Status</option>
                                        @foreach (\App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::cases() as $status)
                                            <option value="{{ $status->value }}" {{ old('ApprovalStatus', $thirdParty->ApprovalStatus->value ?? '') == $status->value ? 'selected' : '' }}>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label for="Status" class="form-label">Operational Status</label>
                                    <select class="form-select" id="Status" name="Status">
                                        <option value="">Select Status</option>
                                        @foreach (\App\Enums\ThirdParty\ThirdPartyStatusEnum::cases() as $status)
                                            <option value="{{ $status->value }}" {{ old('Status', $thirdParty->Status->value ?? '') == $status->value ? 'selected' : '' }}>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- FOOTER BUTTONS --}}
                        <div class="card-footer bg-light p-5 border-top d-flex flex-column flex-sm-row-reverse gap-3">
                            <button type="submit"
                                    class="btn btn-primary rounded-pill px-5 py-3 flex-grow-1 flex-sm-grow-0">
                                <i class="fas fa-save me-2"></i> Update Third Party
                            </button>
                            <a href="{{ route('thirdparty.parties.index') }}"
                               class="btn btn-outline-secondary rounded-pill px-5 py-3 flex-grow-1 flex-sm-grow-0">
                                Cancel
                            </a>
                        </div>
                    </form>

                    {{-- JS --}}
                    @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const sel = document.getElementById('PartyType');
                                const primary = document.getElementById('primary-user-section');
                                const corp = document.getElementById('corporate-fields');
                                const ind = document.getElementById('individual-fields');

                                function toggleFields(showIndividual) {
                                    document.querySelectorAll('#individual-fields input, #individual-fields select, #individual-fields textarea')
                                        .forEach(el => el.disabled = !showIndividual);

                                    document.querySelectorAll('#corporate-fields input, #corporate-fields select, #corporate-fields textarea')
                                        .forEach(el => el.disabled = showIndividual);
                                }

                                function update() {
                                    if (!sel) return;
                                    const val = sel.value;
                                    if (!val) {
                                        primary.style.display = 'none';
                                        corp.style.display = 'none';
                                        ind.style.display = 'none';
                                        toggleFields(false);
                                        return;
                                    }

                                    primary.style.display = '';
                                    if (val.toLowerCase() === 'in' || sel.options[sel.selectedIndex].text.toLowerCase().includes('individual')) {
                                        ind.style.display = '';
                                        corp.style.display = 'none';
                                        toggleFields(true);
                                    } else {
                                        ind.style.display = 'none';
                                        corp.style.display = '';
                                        toggleFields(false);
                                    }
                                }

                                sel?.addEventListener('change', update);
                                update();
                            });
                        </script>
                    @endpush

                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
@endsection
