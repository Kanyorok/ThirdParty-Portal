@extends('layouts.app')
@section('title', 'Property Registry')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <h1>Edit Property</h1>
    <form action="{{ route('PropertyRegistry.update', $property->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="PropertyName" class="form-label">Property Name:</label>
            <input type="text" name="PropertyName" class="form-control"
                   value="{{ old('PropertyName', $property->PropertyName) }}" required>
        </div>
        <div class="col-md-4">
            <label for="Category" class="form-label">Property Category</label>
            <select name="Category" id="category-select" class="form-select" required>
                <option value="">-- Select a category --</option>
                @foreach ($categories as $category)
                    <option
                        value="{{ $category->Id }}" {{ old('Category', $property->Category ?? '') == $category->Id ? 'selected' : '' }}>
                        {{ $category->Name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="PropertyType" class="form-label">Property Type</label>
                <select name="PropertyType" id="type-select" class="form-select" required>
                    <option value="">-- Select a property type --</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->Id }}"
                            {{ old('PropertyType', $property->PropertyType ?? '') == $type->Id ? 'selected' : '' }}>
                            {{ $type->PropertyTypeName }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="TownCity" class="form-label">Town/City</label>
            <select name="TownCity" class="form-select" required>
                @foreach ($localities as $locality)
                    <option value="{{ $locality->ID }}" {{ $locality->TownCity == $locality->ID ? 'selected' : '' }}>
                        {{ $locality->Name }}
                    </option>
                @endforeach
            </select>
            <div class="mb-3">
                <label for="PropertyDescription" class="form-label">Description </label>
                <textarea name="PropertyDescription" class="form-control"
                          rows="4">{{ old('PropertyDescription', $property->PropertyDescription) }}</textarea>
            </div>

            <button type="submit" class="btn btn-success">Update Property</button>
            <a href="{{ route('PropertyRegistry.index') }}" class="btn btn-secondary">Cancel</a>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category-select');
            const typeSelect = document.getElementById('type-select');

            categorySelect.addEventListener('change', function () {
                const categoryId = this.value;

                // Reset type dropdown
                typeSelect.innerHTML = '<option value="">-- Select a Type --</option>';

                if (categoryId) {
                    // Construct the URL from the named route
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
