@extends('layouts.app')
@section('title', 'Edit Property')

@section('content')

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

<div class="container mt-4" style="max-width: 1000px;">

    {{-- ================= ERROR SUMMARY ================= --}}
    @if ($errors->any())
        <div class="alert alert-danger small">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('PropertyRegistry.update', $property->Id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card shadow-lg border-0 rounded-4">

            {{-- Header --}}
            <div class="card-header bg-primary border-bottom rounded-top-4">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>
                    Update Property Details
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
                                   value="{{ old('PropertyName', $property->PropertyName) }}"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Property Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="PropertyCode"
                                   class="form-control form-control-sm"
                                   value="{{ old('PropertyCode', $property->PropertyCode) }}"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Property Category <span class="text-danger">*</span>
                            </label>
                            <select name="Category"
                                    id="category-select"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select a category --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->Id }}"
                                        {{ old('Category', $property->Category) == $category->Id ? 'selected' : '' }}>
                                        {{ $category->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= TYPE / OWNER ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Ownership & Classification</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Property Type <span class="text-danger">*</span>
                            </label>
                            <select name="PropertyType"
                                    id="type-select"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select a Type --</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->Id }}"
                                        {{ old('PropertyType', $property->PropertyType) == $type->Id ? 'selected' : '' }}>
                                        {{ $type->PropertyTypeName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Owner <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="Owner"
                                   class="form-control form-control-sm"
                                   value="{{ old('Owner', $property->Owner) }}"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Acquisition Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="AcquisitionDate"
                                   class="form-control form-control-sm"
                                   value="{{ old('AcquisitionDate', $property->AcquisitionDate ? \Carbon\Carbon::parse($property->AcquisitionDate)->format('Y-m-d') : '') }}"
                                   required>
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
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select a Country --</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->Id }}"
                                        {{ old('CountryId', $property->CountryId) == $country->Id ? 'selected' : '' }}>
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
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select a Town or City --</option>
                                @foreach($localities as $loc)
                                    <option value="{{ $loc->ID }}"
                                        {{ old('LocationId', $property->LocationId) == $loc->ID ? 'selected' : '' }}>
                                        {{ $loc->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Address <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="Address"
                                   class="form-control form-control-sm"
                                   value="{{ old('Address', $property->Address) }}"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= DOCUMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Supporting Documents</h6>

                    <label class="form-label small ">Upload New Documents</label>
                    <small class="text-muted d-block mb-2">
                        Allowed: pdf, jpg, png, docx, xlsx | Max 25MB
                    </small>

                    <input type="file"
                           name="file[]"
                           class="form-control form-control-sm"
                           accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                           multiple>

                    <div class="p-3 border rounded-3 bg-light mt-3">
                        @forelse($property->documents()->get(['t_Documents.Id','t_Documents.DocumentId','MimeType','Name']) as $document)
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        @empty
                            <span class="text-muted small">No documents attached.</span>
                        @endforelse
                    </div>
                </div>

                {{-- ================= DESCRIPTION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Description</h6>

                    <textarea name="PropertyDescription"
                              class="form-control form-control-sm"
                              rows="3">{{ old('PropertyDescription', $property->PropertyDescription) }}</textarea>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Status</h6>

                    <div class="form-check form-switch">
                        <input type="hidden" name="IsActive" value="0">
                        <input class="form-check-input"
                               type="checkbox"
                               id="IsActive"
                               name="IsActive"
                               value="1"
                               {{ old('IsActive', $property->IsActive) ? 'checked' : '' }}>
                        <label class="form-check-label small " for="IsActive">
                            Property is Active
                        </label>
                    </div>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('PropertyRegistry.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-sm btn-success px-4">
                        <i class="bi bi-save me-1"></i>
                        Update Property
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

{{-- ================= SCRIPTS ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category-select');
    const typeSelect = document.getElementById('type-select');
    const countrySelect = document.getElementById('country-select');
    const localitySelect = document.getElementById('locality-select');

    categorySelect.addEventListener('change', function () {
        typeSelect.innerHTML = '<option value="">-- Select a Type --</option>';
        if (!this.value) return;

        fetch(`{{ route('gettypes', ':id') }}`.replace(':id', this.value))
            .then(res => res.json())
            .then(types => {
                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.Id;
                    option.textContent = type.PropertyTypeName;
                    typeSelect.appendChild(option);
                });
            });
    });

    countrySelect.addEventListener('change', function () {
        localitySelect.innerHTML = '<option value="">-- Select a Town or City --</option>';
        if (!this.value) return;

        fetch(`{{ route('getlocalities', ':id') }}`.replace(':id', this.value))
            .then(res => res.json())
            .then(localities => {
                localities.forEach(loc => {
                    const option = document.createElement('option');
                    option.value = loc.ID;
                    option.textContent = loc.Name;
                    localitySelect.appendChild(option);
                });
            });
    });
});
</script>

@endsection
@section('scripts')
 @include('snippets.actions.preview-files')
@endsection