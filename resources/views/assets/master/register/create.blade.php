@extends('layouts.app')
@section('title','New Asset')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Create Asset</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

      <form method="post" action="{{ route('assets.master.register.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Asset Code</label><input name="AssetCode" class="form-control form-control-sm" required></div>
          <div class="col-md-5"><label class="form-label">Asset Name</label><input name="AssetName" class="form-control form-control-sm" required></div>
          <div class="col-md-4">
            <label class="form-label">Class</label>
            <select name="ClassID" class="form-select form-select-sm" required>
              @foreach($classes as $c)<option value="{{ $c->Id }}">{{ $c->Name }} ({{ $c->Code }})</option>@endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Location</label>
            <select name="LocationID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($locations as $l)<option value="{{ $l->Id }}">{{ $l->Code }} — {{ $l->Site }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Acquisition Date</label><input type="date" name="AcquisitionDate" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Capitalization Date</label><input type="date" name="CapitalizationDate" class="form-control form-control-sm"></div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="Status" class="form-select form-select-sm">
              @foreach(['Active','Inactive','Disposed'] as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Manufacturer</label><input name="Manufacturer" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Model</label><input name="Model" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Serial</label><input name="SerialNumber" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Tag No.</label><input name="TagNo" class="form-control form-control-sm"></div>
          <div class="col-md-12"><label class="form-label">Notes</label><textarea name="Notes" rows="2" class="form-control form-control-sm"></textarea></div>
        </div>

        <hr>
        <h6 class="text-muted">Initial Financials (per Book) — optional</h6>
        <div class="row g-3">
          @foreach($books as $i => $b)
          <div class="col-12 border rounded p-2">
            <div class="row g-2 align-items-end">
              <input type="hidden" name="Book[{{ $i }}][BookID]" value="{{ $b->Id }}">
              <div class="col-md-3"><label class="form-label">{{ $b->Name }} — Acquisition Cost</label>
                <input type="number" step="0.01" name="Book[{{ $i }}][AcquisitionCost]" class="form-control form-control-sm">
              </div>
              <div class="col-md-2"><label class="form-label">Dep Method</label>
                <select name="Book[{{ $i }}][DepMethod]" class="form-select form-select-sm">
                  <option value="">(default)</option>
                  @foreach(['SL','DB','SUA'] as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                </select>
              </div>
              <div class="col-md-2"><label class="form-label">Life (months)</label>
                <input type="number" name="Book[{{ $i }}][UsefulLifeMonths]" class="form-control form-control-sm">
              </div>
              <div class="col-md-2"><label class="form-label">Residual %</label>
                <input type="number" step="0.01" name="Book[{{ $i }}][ResidualPct]" class="form-control form-control-sm">
              </div>
              <div class="col-md-3"><label class="form-label">Dep Start Date</label>
                <input type="date" name="Book[{{ $i }}][DepStartDate]" class="form-control form-control-sm">
              </div>
            </div>
          </div>
          @endforeach
        </div>

        <div class="mt-3">
          <a href="{{ route('assets.master.register.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
