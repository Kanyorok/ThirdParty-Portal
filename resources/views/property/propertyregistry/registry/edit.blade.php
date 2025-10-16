@extends('layouts.app')
@section('title', 'Edit Property')
@section('content')
    <div class="container mt-4" style="max-width: 900px;">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <form action="{{ route('PropertyRegistry.update', $property->Id) }}" method="POST"
              enctype="multipart/form-data">
        @csrf
        @method('PUT')

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">Update Property Changes</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Property Name <span class="text-danger">*</span></label>
                            <input type="text" name="PropertyName" class="form-control"
                                   value="{{ old('PropertyName', $property->PropertyName) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Property Code <span class="text-danger">*</span></label>
                            <input type="text" name="PropertyCode" class="form-control"
                                   value="{{ old('PropertyCode', $property->PropertyCode) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Property Category <span class="text-danger">*</span></label>
                            <select name="Category" id="category-select" class="form-select" required>
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

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Property Type <span class="text-danger">*</span></label>
                            <select name="PropertyType" id="type-select" class="form-select" required>
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
                            <label class="form-label">Owner <span class="text-danger">*</span></label>
                            <input type="text" name="Owner" class="form-control"
                                   value="{{ old('Owner', $property->Owner) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Acquisition Date <span class="text-danger">*</span></label>
                            <input type="date" name="AcquisitionDate" class="form-control"
                                   value="{{ old('AcquisitionDate', $property->AcquisitionDate ? \Carbon\Carbon::parse($property->AcquisitionDate)->format('Y-m-d') : '') }}"
                                   required>
                        </div>
                    </div>

                    {{-- Country / Town / Address --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select name="CountryId" id="country-select" class="form-select" required>
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
                            <label for="TownCity" class="form-label">Town / City<span
                                    class="text-danger">*</span></label>
                            <select name="LocationId" id="locality-select" class="form-select" required>
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
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" name="Address" class="form-control"
                                   value="{{ old('Address', $property->Address) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Upload New Documents</label>
                        <input type="file" name="file[]" class="form-control" multiple>
                        <div class="form-text">You may upload multiple files. Existing documents are shown below.</div>
                        <div class="p-2 border rounded bg-light mt-2">
                            @forelse($property->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                                {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                            @empty
                                <span class="text-muted">No documents attached.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Property Description</label>
                        <textarea class="form-control" rows="3"
                                  name="PropertyDescription">{{ old('PropertyDescription', $property->PropertyDescription) }}</textarea>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="IsActive" value="0">
                        <input class="form-check-input" type="checkbox" id="IsActive" name="IsActive"
                               value="1" {{ old('IsActive', $property->IsActive) ? 'checked' : '' }}>
                        <label class="form-check-label" for="IsActive">Is Active</label>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Update Property</button>
                        <a href="{{ route('PropertyRegistry.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
    </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category-select');
            const typeSelect = document.getElementById('type-select');
            const countrySelect = document.getElementById('country-select');
            const localitySelect = document.getElementById('locality-select');

            // Handle category -> types
            categorySelect.addEventListener('change', function () {
                const categoryId = this.value;
                typeSelect.innerHTML = '<option value="">-- Select a Type --</option>';

                if (categoryId) {
                    const url = `{{ route('gettypes', ':Id') }}`.replace(':Id', categoryId);
                    fetch(url)
                        .then(response => response.json())
                        .then(types => {
                            types.forEach(type => {
                                const option = document.createElement('option');
                                option.value = type.Id;
                                option.textContent = type.PropertyTypeName;
                                typeSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error loading property types:', error));
                }
            });

            // Handle country -> localities
            countrySelect.addEventListener('change', function () {
                const country = this.value;
                localitySelect.innerHTML = '<option value="">-- Select a Town or City --</option>';

                if (country) {
                    const url = `{{ route('getlocalities', ':country') }}`.replace(':country', country);
                    fetch(url)
                        .then(response => response.json())
                        .then(localities => {
                            localities.forEach(locality => {
                                const option = document.createElement('option');
                                option.value = locality.ID;
                                option.textContent = locality.Name;
                                localitySelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error loading localities:', error));
                }
            });
        });
    </script>
@endsection
