{{-- resources/views/assets/acc/impair/create.blade.php --}}
@extends('layouts.app')
@section('title','New Impairment Test')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Record Impairment Test</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      <form method="post" action="{{ route('assets.acc.impair.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Asset ID</label>
            <input name="AssetID" value="{{ old('AssetID') }}" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Book</label>
            <select name="BookID" class="form-select form-select-sm" required>
              @foreach($books as $b)<option value="{{ $b->Id }}" @selected(old('BookID')==$b->Id)>{{ $b->Name }}</option>@endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Test Date</label>
            <input type="date" name="TestDate" value="{{ old('TestDate') }}" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Recoverable Amount</label>
            <input type="number" step="0.01" name="RecoverableAmount" value="{{ old('RecoverableAmount') }}" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Remarks</label>
            <input name="Remarks" value="{{ old('Remarks') }}" class="form-control form-control-sm">
          </div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.acc.impair.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
