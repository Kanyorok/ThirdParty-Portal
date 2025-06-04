@extends('layouts.app')
@section('title', 'CBS GL Accounts')
@section('content')
<div class="card mb-4">
  <div class="card-header bg-dark text-white">
    🧾 CBS GL Accounts
  </div>
  <div class="card-body">
    <p class="text-muted">Below is a list of General Ledger accounts synced from Core Banking System (CBS). You can monitor mapping status to budget lines and products.</p>

    <table class="table table-bordered table-striped table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>GL Account No</th>
          <th>GL Name</th>
          <th>Description</th>
          <th>GL Type</th>
          <th>Mapped to Budget Line</th>
          <th>Mapped to Product</th>
          <th>Active</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>GL1001</td>
          <td>Interest Income</td>
          <td>Income from all loan products</td>
          <td>Income</td>
          <td><span class="badge bg-success">✅ Yes</span></td>
          <td><span class="badge bg-success">✅ Yes</span></td>
          <td><span class="badge bg-success">✔</span></td>
          <td>
            <button class="btn btn-sm btn-info">🔍 View</button>
            <button class="btn btn-sm btn-outline-primary">🔗 Map</button>
          </td>
        </tr>
        <tr>
          <td>2</td>
          <td>GL2005</td>
          <td>Deposit Account Balance</td>
          <td>Customer deposit liabilities</td>
          <td>Liability</td>
          <td><span class="badge bg-danger">❌ No</span></td>
          <td><span class="badge bg-warning text-dark">⚠ Partial</span></td>
          <td><span class="badge bg-success">✔</span></td>
          <td>
            <button class="btn btn-sm btn-info">🔍 View</button>
            <button class="btn btn-sm btn-outline-primary">🔗 Map</button>
          </td>
        </tr>
        <!-- Repeat rows as needed -->
      </tbody>
    </table>
  </div>
</div>
@endsection
