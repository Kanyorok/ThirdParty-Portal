{{-- @extends('layouts.app')
@section('title', 'Tenant Ledger Statement')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📒 Tenant Ledger Statement</h4>

  <!-- Tenant Filter -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <label class="form-label">Select Tenant</label>
      <select class="form-select">
        <option selected>Moses K. (Lease #L-2025-001)</option>
        <option>Acme Ltd. (Lease #L-2025-002)</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">From Date</label>
      <input type="date" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">To Date</label>
      <input type="date" class="form-control">
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button class="btn btn-primary w-100">🔍 View Statement</button>
    </div>
  </div>

  <!-- Ledger Table -->
  <table class="table table-bordered align-middle mt-3">
    <thead class="table-light">
      <tr>
        <th>Date</th>
        <th>Reference</th>
        <th>Description</th>
        <th class="text-end">Debit (KES)</th>
        <th class="text-end">Credit (KES)</th>
        <th class="text-end">Balance</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>2025-05-01</td>
        <td>INV-2025-0001</td>
        <td>Rent Invoice - May 2025</td>
        <td class="text-end">26,500</td>
        <td class="text-end">-</td>
        <td class="text-end">26,500</td>
      </tr>
      <tr>
        <td>2025-05-02</td>
        <td>RCPT-2025-0031</td>
        <td>Payment - MPESA12345</td>
        <td class="text-end">-</td>
        <td class="text-end">10,000</td>
        <td class="text-end">16,500</td>
      </tr>
      <tr>
        <td>2025-05-05</td>
        <td>RCPT-2025-0032</td>
        <td>Payment - MPESA87654</td>
        <td class="text-end">-</td>
        <td class="text-end">16,500</td>
        <td class="text-end">0</td>
      </tr>
    </tbody>
    <tfoot class="table-light">
      <tr>
        <th colspan="3" class="text-end">Total</th>
        <th class="text-end">26,500</th>
        <th class="text-end">26,500</th>
        <th class="text-end">0</th>
      </tr>
    </tfoot>
  </table>

  <div class="text-end mt-3">
    <button class="btn btn-outline-secondary">🖨️ Print Statement</button>
    <button class="btn btn-outline-primary">⬇ Export to PDF</button>
  </div>
</div>
@endsection --}}
