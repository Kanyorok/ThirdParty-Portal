@extends('layouts.app')
@section('title', 'Tax Jurisdiction Setup')
@section('content')

    <div class="container mt-4">
        <div class="card p-4">
            <div class="card-header bg-dark text-white">
                🌍 Edit Tax Jurisdiction
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

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <p class="text-muted mt-0">
                    Update the tax jurisdiction details for your organization.
                </p>

                <form method="POST" action="{{ route('taxjurisdiction.update', $taxJurisdiction->Id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Jurisdiction Name</label>
                        <input type="text" name="JurisdictionName" class="form-control" value="{{ old('JurisdictionName', $taxJurisdiction->JurisdictionName) }}" placeholder="e.g., Kenya, Uganda" required>
                    </div> 

                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <select name="Currency" class="form-select">
                            <option disabled selected value="">Select Currency</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->Id }}" {{ $taxJurisdiction->Currency == $currency->Id ? 'selected' : '' }}>{{ $currency->Code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tax Authority</label>
                        <input type="text" name="TaxAuthority" class="form-control" value="{{ old('TaxAuthority', $taxJurisdiction->TaxAuthority) }}" placeholder="e.g., KRA, URA" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="Status" value="1" {{ $taxJurisdiction->Status ? 'checked' : '' }}>
                        <label class="form-check-label">Status</label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('taxjurisdiction.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='🔄 Updating...'; this.form.submit();}">🔄 Update Jurisdiction</button> 
                    </div>
                </form>
            </div>
        </div>

@endsection