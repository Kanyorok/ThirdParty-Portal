@extends('layouts.app')
@section('title', 'Add Unit of Measure (UOM)')
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
  <h4>Add Unit of Measure (UOM)</h4>
    <form action="{{ route('unitofmeasure.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

    <div class="mb-3">
        <label for="Code" class="form-label">UOM Code</label>
        <input type="text" class="form-control" id="Code" name="Code" placeholder="e.g., PCS">
    </div>
    <div class="mb-3">
        <label for="Name" class="form-label">UOM Name</label>
        <input type="text" class="form-control" id="Name" name="Name" placeholder="e.g., Pieces">
    </div>
    <div class="form-check mb-2">
        <input type="hidden" name="BaseUnit" value="0">
        <input class="form-check-input" type="checkbox" name="BaseUnit" value="1" id="BaseUnit" checked>
        <label class="form-check-label" for="BaseUnit">Base Unit?</label>
    </div>
    <div class="form-check mb-3">
        <input type="hidden" name="Active" value="0">
        <input class="form-check-input" type="checkbox" name="Active" value="1" id="Active" checked>
        <label class="form-check-label" for="Active">Is Active?</label>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
  </form>
</div>

@endsection
