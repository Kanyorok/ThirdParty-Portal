@extends('layouts.app')
@section('title', 'Add Item Type')
@section('content')
<div class="container mt-4">
  <h4>Add Item Type</h4>
  <form>
    <div class="mb-3">
      <label for="typeName" class="form-label">Type Name</label>
      <input type="text" class="form-control" id="typeName" placeholder="e.g., Asset, Stock, Non-Stock">
    </div>
 <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="stockTracked">
          <label class="form-check-label" for="stockTracked">Stock Tracked?</label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="tagRequired">
          <label class="form-check-label" for="tagRequired">Requires Tagging?</label>
        </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" id="active">
      <label class="form-check-label" for="active">Active</label>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
  </form>
</div>

@endsection