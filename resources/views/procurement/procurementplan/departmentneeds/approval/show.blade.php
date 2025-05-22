@extends('layouts.app')
@section('title', 'View Procurement Need')

@section('content')
<div class="card shadow rounded-4 p-4">
  <h3 class="mb-4 text-primary">Needs Details</h3>

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label fw-semibold">Item Name</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ $NeedsApprovalviews->item->ItemName ?? 'N/A' }}
      </div>
    </div>
    <div class="col-md-6">
      <label class="form-label fw-semibold">Category</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ $NeedsApprovalviews->item->category->Name ?? 'N/A' }}
      </div>
    </div>

    <div class="col-md-4">
      <label class="form-label fw-semibold">Quantity Needed</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ $need->RequestedQty }}
      </div>
    </div>
    <div class="col-md-4">
      <label class="form-label fw-semibold">Unit of Measure</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ $need->item->UOM ?? 'N/A' }}
      </div>
    </div>
    <div class="col-md-4">
      <label class="form-label fw-semibold">Estimated Cost</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ number_format($need->EstimatedUnitCost, 2) }}
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label fw-semibold">Required By</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ \Carbon\Carbon::parse($need->RequestedDate)->format('d M Y') }}
      </div>
    </div>
    <div class="col-md-6">
      <label class="form-label fw-semibold">Submitted On</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ \Carbon\Carbon::parse($need->CreatedOn)->format('d M Y') }}
      </div>
    </div>

    <div class="col-12">
      <label class="form-label fw-semibold">Justification</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ $need->Justification ?? 'N/A' }}
      </div>
    </div>

    <div class="col-12">
      <label class="form-label fw-semibold">Submitted By</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        {{ $need->creator->Name ?? 'N/A' }} 
        <small class="text-muted">
          ({{ $need->creator->department->Name ?? 'Unknown Department' }})
        </small>
      </div>
    </div>

    <div class="col-12">
      <label class="form-label fw-semibold">Attached Document</label>
      <div class="form-control-plaintext border rounded bg-light px-3 py-2">
        @if($need->Attachment)
          <a href="{{ asset('uploads/' . $need->Attachment) }}" target="_blank" class="text-decoration-underline">
            {{ $need->Attachment }}
          </a>
        @else
          <span class="text-muted">No document attached.</span>
        @endif
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ url('/planning/approve/' . $need->Id) }}" class="btn btn-success">
      Approve
    </a>
    <a href="{{ url('/planning/reject/' . $need->Id) }}" class="btn btn-danger">
      Reject
    </a>
    <a href="{{ route('department-need-approval.index') }}" class="btn btn-secondary">
      Back to List
    </a>
  </div>
</div>
@endsection
