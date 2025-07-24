@extends('layouts.app')
@section('title', 'Tax Jurisdiction Setup')
@section('content')
    <div class="container mt-4">
        <div class="card p-2">
{{--            <div class="card-header bg-dark text-white">--}}
{{--                🌍 Add Tax Jurisdiction--}}
{{--            </div>--}}

            <div class="card-header mb-1">
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
                            Set up a new tax jurisdiction for your organization.
                        </p>
                <form method="POST" action="{{ route('taxjurisdiction.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Jurisdiction Name</label>
                        <input type="text"
                        name="JurisdictionName"
                        value="{{old('JurisdictionName')}}"
                        class="form-control @error('JurisdictionName') is-invalid @enderror"
                        placeholder="e.g., Kenya, Uganda" required>
                        @error('JurisdictionName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <select name="Currency" class="form-select">
                            <option value="" disabled selected>Select Currency</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->Id }}">{{ $currency->Code }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tax Authority</label>
                        <input type="text" name="TaxAuthority" class="form-control" placeholder="e.g., KRA, URA" required>
                    </div>

                    <div class="d-flex justify-content-between align-coontent-centre mt-4">
                        <a href="{{ route('taxjurisdiction.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){this.disabled=true; this.innerText='💾Saving...'; this.form.submit();}">💾Save Jurisdiction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
