@extends('layouts.app')
@section('title','Class-Book Override')

@section('content')
@php
  $class = $classes->firstWhere('Id',$row->ClassID);
  $book  = $books->firstWhere('Id',$row->BookID);
@endphp
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 text-muted"><i class="far fa-copy me-2"></i> Class-Book Override</h6>
      <div>
        <a href="{{ route('assets.settings.class-books.edit',$row->Id) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        <a href="{{ route('assets.settings.class-books.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
      </div>
    </div>
    <div class="card-body p-3">
      <dl class="row mb-0">
        <dt class="col-md-3">Class</dt><dd class="col-md-9">{{ $class->Name ?? ('#'.$row->ClassID) }} ({{ $class->Code ?? '' }})</dd>
        <dt class="col-md-3">Book</dt><dd class="col-md-9">{{ $book->Name ?? ('#'.$row->BookID) }} ({{ $book->Code ?? '' }})</dd>
        <dt class="col-md-3">Method</dt><dd class="col-md-9">{{ $row->DepMethod ?? '—' }}</dd>
        <dt class="col-md-3">Life (months)</dt><dd class="col-md-9">{{ $row->UsefulLifeMonths ?? '—' }}</dd>
        <dt class="col-md-3">Residual %</dt><dd class="col-md-9">{{ $row->ResidualPct !== null ? number_format($row->ResidualPct,2) : '—' }}</dd>
        <dt class="col-md-3">Active</dt><dd class="col-md-9">{{ $row->IsActive ? 'Yes' : 'No' }}</dd>
      </dl>
    </div>
  </div>
</div>
@endsection
