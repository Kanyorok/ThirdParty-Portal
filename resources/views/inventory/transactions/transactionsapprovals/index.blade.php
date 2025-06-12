@extends('layouts.app')
@section('title', 'Approve Stock Transactions')
@section('content')

<div class="card mb-4">
  <div class="card-header bg-primary text-white">✅ Approve Stock Transactions</div>
  <div class="card-body">

    <!-- Filters -->
    <form class="mb-4">
      <div class="row">
        <div class="col-md-3">
          <label class="form-label">Transaction Type</label>
          <select class="form-select">
            <option>All</option>
            <option>Stock Transfer</option>
            <option>Stock Issue</option>
            <option>Stock Adjustment</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Branch</label>
          <select class="form-select">
            <option>All Branches</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">From Date</label>
          <input type="date" class="form-control" />
        </div>
        <div class="col-md-3">
          <label class="form-label">To Date</label>
          <input type="date" class="form-control" />
        </div>
      </div>
      <button class="btn btn-secondary mt-3">🔍 Filter</button>
    </form>

    <!-- Transactions Table -->
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Type</th>
          <th>Ref No</th>
          <th>Branch</th>
          <th>Date</th>
          <th>Initiated By</th>
          <th>Status</th>
          <th>Approve</th>
          <th>Reject</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Stock Issue</td>
          <td>ISS/2025/010</td>
          <td>Branch A</td>
          <td>2025-06-11</td>
          <td>John M.</td>
          <td>Pending</td>
          <td><button class="btn btn-success btn-sm">Approve</button></td>
          <td>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectModalLabel">Reject Transaction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Reason for Rejection</label>
        <textarea class="form-control" rows="3" required></textarea>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Submit Rejection</button>
      </div>
    </form>
  </div>
</div>

@endsection