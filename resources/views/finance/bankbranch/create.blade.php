@extends('layouts.app')
@section('title', isset($branch) ? 'Edit Branch' : 'Create Branch')

@section('content')
<div class="container mt-0">
    <div class="card shadow rounded-4">
        <div class="card-header bg-light py-2 px-3 d-flex align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="fab fa-wpforms text-info"></i>
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

            <form action="{{ isset($branch) ? route('finance.bankbranch.update', $branch->BranchID) : route('finance.bankbranch.store') }}"
                  method="POST" novalidate>
                @csrf
                @if(isset($branch)) @method('PUT') @endif

                {{-- Bank Selection --}}
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
                        <h6 class="mb-0 text-primary"><i class="fas fa-code-branch me-1"></i> Branch Details</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Branch Code</label>
                            <input name="BranchCode" class="form-control"
                                   value="{{ old('BranchCode', $branch->BranchCode ?? '') }}" required>
                        </div>

                        {{-- Country (plain select) --}}
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <select name="CountryID" id="country" class="form-select">
                                <option value="" disabled {{ old('CountryID', $branch->CountryID ?? '') ? '' : 'selected' }}>Select Country</option>
                                @foreach(\App\Models\Core\Country::active()->ordered()->get(['Id','Name']) as $c)
                                    <option value="{{ $c->Id }}"
                                        {{ (string) old('CountryID', $branch->CountryID ?? '') === (string) $c->Id ? 'selected' : '' }}>
                                        {{ $c->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- City (plain select) --}}
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <select name="CityID" id="city" class="form-select">
                                {{-- This will be populated by JS. Provide a fallback when JS disabled --}}
                                @if(old('CityID'))
                                    <option value="{{ old('CityID') }}" selected>
                                        {{ optional(\App\Models\Core\Locality::find(old('CityID')))->Name }}
                                    </option>
                                @elseif(isset($branch) && $branch->CityID)
                                    <option value="{{ $branch->CityID }}" selected>
                                        {{ optional(\App\Models\Core\Locality::find($branch->CityID))->Name }}
                                    </option>
                                @else
                                    <option value="" disabled selected>Select Country first</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Address 1</label>
                            <input name="Address1" class="form-control"
                                   value="{{ old('Address1', $branch->Address1 ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address 2</label>
                            <input name="Address2" class="form-control"
                                   value="{{ old('Address2', $branch->Address2 ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Zip Code</label>
                            <input name="ZipCode" class="form-control"
                                   value="{{ old('ZipCode', $branch->ZipCode ?? '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input name="Phone" class="form-control"
                                   value="{{ old('Phone', $branch->Phone ?? '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="EmailID" class="form-control"
                                   value="{{ old('EmailID', $branch->EmailID ?? '') }}" required>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('finance.bank.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success"
                            onclick="if(this.form.checkValidity()){
                                this.disabled = true;
                                this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                                this.form.submit();
                            }">
                        <i class="fas fa-save me-1"></i>
                        Create Branch
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    if (!window.jQuery) {
        console.error('jQuery is required for this page to work.');
        return;
    }

    var $country = $('#country');
    var $city = $('#city');
    var getCitiesUrl = '{{ route('getCities') }}'; // route must return JSON array [{ID, Name}, ...]

    function setCityStateLoading() {
        $city.prop('disabled', true).empty().append(new Option('Loading...', '', false, false));
    }

    function setCityStatePlaceholder(text) {
        $city.prop('disabled', true).empty().append(new Option(text, '', false, false));
    }

    function populateCities(list, selectedId) {
        $city.empty();
        if (Array.isArray(list) && list.length > 0) {
            $city.append(new Option('Select City', '', false, false));
            list.forEach(function (item) {
                // use Option constructor (safe)
                var isSelected = selectedId != null && String(selectedId) === String(item.ID);
                var opt = new Option(item.Name, item.ID, false, isSelected);
                $city.append(opt);
            });
            $city.prop('disabled', false);
        } else {
            $city.append(new Option('No cities found', '', false, false));
            $city.prop('disabled', true);
        }
    }

    function loadCities(countryId, selectedCityId) {
        if (!countryId) {
            setCityStatePlaceholder('Select Country first');
            return;
        }

        setCityStateLoading();

        $.ajax({
            url: getCitiesUrl,
            method: 'GET',
            dataType: 'json',
            data: { countryId: countryId },
            success: function (data) {
                // Expecting JSON array [{ID, Name}, ...]
                populateCities(data || [], selectedCityId);
            },
            error: function (xhr, status, err) {
                console.error('Failed to load cities:', status, err);
                $city.empty().append(new Option('Error loading cities', '', false, false));
                $city.prop('disabled', true);
            }
        });
    }

    // when user picks a country
    $country.on('change', function () {
        var cid = $(this).val();
        loadCities(cid, null);
    });

    // on initial load: if there's an initial country (from old() or $branch), load cities and select old city
    var initialCountry = @json(old('CountryID', $branch->CountryID ?? null));
    var initialCity = @json(old('CityID', $branch->CityID ?? null));

    if (initialCountry) {
        // ensure the country select reflects initialCountry (handled by blade option selected)
        loadCities(initialCountry, initialCity);
    } else {
        setCityStatePlaceholder('Select Country first');
    }
});
</script>
@endsection
