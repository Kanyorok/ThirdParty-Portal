@extends('layouts.app')
@section('title', 'View Transfers')
@section('content')
 <div class="card mb-4">
    <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('transactionstransfers.create') }}" class="btn btn-success">➕ New Transfer</a>    
  </div>
  <div class="card-header bg-light">📤 Stock Transfers Out</div>
  <div class="card-body">
    <table class="table table-bordered table-striped">
      <thead class="table-secondary">
        <tr>
          <th>#</th>
          <th>Requisition Ref</th>
          <th>From</th>
          <th>To</th>
          <th>Dispatch Date</th>
          <th>Status</th>
          <th>Dispatched By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>REQ/2025/007</td>
          <td>Warehouse A</td>
          <td>Branch X</td>
          <td>2025-06-11</td>
          <td>In Transit</td>
          <td>John M.</td>
          <td>
            <a href="#" class="btn btn-sm btn-info">View</a>
            <a href="#" class="btn btn-sm btn-secondary">Cancel</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>


@endSection