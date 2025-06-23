@extends('layouts.app')
@section('title', 'Property Registry ')
@section('content')
<div class="container mt-5" style="max-width: 700px;">
  <h3 class="mb-4">Property Details</h3>
  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-4">Property Name</dt>
        <dd class="col-sm-8">{{ $property->PropertyName?? '-' }}</dd>

        <dt class="col-sm-4">Property Code</dt>
        <dd class="col-sm-8">{{ $property->PropertyCode?? '-' }}</dd>

        <dt class="col-sm-4">Property Type</dt>
        <dd class="col-sm-8">{{ $property->type->PropertyTypeName?? '-' }}</dd>

        <dt class="col-sm-4">Property Category</dt>
        <dd class="col-sm-8">{{ $property->propertyCategory->Name?? '-' }}</dd>

        <dt class="col-sm-4">Owner</dt>
        <dd class="col-sm-8">{{ $property->Owner?? '-' }}</dd>

        <dt class="col-sm-4">Acquisition Date</dt>
        <dd class="col-sm-8">{{ $property->AcquisitionDate ? \Carbon\Carbon::parse($property->AcquisitionDate)->format('d M Y') : '-' }}</dd>

        <dt class="col-sm-4">Country</dt>
        <dd class="col-sm-8">{{ $property->Country?? '-' }}</dd>

        <dt class="col-sm-4">Town/City</dt>
        <dd class="col-sm-8">{{ $property->propertyLocality->Name?? '-' }}</dd>

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
