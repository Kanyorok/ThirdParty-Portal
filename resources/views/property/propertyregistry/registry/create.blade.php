@extends('layouts.app')

@section('title', 'Add New Property')

@section('content')

{{-- ================= ERROR SUMMARY ================= --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <form action="{{ route('propertyregistry.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow">
            <div class="card-header bg-light fw-bold">
                Property Registration
            </div>

            <div class="card-body">

                {{-- ================= BASIC DETAILS ================= --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Property Name <span class="text-danger">*</span></label>
                        <input type="text" name="PropertyName" class="form-control"
                               value="{{ old('PropertyName') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Property Code <span class="text-danger">*</span></label>
                        <input type="text" name="PropertyCode" class="form-control"
                               value="{{ old('PropertyCode') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Property Category <span class="text-danger">*</span></label>
                        <select name="Category" id="category-select" class="form-select">
                            <option value="">-- Select a category --</option>
                            @foreach ($lineentries as $category)
                                <option value="{{ $category->Id }}"
                                    {{ old('Category') == $category->Id ? 'selected' : '' }}>
                                    {{ $category->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ================= TYPE / OWNER / DATE ================= --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Property Type <span class="text-danger">*</span></label>
                        <select name="PropertyType" id="type-select" class="form-select">
                            <option value="">-- Select a Type --</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Owner <span class="text-danger">*</span></label>
                        <input type="text" name="Owner" class="form-control"
                               value="{{ old('Owner') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Acquisition Date <span class="text-danger">*</span></label>
                        <input type="date" name="AcquisitionDate" class="form-control"
                               value="{{ old('AcquisitionDate') }}">
                    </div>
                </div>

                {{-- ================= LOCATION ================= --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="CountryId" id="country-select" class="form-select">
                            <option value="">-- Select a Country --</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->Id }}"
                                    {{ old('CountryId') == $country->Id ? 'selected' : '' }}>
                                    {{ $country->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Town / City <span class="text-danger">*</span></label>
                        <select name="LocationId" id="locality-select" class="form-select">
                            <option value="">-- Select a Town or City --</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" name="Address" class="form-control"
                               value="{{ old('Address') }}">
                    </div>
                </div>

                {{-- ================= DOCUMENTS ================= --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Upload Documents</label>
                        <small class="text-muted d-block mb-1">
                            Allowed: pdf, jpg, png, docx, xlsx | Max 25MB
                        </small>
                        <input type="file" name="file[]" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple>
                    </div>
                </div>

                {{-- ================= DESCRIPTION ================= --}}
                <div class="mb-3">
                    <label class="form-label">Property Description</label>
                    <textarea name="PropertyDescription" class="form-control" rows="3">{{ old('PropertyDescription') }}</textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('PropertyRegistry.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        Save Property
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- ================= OLD VALUES FOR JS ================= --}}
<script>
    const oldCategory     = "{{ old('Category') }}";
    const oldPropertyType = "{{ old('PropertyType') }}";
    const oldCountry      = "{{ old('CountryId') }}";
    const oldLocality     = "{{ old('LocationId') }}";
</script>

{{-- ================= PROPERTY TYPES ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category-select');
    const typeSelect = document.getElementById('type-select');

    function loadTypes(categoryId, selectedType = null) {
        typeSelect.innerHTML = '<option value="">-- Select a Type --</option>';
        if (!categoryId) return;

        const url = `{{ route('gettypes', ':id') }}`.replace(':id', categoryId);

        fetch(url)
            .then(res => res.json())
            .then(types => {
                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.Id;
                    option.textContent = type.PropertyTypeName;
                    if (selectedType && selectedType == type.Id) option.selected = true;
                    typeSelect.appendChild(option);
                });
            });
    }

    categorySelect.addEventListener('change', () => {
        loadTypes(categorySelect.value);
    });

    if (oldCategory) {
        categorySelect.value = oldCategory;
        loadTypes(oldCategory, oldPropertyType);
    }
});
</script>

{{-- ================= LOCALITIES ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const countrySelect = document.getElementById('country-select');
    const localitySelect = document.getElementById('locality-select');

    function loadLocalities(countryId, selectedLocality = null) {
        localitySelect.innerHTML = '<option value="">-- Select a Town or City --</option>';
        if (!countryId) return;

        const url = `{{ route('getlocalities', ':id') }}`.replace(':id', countryId);

        fetch(url)
            .then(res => res.json())
            .then(localities => {
                localities.forEach(loc => {
                    const option = document.createElement('option');
                    option.value = loc.ID;
                    option.textContent = loc.Name;
                    if (selectedLocality && selectedLocality == loc.ID) option.selected = true;
                    localitySelect.appendChild(option);
                });
            });
    }

    countrySelect.addEventListener('change', () => {
        loadLocalities(countrySelect.value);
    });

    if (oldCountry) {
        countrySelect.value = oldCountry;
        loadLocalities(oldCountry, oldLocality);
    }
});
</script>

@endsection
