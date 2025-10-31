@extends('layouts.app')
@section('title','New Asset Class')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Create Asset Class</h6></div>
    <div class="card-body p-3">
      @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      <form method="post" action="{{ route('assets.settings.classes.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Code</label><input name="Code" value="{{ old('Code') }}" class="form-control form-control-sm" required></div>
          <div class="col-md-5"><label class="form-label">Name</label><input name="Name" value="{{ old('Name') }}" class="form-control form-control-sm" required></div>
          <div class="col-md-2">
            <label class="form-label">Method</label>
            <select name="DepMethod" class="form-select form-select-sm">
              @foreach(['SL','DB','SUA'] as $m)<option value="{{ $m }}" @selected(old('DepMethod')===$m)>{{ $m }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-2"><label class="form-label">Life (months)</label><input type="number" name="UsefulLifeMonths" value="{{ old('UsefulLifeMonths') }}" class="form-control form-control-sm"></div>
          <div class="col-md-2"><label class="form-label">Residual %</label><input type="number" step="0.01" name="ResidualPct" value="{{ old('ResidualPct',0) }}" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Cap Threshold</label><input type="number" step="0.01" name="CapThreshold" value="{{ old('CapThreshold',0) }}" class="form-control form-control-sm"></div>
          <div class="col-md-2 d-flex align-items-end"><div class="form-check">
            <input class="form-check-input" type="checkbox" name="PoolingFlag" id="PoolingFlag" @checked(old('PoolingFlag'))><label class="form-check-label" for="PoolingFlag">Pooling</label>
          </div></div>
          <div class="col-md-3 d-flex align-items-end"><div class="form-check">
            <input class="form-check-input" type="checkbox" name="RevaluationAllowed" id="RevaluationAllowed" @checked(old('RevaluationAllowed',true))><label class="form-check-label" for="RevaluationAllowed">Allow Revaluation</label>
          </div></div>
          <div class="col-md-12">
            <label class="form-label">Default GL Map (JSON)</label>
            <textarea name="DefaultGLMap" rows="3" class="form-control form-control-sm" placeholder='{"Capitalize":{"DR":"1100","CR":"2000"}}'>{{ old('DefaultGLMap') }}</textarea>
          </div>
          <div class="col-md-2 d-flex align-items-end"><div class="form-check">
            <input class="form-check-input" type="checkbox" name="IsActive" id="IsActive" @checked(old('IsActive',true))><label class="form-check-label" for="IsActive">Active</label>
          </div></div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.settings.classes.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
