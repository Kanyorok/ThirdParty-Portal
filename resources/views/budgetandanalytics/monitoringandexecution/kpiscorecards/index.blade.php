@extends('layouts.app')
@section('title', 'KPI Scorecard')
@section('content')
<<<<<<< HEAD
<div class="card p-4">
  <h5>📈 KPI Scorecard</h5>
  <p class="text-muted">Track key performance indicators across branches, products, and time periods. Analyze financial and operational efficiency.</p>

  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
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
        <option>Personal Loan</option>
        <option>Savings Account</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Export</label><br>
      <button class="btn btn-outline-success btn-sm">📥 Excel</button>
      <button class="btn btn-outline-danger btn-sm">📄 PDF</button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>KPI</th>
          <th>Target</th>
          <th>Actual</th>
          <th>Variance</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Loan Growth (%)</td>
          <td>15%</td>
          <td>13.2%</td>
          <td>-1.8%</td>
          <td><span class="badge bg-warning text-dark">⚠ Below Target</span></td>
        </tr>
        <tr>
          <td>Deposit Growth (%)</td>
          <td>10%</td>
          <td>12%</td>
          <td>+2%</td>
          <td><span class="badge bg-success">✅ On Track</span></td>
        </tr>
        <tr>
          <td>NPL Ratio</td>
          <td>≤ 5%</td>
          <td>6.1%</td>
          <td>+1.1%</td>
          <td><span class="badge bg-danger">🔺 Exceeded</span></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
=======
    <div class="card p-4">
        <h5>🔍 Officer-Level KPI Drilldown</h5>
        <p class="text-muted">Review how individual officers are performing against their assigned KPIs.</p>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">KPI</label>
                <input type="text" class="form-control" value="Loan Book Growth" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select class="form-select">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>Full Year</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <select class="form-select">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Officer Name</th>
                    <th>Target</th>
                    <th>Actual</th>
                    <th>Variance</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Moses Kariuki</td>
                    <td>15%</td>
                    <td>13.2%</td>
                    <td>-1.8%</td>
                    <td><span class="badge bg-warning text-dark">⚠ Below Target</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Janet Chebet</td>
                    <td>10%</td>
                    <td>11%</td>
                    <td>+1%</td>
                    <td><span class="badge bg-success">✅ On Track</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
>>>>>>> feature/newMenu-dev
@endsection
