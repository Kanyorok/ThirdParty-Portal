@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
<div class="container mt-5" style="max-width: 700px;">
  <h3 class="mb-4">Patent Details</h3>
  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-4">Property Name</dt>
        <dd class="col-sm-8">{{ $property->PropertyName?? '-' }}</dd>

        <dt class="col-sm-4">Town/City</dt>
        <dd class="col-sm-8">{{ $property->TownCity?? '-' }}</dd>

        <dt class="col-sm-4">Area/Locality</dt>
        <dd class="col-sm-8">{{ $property->AreaLocality ?? '-' }}</dd>

        <dt class="col-sm-4">Property Description</dt>
        <dd class="col-sm-8">{{ $property->PropertyDescription?? '-' }}</dd>
      </dl>
    </div>
    <div class="card-footer">
        <a href="{{ route('PropertyRegistry.index') }}" class="btn btn-secondary">Back</a>
    </div>
  </div>
</div>
@endsection
