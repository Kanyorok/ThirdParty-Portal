@extends('layouts.app')
@section('title','New Reallocation')
@section('content')

<div class="card mt-4">
  <div class="card-header bg-primary text-white">➕ New Budget Reallocation</div>
  <div class="card-body">
    <form method="POST" action="{{ route('budgetandanalytics.reallocation.store') }}">
      @csrf

      <!-- Budget & Type -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Budget</label>
          <select name="BudgetID" class="form-select" required>
            <option value="">-- Select Budget --</option>
            @foreach($budgets as $budget)
              <option value="{{ $budget->Id }}">{{ $budget->Name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Reallocation Type</label>
          <select name="ReallocationType" id="reallocationType" class="form-select" required>
            <option value="">-- Choose Type --</option>
            <option value="Branch">Within Branch</option>
            <option value="Department">Within Department</option>
            <option value="Cross-Department">Across Departments</option>
          </select>
        </div>
      </div>

      <!-- Branch -->
      <div class="row mb-3" id="branchSection" style="display:none;">
        <div class="col-md-6">
          <label class="form-label">Branch</label>
          <select name="BranchID" id="branchSelect" class="form-select">
            <option value="">-- Select Branch --</option>
            @foreach($branches as $branch)
              <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- From Department -->
      <div class="row mb-3" id="departmentSection" style="display:none;">
        <div class="col-md-6">
          <label class="form-label">From Department</label>
          <select name="DepartmentID" id="departmentSelect" class="form-select">
            <option value="">-- Select Department --</option>
            @foreach($departments as $dept)
              <option value="{{ $dept->Id }}">{{ $dept->Name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- To Department (for cross-department only) -->
      <div class="row mb-3" id="toDepartmentSection" style="display:none;">
        <div class="col-md-6">
          <label class="form-label">To Department</label>
          <select name="ToDepartmentID" id="toDepartmentSelect" class="form-select">
            <option value="">-- Select Department --</option>
            @foreach($departments as $dept)
              <option value="{{ $dept->Id }}">{{ $dept->Name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- From & To Lines -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">From Budget Line</label>
          <select name="FromBudgetLineID" id="fromLine" class="form-select" required>
            <option value="">-- Select Line --</option>
            @foreach($lines as $line)
              <option value="{{ $line->Id }}">{{ $line->LineName }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">To Budget Line</label>
          <select name="ToBudgetLineID" id="toLine" class="form-select" required>
            <option value="">-- Select Line --</option>
            @foreach($lines as $line)
              <option value="{{ $line->Id }}">{{ $line->LineName }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <!-- Dynamic Details -->
      <div id="lineDetailsSection" class="mb-3"></div>

      <!-- Amount & Justification -->
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Amount</label>
          <input type="number" step="0.01" name="Amount" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Justification</label>
          <textarea name="Justification" class="form-control" rows="1" required></textarea>
        </div>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Submit Reallocation</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Toggle branch/department fields
  document.getElementById('reallocationType').addEventListener('change', function() {
    let type = this.value;
    document.getElementById('branchSection').style.display = 'none';
    document.getElementById('departmentSection').style.display = 'none';
    document.getElementById('toDepartmentSection').style.display = 'none';

    if (type === 'Branch') {
      document.getElementById('branchSection').style.display = 'flex';
    } else if (type === 'Department') {
      document.getElementById('departmentSection').style.display = 'flex';
    } else if (type === 'Cross-Department') {
      document.getElementById('departmentSection').style.display = 'flex';
      document.getElementById('toDepartmentSection').style.display = 'flex';
    }
  });

  // Fetch budget lines dynamically when department changes
  document.getElementById('departmentSelect').addEventListener('change', function() {
    let deptId = this.value;
    if (deptId) {
      fetch(`/budgetandanalytics/reallocation/budget-lines/${deptId}`)
        .then(res => res.json())
        .then(data => {
          let fromSelect = document.getElementById('fromLine');
          fromSelect.innerHTML = '<option value="">-- Select Line --</option>';
          data.forEach(line => {
            fromSelect.innerHTML += `<option value="${line.Id}">${line.LineName}</option>`;
          });
        });
    }
  });

  // Fetch budget lines dynamically for To Department (Cross-Department)
  document.getElementById('toDepartmentSelect').addEventListener('change', function() {
    let deptId = this.value;
    if (deptId) {
      fetch(`/budgetandanalytics/reallocation/budget-lines/${deptId}`)
        .then(res => res.json())
        .then(data => {
          let toSelect = document.getElementById('toLine');
          toSelect.innerHTML = '<option value="">-- Select Line --</option>';
          data.forEach(line => {
            toSelect.innerHTML += `<option value="${line.Id}">${line.LineName}</option>`;
          });
        });
    }
  });

  // Fetch details for selected line
  document.getElementById('fromLine').addEventListener('change', function() {
    let lineId = this.value;
    let branchId = document.querySelector('[name="BranchID"]')?.value || '';
    if (lineId) {
      fetch(`/budgetandanalytics/reallocation/budget-line/${lineId}/details/${branchId}`)
        .then(res => res.json())
        .then(data => {
          let html = '';
          if (data.type === 'activity') {
            html = `<h6>Activity Driven Line</h6>
                    <table class="table table-bordered table-sm">
                      <thead><tr><th>Activity</th><th>Description</th><th>Allocations</th></tr></thead>
                      <tbody>`;
            data.activities.forEach(a => {
              let allocHtml = '<ul>';
              a.monthly_allocations.forEach(m => {
                allocHtml += `<li>Month ${m.month}: ${m.amount}</li>`;
              });
              allocHtml += '</ul>';
              html += `<tr><td>${a.name}</td><td>${a.description || ''}</td><td>${allocHtml}</td></tr>`;
            });
            html += '</tbody></table>';
          } else if (data.type === 'manual') {
            html = `<h6>Manual Allocations</h6>
                    <table class="table table-bordered table-sm">
                      <thead><tr><th>Month</th><th>Allocation</th></tr></thead>
                      <tbody>`;
            data.entries.forEach(e => {
              e.monthly_allocations.forEach(m => {
                html += `<tr><td>${m.month}</td><td>${m.amount}</td></tr>`;
              });
            });
            html += '</tbody></table>';
          }
          document.getElementById('lineDetailsSection').innerHTML = html;
        });
    }
  });
</script>
@endsection
