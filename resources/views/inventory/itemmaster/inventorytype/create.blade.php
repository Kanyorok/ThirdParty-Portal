
@extends('layouts.app')
@section('title', 'Add Inventory Type')
@section('content')

<h4>Add Inventory Type</h4>
<form action="{{ route('inventorytype.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label for="Type" class="form-label">Inventory Type</label>
      <input type="text" class="form-control" id="Type" name="Type" placeholder="e.g., Asset">
    </div>
       <div class="form-check mb-3">
           <input type="hidden" name="Status" value="0">
           <input class="form-check-input" type="checkbox" name="Status" value="1" id="Status" checked>
           <label class="form-check-label" for="Status">Is Active</label>
       </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>
</div>

@endsection