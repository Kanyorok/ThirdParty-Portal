@extends('layouts.app')
@section('title','Location • '.$row->Code)

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-map-marker-alt me-2"></i> Location</h6>
      <div>
        <a href="{{ route('assets.settings.locations.edit',$row->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('assets.settings.locations.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <dl class="row mb-0">
        <dt class="col-md-3">Code</dt><dd class="col-md-9">{{ $row->Code }}</dd>
        <dt class="col-md-3">Site</dt><dd class="col-md-9">{{ $row->Site }}</dd>
        <dt class="col-md-3">Building</dt><dd class="col-md-9">{{ $row->Building ?? '—' }}</dd>
        <dt class="col-md-3">Floor</dt><dd class="col-md-9">{{ $row->Floor ?? '—' }}</dd>
        <dt class="col-md-3">Room</dt><dd class="col-md-9">{{ $row->Room ?? '—' }}</dd>
        <dt class="col-md-3">Parent</dt><dd class="col-md-9">{{ $parent?->Code ?? '—' }}</dd>
        <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $row->IsActive ? 'Yes' : 'No' }}</dd>
      </dl>
    </div>
  </div>
</div>
@endsection
