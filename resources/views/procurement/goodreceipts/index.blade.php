@extends('layouts.app')
@section('title', 'Goods Receipt')
@section('content')
<!-- GRNIndex.html -->
<div class="container mt-4">
  <h4 class="mb-3">📑 GRN Listing – Goods Receipt Notes</h4>

  <!-- Search/Filter -->
  <div class="row mb-3">
    <div class="col-md-3">
      <input type="text" class="form-control" placeholder="Search by GRN No / PO No">
    </div>
    <div class="col-md-3">
      <select class="form-select">
        <option>Filter by Status</option>
        <option>Draft</option>
        <option>Posted</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Search</button>
    </div>
    <div class="col-md-4 text-end">
      <a href="{{ route('procurementreceipts.create') }}" class="btn btn-success">+ New GRN</a>
    </div>
  </div>

  <!-- GRN Table -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>GRN No</th>
          <th>PO No</th>
          <th>Supplier</th>
          <th>Date</th>
          <th>Status</th>
          <th>Received By</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($goodsReceipts as $key => $receipt)
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $receipt->GRNID }}</td>
            <td>{{ $receipt->POID }}</td>
            <td>{{ $receipt->supplier->name ?? 'N/A' }}</td>
            <td>{{ $receipt->ReceivedDate}}</td>
            <td>
              <span class="badge bg-{{ $receipt->InspectionStatus == 'Posted' ? 'success' : 'warning' }}">
                {{ $receipt->InspectionStatus }}
              </span>
            </td>
            <td>{{ $receipt->ReceivedBy }}</td>
            <td>
              <a href="#">View</a> |
              <a href="#">Edit</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection