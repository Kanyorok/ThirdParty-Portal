@extends('layouts.app')
@section('title', 'Add Block to Property')
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
<form action="{{ route('addblock.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">Block Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Property<span class="text-danger">*</span></label>
            <select name="PropertyID" class="form-select" required>
                <option value="#">--Select a property--</option>
              @foreach ($properties as $property)
                    <option value="{{ $property->Id }}">{{ $property->PropertyName }}</option>
              @endforeach
            </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Block Name<span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="BlockName" placeholder="e.g. Block A, Tower 1">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Block Description</label>
        <textarea class="form-control" rows="2" name="Description" placeholder="Optional description"></textarea>
      </div>
        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save Block</button>
        <a href="{{ route('addblock.index') }}" class="btn btn-secondary">Cancel</a>
</form>
      </div>
    </div>
  </div>
</div>
@endsection
