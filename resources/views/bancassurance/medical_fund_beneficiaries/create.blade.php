@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Add Beneficiary — {{ $medical_fund->FundName }}</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Fix the following:</strong>
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('bancassurance.medicalfunds.beneficiaries.store',$medical_fund->ID) }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="FullName" class="form-control" value="{{ old('FullName') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Relationship *</label>
                        <select name="Relationship" class="form-select" required>
                            @foreach($relationships as $rel)
                            <option value="{{ $rel->Name }}">{{ $rel->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="DateOfBirth" class="form-control" value="{{ old('DateOfBirth') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">National ID</label>
                        <input type="text" name="NationalID" class="form-control" value="{{ old('NationalID') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact</label>
                        <input type="text" name="Contact" class="form-control" value="{{ old('Contact') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="IsActive" id="isActive" value="1" {{ old('IsActive',1) ? 'checked':'' }}>
                            <label for="isActive" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('bancassurance.medicalfunds.beneficiaries.index',$medical_fund->ID) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
