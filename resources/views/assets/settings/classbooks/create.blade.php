@extends('layouts.app')
@section('title','New Class-Book Override')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Create Class-Book Override</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

      <form method="post" action="{{ route('assets.settings.class-books.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label">Class</label>
            <select name="ClassID" class="form-select form-select-sm" required>
              <option value="">— Select —</option>
              @foreach($classes as $c)
                <option value="{{ $c->Id }}" @selected(old('ClassID')==$c->Id)>{{ $c->Name }} ({{ $c->Code }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Book</label>
            <select name="BookID" class="form-select form-select-sm" required>
              <option value="">— Select —</option>
              @foreach($books as $b)
                <option value="{{ $b->Id }}" @selected(old('BookID')==$b->Id)>{{ $b->Name }} ({{ $b->Code }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Method (opt.)</label>
            <select name="DepMethod" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach(['SL','DB','SUA'] as $m)
                <option value="{{ $m }}" @selected(old('DepMethod')==$m)>{{ $m }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Life (months)</label><input type="number" name="UsefulLifeMonths" value="{{ old('UsefulLifeMonths') }}" class="form-control form-control-sm"></div>
          <div class="col-md-3"><label class="form-label">Residual %</label><input type="number" step="0.01" name="ResidualPct" value="{{ old('ResidualPct') }}" class="form-control form-control-sm"></div>
          <div class="col-md-2 d-flex align-items-end"><div class="form-check">
            <input class="form-check-input" type="checkbox" name="IsActive" id="IsActive" @checked(old('IsActive',true))><label class="form-check-label" for="IsActive">Active</label>
          </div></div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.settings.class-books.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
