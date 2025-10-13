@extends('layouts.app')
@section('title','Maintenance Type • '.$row->Code)

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-tools me-2"></i> Maintenance Type</h6>
      <div>
        <a href="{{ route('assets.settings.maintenance-types.edit',$row->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('assets.settings.maintenance-types.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <dl class="row mb-0">
        <dt class="col-md-3">Code</dt><dd class="col-md-9">{{ $row->Code }}</dd>
        <dt class="col-md-3">Name</dt><dd class="col-md-9">{{ $row->Name }}</dd>
        <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $row->IsActive ? 'Yes' : 'No' }}</dd>
      </dl>
    </div>
  </div>
</div>
@endsection
