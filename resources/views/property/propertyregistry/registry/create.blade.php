@extends('layouts.app')
@section('title', 'Add New Property')

@section('content')

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

{{-- ================= ERROR SUMMARY ================= --}}
@if ($errors->any())
    <div class="container mt-3">
        <div class="alert alert-danger small">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="container mt-4" style="max-width: 1000px;">
    <form action="{{ route('propertyregistry.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card shadow-lg border-0 rounded-4">

            {{-- Header --}}
            <div class="card-header bg-primary border-bottom rounded-top-4">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-house-door me-2"></i>
                    Property Registration
                </h5>
            </div>

            <div class="card-body p-4">

                {{-- ================= BASIC DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Basic Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Property Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="PropertyName"
                                   class="form-control form-control-sm"
                                   value="{{ old('PropertyName') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Property Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="PropertyCode"
                                   class="form-control form-control-sm"
                                   value="{{ old('PropertyCode') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Property Category <span class="text-danger">*</span>
                            </label>
                            <select name="Category"
                                    id="category-select"
                                    class="form-select form-select-sm">
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
                </div>

                {{-- ================= TYPE / OWNER / DATE ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Ownership & Classification</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Property Type <span class="text-danger">*</span>
                            </label>
                            <select name="PropertyType"
                                    id="type-select"
                                    class="form-select form-select-sm">
                                <option value="">-- Select a Type --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Owner <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="Owner"
                                   class="form-control form-control-sm"
                                   value="{{ old('Owner') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Acquisition Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="AcquisitionDate"
                                   class="form-control form-control-sm"
                                   value="{{ old('AcquisitionDate') }}">
                        </div>
                    </div>
                </div>

                {{-- ================= LOCATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Location Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Country <span class="text-danger">*</span>
                            </label>
                            <select name="CountryId"
                                    id="country-select"
                                    class="form-select form-select-sm">
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
                            <label class="form-label small ">
                                Town / City <span class="text-danger">*</span>
                            </label>
                            <select name="LocationId"
                                    id="locality-select"
                                    class="form-select form-select-sm">
                                <option value="">-- Select a Town or City --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Address <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="Address"
                                   class="form-control form-control-sm"
                                   value="{{ old('Address') }}">
                        </div>
                    </div>
                </div>

                {{-- ================= DOCUMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Supporting Documents</h6>

                    <label class="form-label small ">Upload Documents</label>
                    <small class="text-muted d-block mb-2">
                        Allowed: pdf, jpg, png, docx, xlsx | Max 25MB
                    </small>
                    <input type="file"
                           name="file[]"
                           class="form-control form-control-sm"
                           accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                           multiple>
                </div>

                {{-- ================= DESCRIPTION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Description</h6>

                    <textarea name="PropertyDescription"
                              class="form-control form-control-sm"
                              rows="3"
                              placeholder="Optional description of the property...">{{ old('PropertyDescription') }}</textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('PropertyRegistry.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-sm btn-success px-4"
                            onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                        <i class="bi bi-save me-1"></i>
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

    categorySelect.addEventListener('change', () => loadTypes(categorySelect.value));

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
                    option.textContent = loc.LocationType
                        ? `${loc.Name} (${loc.LocationType})`
                        : loc.Name;

                    if (selectedLocality && selectedLocality == loc.ID) {
                        option.selected = true;
                    }

                    localitySelect.appendChild(option);
                });
            });
    }

    countrySelect.addEventListener('change', () => loadLocalities(countrySelect.value));

    if (oldCountry) {
        countrySelect.value = oldCountry;
        loadLocalities(oldCountry, oldLocality);
    }
});
</script>

@endsection
