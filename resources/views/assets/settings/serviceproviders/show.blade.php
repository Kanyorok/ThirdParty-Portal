@extends('layouts.app')
@section('title','Service Provider • '.$row->Name)

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-handshake me-2"></i> Service Provider</h6>
      <div>
        <a href="{{ route('assets.settings.service-providers.edit',$row->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('assets.settings.service-providers.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <dl class="row mb-0">
        <dt class="col-md-3">Name</dt><dd class="col-md-9">{{ $row->Name }}</dd>
        <dt class="col-md-3">Category</dt><dd class="col-md-9">{{ $row->Category ?? '—' }}</dd>
        <dt class="col-md-3">SupplierID</dt><dd class="col-md-9">{{ $row->SupplierID ?? '—' }}</dd>
        <dt class="col-md-3">Email</dt><dd class="col-md-9">{{ $row->ContactEmail ?? '—' }}</dd>
        <dt class="col-md-3">Phone</dt><dd class="col-md-9">{{ $row->ContactPhone ?? '—' }}</dd>
        <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $row->IsActive ? 'Yes' : 'No' }}</dd>
      </dl>
    </div>
  </div>
</div>
@endsection
