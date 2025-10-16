{{-- resources/views/assets/acc/improv/create.blade.php --}}
@extends('layouts.app')
@section('title','New Improvement')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm">
    <div class="card-header bg-light py-2 px-3"><h6 class="mb-0 text-muted">Record Improvement</h6></div>
    <div class="card-body p-3">
      @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
      <form method="post" action="{{ route('assets.acc.improv.store') }}">
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
            <label class="form-label">Document No.</label>
            <input name="DocNo" value="{{ old('DocNo') }}" class="form-control form-control-sm">
          </div>
          <div class="col-md-3">
            <label class="form-label">Document Date</label>
            <input type="date" name="DocDate" value="{{ old('DocDate') }}" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Description</label>
            <input name="Description" value="{{ old('Description') }}" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Amount</label>
            <input type="number" step="0.01" name="Amount" value="{{ old('Amount') }}" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Treatment</label>
            <select name="Treatment" class="form-select form-select-sm" required>
              @foreach(['CAPEX','EXP'] as $t)<option value="{{ $t }}" @selected(old('Treatment')==$t)>{{ $t }}</option>@endforeach
            </select>
          </div>
        </div>
        <div class="mt-3">
          <a href="{{ route('assets.acc.improv.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
