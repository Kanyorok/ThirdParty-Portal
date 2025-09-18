@extends('layouts.app')
@section('content')
<h4 class="mb-3">New Petty Cash Float</h4>
@if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('finance.pettyfloats.store') }}">@csrf
<div class="row g-3">
  <div class="col-md-3"><label class="form-label">Code</label><input name="Code" class="form-control" required></div>
  <div class="col-md-5"><label class="form-label">Name</label><input name="Name" class="form-control" required></div>
  <div class="col-md-4">
    <label class="form-label">Currency</label>
    <select name="CurrencyID" class="form-select" required>
      <option value="">-- select --</option>
      @foreach($currencies as $c)<option value="{{ $c->Id }}">{{ $c->Code }} — {{ $c->Name }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-3"><label class="form-label">Float Limit</label><input type="number" step="0.01" name="FloatLimit" class="form-control"></div>
  <div class="col-md-3"><label class="form-label">Reorder Level</label><input type="number" step="0.01" name="ReorderLevel" class="form-control"></div>
  <div class="col-md-3"><label class="form-label">Opening Balance</label><input type="number" step="0.01" name="OpeningBalance" class="form-control" value="0"></div>
  <div class="col-md-3"><label class="form-label">Active</label><select name="IsActive" class="form-select"><option value="1">Yes</option><option value="0">No</option></select></div>
</div>
<hr class="my-4">
<button class="btn btn-success">Save</button> <a class="btn btn-secondary" href="{{ route('finance.pettyfloats.index') }}">Cancel</a>
</form>
@endsection
