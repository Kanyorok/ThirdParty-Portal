@extends('layouts.app')

@section('title', 'View UOM Conversion')

@section('content')
<div class="container mt-4">
  <div class="card shadow">
    <div class="card-header bg-light fw-bold">UOM Conversion Details</div>
    <div class="card-body">
      
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label fw-bold">Item</label>
          <p class="form-control-plaintext">
            {{ $uomConversion->item->ItemCode ?? 'N/A' }} - {{ $uomConversion->item->ItemName ?? 'N/A' }}
          </p>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold">Base UOM</label>
          <p class="form-control-plaintext">
            {{ $uomConversion->uom->Name ?? 'N/A' }}
          </p>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold">Alternate UOM</label>
          <p class="form-control-plaintext">
            {{ $uomConversion->alternateUom->Name ?? 'N/A' }}
          </p>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label fw-bold">Conversion Factor</label>
          <p class="form-control-plaintext">{{ $uomConversion->ConversionFactor }}</p>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-bold">Remarks</label>
          <p class="form-control-plaintext">{{ $uomConversion->Remarks ?? '-' }}</p>
        </div>
          <div class="mt-4">
                <a href="{{ route('uomconversion.edit', $uomConversion->Id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('uomconversion.index') }}" class="btn btn-secondary">Back</a>
            </div>
    </div>
  </div>
</div>
@endsection
