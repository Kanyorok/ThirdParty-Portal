@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏬 Add Floor to Block</h4>
<form action="{{ route('addfloor.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Floor Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Property</label>
            <select name="PropertyID" class="form-select" required>
              @foreach ($properties as $property)
                <option value="{{ $property->id }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
        </div>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Block</label>
            <select name="BlockID" class="form-select" required>
              @foreach ($blocks as $block)
                <option value="{{ $block->id }}">{{ $block->BlockName }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Floor Label</label>
          <input type="text" class="form-control" placeholder="e.g. Ground Floor, 1st Floor" name="FloorLabel">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Floor Notes</label>
        <textarea class="form-control" rows="2" placeholder="Optional floor notes" name="FloorNotes"></textarea>
      </div>
      <button class="btn btn-success">💾 Save Floor</button>
</form>
    </div>
  </div>
</div>
@endsection