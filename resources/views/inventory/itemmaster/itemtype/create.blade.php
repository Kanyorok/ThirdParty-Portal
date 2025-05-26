@extends('layouts.app')
@section('title', 'Add Item Type')
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
  <h4>Add Item Type</h4>
  <form action="{{ route('itemtype.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label for="typeName" class="form-label">Type Name</label>
      <input type="text" class="form-control" id="typeName" name= "TypeName" placeholder="e.g., Asset, Stock, Non-Stock">
    </div>
 <div class="form-check mb-2">
      <input type="hidden" name="StockTracked" value="0">
      <input class="form-check-input" type="checkbox" name="StockTracked" value="1" id="StockTracked" checked>
      <label class="form-check-label" for="StockTracked">Stock Tracked?</label>
    <div class="form-check mb-2">
      <input type="hidden" name="RequiresTagging" value="0">
      <input class="form-check-input" type="checkbox" name="RequiresTagging" value="1" id="RequiresTagging" checked>
      <label class="form-check-label" for="RequiresTagging">Requires Tagging ?</label>
        </div>
    <div class="form-check mb-2">
      <input type="hidden" name="Active" value="0">
      <input class="form-check-input" type="checkbox" name="Active" value="1" id="Active" checked>
      <label class="form-check-label" for="Active">Is Active?</label>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
  </form>
</div>

@endsection