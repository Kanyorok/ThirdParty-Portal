@extends('layouts.app')
@section('title','Direct Capitalization')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Direct Capitalization</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      <form method="post" action="{{ route('assets.acq.direct.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Asset Code (optional)</label><input name="AssetCode" class="form-control form-control-sm"></div>
          <div class="col-md-5"><label class="form-label">Asset Name</label><input name="AssetName" class="form-control form-control-sm" required></div>
          <div class="col-md-4">
            <label class="form-label">Class</label>
            <select name="ClassID" class="form-select form-select-sm" required>
              @foreach($classes as $c)<option value="{{ $c->Id }}">{{ $c->Name }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Location</label>
            <select name="LocationID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($locations as $l)<option value="{{ $l->Id }}">{{ $l->Code }} — {{ $l->Site }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Book</label>
            <select name="BookID" class="form-select form-select-sm" required>
              @foreach($books as $b)<option value="{{ $b->Id }}">{{ $b->Name }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-2"><label class="form-label">Dep Start</label><input type="date" name="DepStartDate" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Cost</label><input type="number" step="0.01" name="AcquisitionCost" class="form-control form-control-sm" required></div>
          <div class="col-md-2"><label class="form-label">Residual %</label><input type="number" step="0.01" name="ResidualPct" class="form-control form-control-sm"></div>
        </div>
        <div class="mt-3">
          <button class="btn btn-primary btn-sm">Capitalize</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
