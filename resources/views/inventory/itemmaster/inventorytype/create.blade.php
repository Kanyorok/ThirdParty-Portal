@extends('layouts.app')
@section('title', 'Add Inventory Type')
@section('content')

 
  <h4>Add Inventory Type</h4>
  <form>
    <div class="mb-3">
      <label for="invTypeName" class="form-label">Inventory Type</label>
      <input type="text" class="form-control" id="invTypeName" placeholder="e.g., Asset">
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" id="invActive" checked>
      <label class="form-check-label" for="invActive">Active</label>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
  </form>
</div>

@endsection