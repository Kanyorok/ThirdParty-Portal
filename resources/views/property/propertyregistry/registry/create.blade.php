@extends('layouts.app')
@section('title', 'Item Sub Category')
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
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏢 Add New Property</h4>
    <form action="{{ route('propertyregistry.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Property Registration</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Property Name</label>
            <input type="text" class="form-control" name="PropertyName">
        </div>
        <div class="col-md-4">
          <label class="form-label">Property Code</label>
            <input type="text" class="form-control" name="PropertyCode">
        </div>
        <div class="col-md-4">
            <label for="PropertyType" class="form-label">Property Type</label>
            <select name="PropertyType" class="form-select" required>
                @foreach ($lineentries as $type)
                    <option value="{{ $type->Id }}">{{ $type->PropertyTypeName }}</option>
                @endforeach
            </select>
        </div>
      <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="Category" class="form-label">Category</label>
            <select name="Category" class="form-select" required>
                @foreach ($lineentries as $category)
                    <option value="{{ $category->Id }}">{{ $category->PropertyCategoryName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Owner</label>
            <input type="text" class="form-control" name="Owner">
        </div>
        <div class="col-md-4">
          <label class="form-label">Acquisition Date</label>
            <input type="date" class="form-control" name="AcquisitionDate">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Country</label>
            <input type="text" class="form-control" name="Country">
        </div>
          <div class="col-md-4">
              <label for="TownCity" class="form-label">Town/City</label>
              <select name="TownCity" class="form-select" required>
                  <optgroup label="Cities">
                      @foreach ($lineentries as $locality)
                          <option value="{{ $locality->ID }}">{{ $locality->Name }}</option>
                      @endforeach
                  </optgroup>
              </select>
          </div>
        <div class="col-md-4">
          <label class="form-label">Area / Locality</label>
            <input type="text" class="form-control" name="AreaLocality">
        </div>
      </div>
        <div class="col-md-6">
          <label class="form-label">Upload Documents (PDF, JPG)</label>
          <input type="file" class="form-control" multiple>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Property Description</label>
          <textarea class="form-control" rows="3" name="PropertyDescription"></textarea>
      </div>
        <button type="submit" class="btn btn-success"
                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
            💾 Save Property
        </button>
    </form>
    </div>
  </div>
</div>
@endsection
