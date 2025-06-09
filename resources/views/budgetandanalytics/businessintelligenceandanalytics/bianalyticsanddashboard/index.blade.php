@extends('layouts.app')
@section('title', 'Business Intelligence & Deep Analytics')
@section('content')
<div class="card p-4">
  <h4 class="mb-4">📊 Business Intelligence & Deep Analytics</h4>
  <p class="text-muted">Quickly view key insights. Use filters to refresh the summaries. Click a tile to explore more details.</p>

  <div class="row mb-4">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select">
        <option>YTD 2025</option>
        <option>Q1 2025</option>
        <option>Q2 2025</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select">
        <option>All Branches</option>
        <option>Central Branch</option>
        <option>West Branch</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Product</label>
      <select class="form-select">
        <option>All Products</option>
        <option>Loans</option>
        <option>Deposits</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Officer</label>
      <select class="form-select">
        <option>All Officers</option>
        <option>Moses Kariuki</option>
        <option>Janet Chebet</option>
      </select>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center h-100">
        <h6>📈 KPI Dashboards</h6>
        <p class="text-muted small">Track by branch/officer/product</p>
        <p><strong>Loan Growth:</strong> 13.5% <span class="badge bg-warning text-dark">⚠</span></p>
        <p><strong>NPL Ratio:</strong> 6.1% <span class="badge bg-danger">🔺</span></p>
        <a href="{{ route('kpidashboards.index') }}" class="btn btn-outline-primary w-100">🔍 View Dashboard</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center h-100">
        <h6>📉 Trend & Growth Analysis</h6>
        <p class="text-muted small">Loan YoY: +12% | Deposit YoY: +9%</p>
        <p><strong>Trend Status:</strong> Stable <span class="badge bg-success">✔</span></p>
        <a href="{{ route('trendsdashboards.index') }}" class="btn btn-outline-success w-100">📊 Explore Trends</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center h-100">
        <h6>⚖️ Regulatory Compliance</h6>
        <p class="text-muted small">NPL: 6.1% | CBK Cap: ≤5%</p>
        <p><strong>Liquidity:</strong> 28% <span class="badge bg-success">✓ Compliant</span></p>
        <a href="{{ route('regulatoryratios.index') }}" class="btn btn-outline-danger w-100">🧾 View Compliance</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center h-100">
        <h6>💰 Top Contributors</h6>
        <p class="text-muted small">Top Depositor: KES 13M</p>
        <p><strong>Top Borrower:</strong> KES 24M</p>
        <a href="{{ route('topcontributors.index') }}" class="btn btn-outline-warning w-100">💼 Show Reports</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center h-100">
        <h6>🧩 Multi-Dimensional Analytics</h6>
        <p class="text-muted small">Sliced by: Branch, Product, GL</p>
        <p><strong>Revenue by Branch:</strong> KES 88M</p>
        <a href="{{ route('multidimensional.index') }}" class="btn btn-outline-secondary w-100">🔬 Open Cube View</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center h-100">
        <h6>📤 Data Export Tools</h6>
        <p class="text-muted small">PDF / Excel / CSV available</p>
        <p><strong>Last Export:</strong> 2 days ago</p>
        <a href="{{ route('analyticsdataexport.index') }}" class="btn btn-outline-dark w-100">⬇ Download</a>
      </div>
    </div>
  </div>
</div>
@endsection
