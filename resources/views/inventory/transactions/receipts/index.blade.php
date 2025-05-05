@extends('layouts.app')
@section('title', 'View Transactions')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>Transactions View</h4>
      <a href={{ route('receipts.create') }} class="btn btn-success">➕ New Receipt</a>
    </div>

    <div class="mb-3">
      <input type="text" class="form-control" placeholder="🔍 Search by Supplier, PO Number, or Date">
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Receipt Date</th>
            <th>Supplier</th>
            <th>PO Number</th>
            <th>Received By</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>2025-05-02</td>
            <td>ABC Suppliers Ltd.</td>
            <td>PO-45678</td>
            <td>Daniel Mbugua</td>
            <td><span class="badge bg-success">Received</span></td>
            <td>
              <a href="#" class="btn btn-sm btn-primary">🔍 View</a>
              <a href="#" class="btn btn-sm btn-secondary">✏️ Edit</a>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>2025-04-29</td>
            <td>Global Stationers</td>
            <td>PO-12345</td>
            <td>Jane Njeri</td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
            <td>
              <a href="#" class="btn btn-sm btn-primary">🔍 View</a>
              <a href="#" class="btn btn-sm btn-secondary">✏️ Edit</a>
            </td>
          </tr>
          <!-- Additional rows as needed -->
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection