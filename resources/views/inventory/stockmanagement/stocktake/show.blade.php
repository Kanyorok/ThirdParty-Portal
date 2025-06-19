@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
<div class="container mt-5" style="max-width: 700px;">
  <h3 class="mb-4">Patent Details</h3>
  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-4">BranchID</dt>
        <dd class="col-sm-8">{{ $stock->branch->Name }}</dd>

        <dt class="col-sm-4">StoreID</dt>
        <dd class="col-sm-8">{{ $stock->store->StoreName?? '-' }}</dd>

        <dt class="col-sm-4">CountedBy</dt>
        <dd class="col-sm-8">{{ $stock->CountedBy?? '-' }}</dd>

        <dt class="col-sm-4">Count Date</dt>
        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($stock->CountDate)->format('d/m/Y') }}</dd>

        <dt class="col-sm-4">Posted By</dt>
        <dd class="col-sm-8">{{ $stock->user?->Name ?? '-' }}</dd>

        <dt class="col-sm-4">Posted Date</dt>
        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($stock->CreatedOn)->format('d/m/Y') }}</dd>

      </dl>
    </div>
    <div class="card-footer">
      <a href="{{ route('stocktake.edit', $stock->Id) }}" class="btn btn-primary">Edit</a>
      <a href="{{ route('stocktake.index') }}" class="btn btn-secondary">Back</a>
    </div>
  </div>
</div>
@endsection
