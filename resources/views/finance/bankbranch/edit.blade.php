@extends('layouts.app')
@section('title', 'Edit Branch')

@section('content')
<div class="container mt-0">
    <div class="card shadow rounded-4">
        <div class="card-header bg-light py-2 px-3 d-flex align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="fas fa-code-branch text-primary"></i> Edit Branch
            </h6>
        </div>

        <div class="card-body">
            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('finance.bankbranch.update', $branch->BranchID) }}" 
                  method="POST" novalidate>
                @csrf
                @method('PUT')

                {{-- Bank Info --}}
                <div class="card mb-3 border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-primary"><i class="fas fa-university me-1"></i> Bank Info</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bank <span class="text-danger">*</span></label>
                            @if(isset($bank))
                                <input type="hidden" name="BankID" value="{{ $bank->BankID }}">
                                <input class="form-control" value="{{ $bank->BankName }}" disabled>
                            @else
                                <input type="number" name="BankID" class="form-control"
                                       value="{{ old('BankID', $branch->BankID ?? '') }}" required>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                            <input name="BranchName" class="form-control"
                                   value="{{ old('BranchName', $branch->BranchName ?? '') }}" required>
                        </div>
                    </div>
                </div>

                {{-- Branch Details --}}
                <div class="card mb-3 border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-primary"><i class="fas fa-map-marker-alt me-1"></i> Branch Details</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Branch Code</label>
                            <input name="BranchCode" class="form-control"
                                   value="{{ old('BranchCode', $branch->BranchCode ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <select id="countrySelect" name="CountryID" class="form-select" required>
                                <option value="" disabled>Select Country</option>
                                @foreach(\App\Models\Core\Country::active()->ordered()->get(['Id','Name']) as $c)
                                    <option value="{{ $c->Id }}" 
                                        {{ (old('CountryID', $branch->CountryID ?? '') == $c->Id) ? 'selected' : '' }}>
                                        {{ $c->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <select id="citySelect" name="CityID" class="form-select" required>
                                @if($branch->CityID)
                                    <option value="{{ $branch->CityID }}" selected>
                                        {{ optional(\App\Models\Core\Locality::find($branch->CityID))->Name }}
                                    </option>
                                @else
                                    <option value="" selected disabled>Select City</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address 1</label>
                            <input name="Address1" class="form-control"
                                   value="{{ old('Address1', $branch->Address1 ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address 2</label>
                            <input name="Address2" class="form-control"
                                   value="{{ old('Address2', $branch->Address2 ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Zip Code</label>
                            <input name="ZipCode" class="form-control"
                                   value="{{ old('ZipCode', $branch->ZipCode ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input name="Phone" class="form-control"
                                   value="{{ old('Phone', $branch->Phone ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="EmailID" class="form-control"
                                   value="{{ old('EmailID', $branch->EmailID ?? '') }}">
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('finance.bankbranch.bybank', $branch->BankID) }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success"
                        onclick="if(this.form.checkValidity()){
                            this.disabled = true;
                            this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                            this.form.submit();
                        }">
                        <i class="fas fa-save me-1"></i> Update Branch
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('countrySelect');
    const citySelect = document.getElementById('citySelect');

    countrySelect.addEventListener('change', function() {
        const countryId = this.value;
        citySelect.innerHTML = '<option disabled selected>Loading...</option>';

        fetch(`/getCities?countryId=${countryId}`)
            .then(res => res.json())
            .then(data => {
                citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';
                data.forEach(city => {
                    let opt = document.createElement('option');
                    opt.value = city.ID;
                    opt.textContent = city.Name;
                    citySelect.appendChild(opt);
                });
            })
            .catch(() => {
                citySelect.innerHTML = '<option disabled selected>Error loading cities</option>';
            });
    });
});
</script>
@endsection
