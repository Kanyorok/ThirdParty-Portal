@extends('layouts.app')
@section('title', 'Tax Jurisdiction Setup')

@section('content')
    <div class="card p-1 shadow rounded-4 border-0">
        <div class="card-body">
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

            <p class="text-muted">Set up a new tax jurisdiction for your organization.</p>

            <form method="POST" action="{{ route('taxjurisdiction.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Jurisdiction Name</label>
                        <input type="text"
                        name="JurisdictionName"
                               value="{{ old('JurisdictionName') }}"
                        class="form-control @error('JurisdictionName') is-invalid @enderror"
                               placeholder="e.g., Kenya, Uganda"
                               required>
                        @error('JurisdictionName')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>Currency</label>
                        <select name="Currency" class="form-select @error('Currency') is-invalid @enderror" required>
                            <option value="" disabled selected>Select Currency</option>
                            @foreach($currencies as $currency)
                                <option
                                    value="{{ $currency->Id }}" {{ old('Currency') == $currency->Id ? 'selected' : '' }}>
                                    {{ $currency->Code }}
                                </option>
                            @endforeach
                        </select>
                        @error('Currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label>Tax Authority</label>
                    <input type="text"
                           name="TaxAuthority"
                           value="{{ old('TaxAuthority') }}"
                           class="form-control @error('TaxAuthority') is-invalid @enderror"
                           placeholder="e.g., KRA, URA"
                           required>
                    @error('TaxAuthority')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    <a href="{{ route('taxjurisdiction.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-long-arrow-alt-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-info"
                            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                        <i class="fas fa-save"></i> Save Jurisdiction
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
