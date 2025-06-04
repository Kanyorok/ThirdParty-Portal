@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
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
          <label class="form-label">Property Type</label>
            <select name="PropertyType" class="form-select" required>
              @foreach ($types as $type)
                <option value="{{ $type->id }}">{{ $type->PropertyTypeName }}</option>
              @endforeach
            </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Category</label>
            <select name="Category" class="form-select" required>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->PropertyCategoryName }}</option>
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
          <label class="form-label">Town / City</label>
          <input type="text" class="form-control" name="TownCity">
        </div>
        <div class="col-md-4">
          <label class="form-label">Area / Locality</label>
          <input type="text" class="form-control" name="AreaLocality">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">GPS Coordinates</label>
          <input type="text" class="form-control" name="GPSCoordinates">
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
        <button type="submit" class="btn btn-success">💾 Save Property</button>
        </form>
    </div>
  </div>
</div>
@endsection