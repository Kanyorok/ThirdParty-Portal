@extends('layouts.app')
@section('title', 'Data Export Tools')
@section('content')
@stack('scripts')
<div class="card p-4">
  <h5>📤 Data Export Tools</h5>
  <p class="text-muted">Select the data type, filters, and format for export.</p>

  <!-- Filter Row -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select" id="exportPeriod">
        <option>Q1 2025</option>
        <option>Q2 2025</option>
        <option>YTD 2025</option>
        <option>2024</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select" id="exportBranch">
        <option>All</option>
        <option>Central</option>
        <option>West</option>
        <option>North</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Department</label>
      <select class="form-select" id="exportDepartment">
        <option>All</option>
        <option>Finance</option>
        <option>Credit</option>
        <option>Operations</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Data Type</label>
      <select class="form-select" id="dataType">
        <option value="budget_lines">Budget Lines</option>
        <option value="driver_projections">Driver Projections</option>
        <option value="top_down_allocation">Top-Down Allocation</option>
        <option value="kpi_dashboards">KPI Dashboards</option>
        <option value="budget_variance">Budget Variance</option>
        <option value="scenario_versions">Scenario Versions</option>
        <option value="loan_deposit_trends">Loan & Deposit Trends</option>
      </select>
    </div>
  </div>

  <!-- Format Row -->
  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">Export Format</label>
      <select class="form-select" id="exportFormat">
        <option value="excel">Excel</option>
        <option value="csv">CSV</option>
        <option value="pdf">PDF</option>
      </select>
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button class="btn btn-success w-100" onclick="exportData()">📁 Export Data</button>
    </div>
  </div>

  <!-- Status -->
  <div id="exportStatus" class="text-success fw-bold mt-3" style="display:none;">
    ✅ Export request sent successfully. Your file will download shortly.
  </div>
</div>


@endsection

<!-- Export Script -->
@push('scripts')
<script>
  function exportData() {
    const dataType = document.getElementById("dataType").value;
    const format = document.getElementById("exportFormat").value;
    const period = document.getElementById("exportPeriod").value;
    const branch = document.getElementById("exportBranch").value;
    const department = document.getElementById("exportDepartment").value;

    // Simulated export logic – in actual app, call Laravel route or trigger download
    console.log(`Exporting ${dataType} for ${period}, ${branch}, ${department} as ${format}`);
    document.getElementById("exportStatus").style.display = "block";
  }
</script>
@endpush