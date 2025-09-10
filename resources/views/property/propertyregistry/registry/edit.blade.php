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

    <form action="{{ route('PropertyRegistry.update', $property->Id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label for="PropertyName" class="form-label">Property Name</label>
                <input type="text" name="PropertyName" id="PropertyName" class="form-control"
                       value="{{ old('PropertyName', $property->PropertyName) }}" required>
            </div>

            <div class="col-md-6">
                <label for="PropertyCode" class="form-label">Property Code</label>
                <input type="text" name="PropertyCode" id="PropertyCode" class="form-control"
                       value="{{ old('PropertyCode', $property->PropertyCode) }}" required>
            </div>

            <div class="col-md-6">
                <label for="Category" class="form-label">Property Category</label>
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

            <div class="col-md-6">
                <label for="PropertyType" class="form-label">Property Type</label>
                <select name="PropertyType" id="type-select" class="form-select" required>
                    <option value="">-- Select a property type --</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->Id }}"
                            {{ old('PropertyType', $property->PropertyType) == $type->Id ? 'selected' : '' }}>
                            {{ $type->PropertyTypeName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="Owner" class="form-label">Owner</label>
                <input type="text" name="Owner" id="Owner" class="form-control"
                       value="{{ old('Owner', $property->Owner) }}">
            </div>

            <div class="col-md-6">
                <label for="AcquisitionDate" class="form-label">Acquisition Date</label>
                <input type="date" name="AcquisitionDate" id="AcquisitionDate" class="form-control"
                       value="{{ old('AcquisitionDate', $property->AcquisitionDate ? \Carbon\Carbon::parse($property->AcquisitionDate)->format('Y-m-d') : '') }}">
            </div>

            <div class="col-md-6">
                <label for="Country" class="form-label">Country</label>
                <input type="text" name="Country" id="Country" class="form-control"
                       value="{{ old('Country', $property->Country) }}">
            </div>

            <div class="col-md-6">
                <label for="TownCity" class="form-label">Town/City</label>
                <select name="TownCity" id="TownCity" class="form-select" required>
                    <option value="">-- Select a city --</option>
                    @foreach ($localities as $locality)
                        <option value="{{ $locality->ID }}"
                            {{ old('TownCity', $property->TownCity) == $locality->ID ? 'selected' : '' }}>
                            {{ $locality->Name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="AreaLocality" class="form-label">Area/Locality</label>
                <input type="text" name="AreaLocality" id="AreaLocality" class="form-control"
                       value="{{ old('AreaLocality', $property->AreaLocality) }}">
            </div>

            <div class="col-12">
                <label for="PropertyDescription" class="form-label">Property Description</label>
                <textarea name="PropertyDescription" id="PropertyDescription" class="form-control" rows="3">{{ old('PropertyDescription', $property->PropertyDescription) }}</textarea>
            </div>

            <div class="col-md-6 form-check form-switch">
                <!-- hidden input ensures 0 is sent when unchecked -->
                <input type="hidden" name="IsActive" value="0">
                <input class="form-check-input" type="checkbox" id="IsActive" name="IsActive"
                    value="1" {{ old('IsActive', $property->IsActive) ? 'checked' : '' }}>
                <label class="form-check-label" for="IsActive">Is Active</label>
            </div>

            <div class="col-12">
                <label for="Documents" class="form-label">Attach New Documents</label>
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
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-success">Update Property</button>
            <a href="{{ route('PropertyRegistry.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categorySelect = document.getElementById('category-select');
        const typeSelect = document.getElementById('type-select');

        categorySelect.addEventListener('change', function () {
            const categoryId = this.value;
            typeSelect.innerHTML = '<option value="">-- Select a property type --</option>';

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
    });
</script>
@endsection
