@extends('layouts.app')
@section('title', 'Edit Tax Jurisdiction')

@section('content')
<div class="container">
    <div class="card p-2 shadow rounded-4 mb-0">
        <div class="card-body mb-0">

            {{-- Alerts --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Intro --}}
            <p class="text-muted">Update the tax jurisdiction details below.</p>

            {{-- Form --}}
            <form method="POST" action="{{ route('taxjurisdiction.update', $taxJurisdiction->Id) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Jurisdiction Name</label>
                        <input type="text"
                               name="JurisdictionName"
                               class="form-control"
                               value="{{ old('JurisdictionName', $taxJurisdiction->JurisdictionName) }}"
                               placeholder="e.g., Kenya, Uganda"
                               required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency</label>
                        <select name="Currency" class="form-select" required>
                            <option disabled selected value="">Select Currency</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->Id }}"
                                    {{ $taxJurisdiction->Currency == $currency->Id ? 'selected' : '' }}>
                                    {{ $currency->Code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <select name="CountryID" class="form-select" required>
                            <option value="" disabled>-- Select Country --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->Id }}"
                                    {{ (old('CountryID', $taxJurisdiction->CountryID) == $country->Id) ? 'selected' : '' }}>
                                    {{ $country->Name ?? $country->CountryCode ?? ('#'.$country->Id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Tax Authority</label>
                        <input type="text"
                               name="TaxAuthority"
                               class="form-control"
                               value="{{ old('TaxAuthority', $taxJurisdiction->TaxAuthority) }}"
                               placeholder="e.g., KRA, URA"
                               required>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="Status"
                                   value="1"
                                   id="statusSwitch"
                                   {{ $taxJurisdiction->Status ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusSwitch">Active Status</label>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('taxjurisdiction.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Back
                    </a>
                    <button type="submit"
                            class="btn btn-info"
                            onclick="if(this.form.checkValidity()){this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i>Updating...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Update Jurisdiction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
