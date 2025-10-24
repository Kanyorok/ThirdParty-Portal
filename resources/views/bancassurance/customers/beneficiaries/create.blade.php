@extends('layouts.app')
@section('title', 'Add Beneficiary')
@section('content')
<div class="container mt-4">
    {{-- @dd($customers) --}}
    <form method="POST" action="{{ route('bancassurance.customers.beneficiaries.store') }}">
        @csrf
        <div class="col-md-4">
            <label class="form-label">CustomerID <span class="text-danger">*</span></label>
            <select name="CustomerID" class="form-select" required>
            <option value="">-- Select CustomerID --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->Id }}">{{ $customer->thirdParty->ThirdPartyName }}</option>
                @endforeach
            </select>
        </div>
                <div class="col-md-4">
            <label class="form-label">PolicyID <span class="text-danger">*</span></label>
            <select name="PolicyID" class="form-select" required>
            <option value="">-- Select PolicyID --</option>
                @foreach($policies as $policy)
                    <option value="{{ $policy->Id }}">{{ $policy->PolicyNumber }}</option>
                @endforeach
            </select>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="FullName" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">ID Number</label>
                <input type="text" name="IDNumber" class="form-control">
            </div>
        <div class="col-md-3">
         <label class="form-label">Relationship <span class="text-danger">*</span></label>
            <select name="Relationship" class="form-select" required>
            <option value="">--Select Occupation--</option>
              @foreach ($relationships as $relationship)
                <option value="{{ $relationship->ID }}">
                  {{ $relationship->Description }}
                </option>
              @endforeach
            </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" name="Phone" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="Email" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">% Share <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="PercentageShare" class="form-control" placeholder="e.g. 50.00" required>
            </div>
        </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsPrimary" value="1" id="primaryCheck">
                <label class="form-check-label" for="primaryCheck">Primary Beneficiary</label>
            </div>

            <div class="text-end">
                <a href="{{ route('bancassurance.customers.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-save"></i> Save Beneficiary
                </button>
            </div>
        </form>
    </div>
@endsection
