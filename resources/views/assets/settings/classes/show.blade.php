@extends('layouts.app')
@section('title','Asset Class • ' . ($row->Name ?? 'View'))

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="fas fa-layer-group me-2"></i> Asset Class</h6>
      <div>
        <a href="{{ route('assets.settings.classes.edit',$row->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('assets.settings.classes.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <dl class="row mb-0">
        <dt class="col-md-3">Code</dt><dd class="col-md-9">{{ $row->Code }}</dd>
        <dt class="col-md-3">Name</dt><dd class="col-md-9">{{ $row->Name }}</dd>
        <dt class="col-md-3">Method</dt><dd class="col-md-9">{{ $row->DepMethod }}</dd>
        <dt class="col-md-3">Useful Life (months)</dt><dd class="col-md-9">{{ $row->UsefulLifeMonths }}</dd>
        <dt class="col-md-3">Residual %</dt><dd class="col-md-9">{{ number_format($row->ResidualPct,2) }}</dd>
        <dt class="col-md-3">Cap Threshold</dt><dd class="col-md-9">{{ number_format($row->CapThreshold,2) }}</dd>
        <dt class="col-md-3">Pooling</dt><dd class="col-md-9">{{ $row->PoolingFlag ? 'Yes' : 'No' }}</dd>
        <dt class="col-md-3">Revaluation Allowed</dt><dd class="col-md-9">{{ $row->RevaluationAllowed ? 'Yes' : 'No' }}</dd>
        <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $row->IsActive ? 'Yes' : 'No' }}</dd>
        <dt class="col-md-3">Default GL Map</dt><dd class="col-md-9"><pre class="mb-0">{{ $row->DefaultGLMap }}</pre></dd>
      </dl>
    </div>
  </div>
</div>
@endsection
