@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏠 Add Unit to Floor</h4>

  <form action="{{ route('addunit.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Unit Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
      <label class="form-label">Select Property</label>
            <select name="PropertyID" class="form-select" required>
              @foreach ($properties as $property)
                <option value="{{ $property->id }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
        </div>
      <div class="col-md-4">
          <label class="form-label">Select Block</label>
            <select name="BlockID" class="form-select" required>
              @foreach ($blocks as $block)
                <option value="{{ $block->id }}">{{ $block->BlockName }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Select Floor</label>
            <select name="FloorID" class="form-select" required>
              @foreach ($floors as $floor)
                <option value="{{ $floor->id }}">{{ $floor->FloorLabel }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Unit Code / Label</label>
          <input type="text" class="form-control" placeholder="e.g. Unit 101"name="UnitCode">
        </div>
        <div class="col-md-4">
          <label class="form-label">Unit Size (sq. ft)</label>
          <input type="number" class="form-control" placeholder="e.g. 1200"name="UnitSize">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Is Rentable?</label>
          <select class="form-select"name="IsRentable">
            <option>Yes</option>
            <option>No</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Current Status</label>
          <select class="form-select"name="CurrentStatus">
            <option>Vacant</option>
            <option>Occupied</option>
            <option>Reserved</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="Optional"name="Remarks">
        </div>
      </div>
      <button class="btn btn-success">💾 Save Unit</button>
    </div>
  </div>
</div>
@endsection