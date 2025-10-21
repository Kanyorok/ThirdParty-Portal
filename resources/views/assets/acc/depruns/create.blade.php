@extends('layouts.app')
@section('title','New Depreciation Run')
@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Create Depreciation Run</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      <form method="post" action="{{ route('assets.acc.depruns.store') }}">@csrf
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Book</label>
            <select name="BookID" class="form-select form-select-sm" required>
              @foreach($books as $b)<option value="{{ $b->Id }}">{{ $b->Name }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-3"><label class="form-label">Period Start</label><input type="date" name="PeriodStart" class="form-control form-control-sm" required></div>
          <div class="col-md-3"><label class="form-label">Period End</label><input type="date" name="PeriodEnd" class="form-control form-control-sm" required></div>
          <div class="col-md-3"><label class="form-label">Remarks</label><input name="Remarks" class="form-control form-control-sm"></div>
        </div>
        <div class="mt-3"><a href="{{ route('assets.acc.depruns.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Build Draft</button></div>
      </form>
    </div>
  </div>
</div>
@endsection
