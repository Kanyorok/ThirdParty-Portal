@extends('layouts.app')
@section('title', 'Top-Down Budget Allocation')
@section('content')
<div class="card p-4">
  <h5>🎯 Top-Down Budget Allocation</h5>
  <p class="text-muted">Define central targets per budget line and allocate to branches based on percentage weights or past performance.</p>

  <div class="mb-3">
    <label class="form-label">Budget Scenario</label>
    <select class="form-select">
      <option>Base Case</option>
      <option>Best Case</option>
      <option>Worst Case</option>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Budget Period</label>
    <select class="form-select">
      <option>2025</option>
      <option>2026</option>
    </select>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Budget Line</th>
          <th>Total Target (KES)</th>
          <th>Branch</th>
          <th>Allocation (%)</th>
          <th>Allocated Amount (KES)</th>
        </tr>
      </thead>
       <tbody id="allocationBody">
        <tr>
          <td rowspan="2">Interest Income – Loans</td>
          <td rowspan="2"><input type="number" class="form-control" value="10000000"></td>
          <td>Central Branch</td>
          <td><input type="number" class="form-control" value="60"></td>
          <td><input type="number" class="form-control" value="6000000"></td>
        </tr>
        <tr>
          <td>West Branch</td>
          <td><input type="number" class="form-control" value="40"></td>
          <td><input type="number" class="form-control" value="4000000"></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script>
  const branches = ["Central Branch", "West Branch", "North Branch"];
  let lineCount = 1;

  function addBudgetLine() {
    const tbody = document.getElementById('allocationBody');
    const lineId = `line${lineCount}`;
    const totalInputId = `total_${lineId}`;

    // Insert a row for selecting the budget line and total
    const totalRow = document.createElement("tr");
    totalRow.innerHTML = `
      <td rowspan="${branches.length}"><select class='form-select'><option>Interest Income – Loans</option><option>Fee Income</option></select></td>
      <td rowspan="${branches.length}"><input type='number' id='${totalInputId}' class='form-control' value='0' onchange='recalcAllocation("${lineId}")'></td>
      <td>${branches[0]}</td>
      <td><input type='number' class='form-control percent' data-branch='${branches[0]}' data-line='${lineId}' value='0' onchange='recalcAllocation("${lineId}")'></td>
      <td><input type='number' class='form-control amt' data-branch='${branches[0]}' data-line='${lineId}' readonly></td>
    `;
    tbody.appendChild(totalRow);

    // Insert additional rows for the other branches
    for (let i = 1; i < branches.length; i++) {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${branches[i]}</td>
        <td><input type='number' class='form-control percent' data-branch='${branches[i]}' data-line='${lineId}' value='0' onchange='recalcAllocation("${lineId}")'></td>
        <td><input type='number' class='form-control amt' data-branch='${branches[i]}' data-line='${lineId}' readonly></td>
      `;
      tbody.appendChild(row);
    }

    lineCount++;
  }

  function recalcAllocation(lineId) {
    const total = parseFloat(document.getElementById(`total_${lineId}`).value) || 0;
    const percentInputs = document.querySelectorAll(`.percent[data-line='${lineId}']`);
    percentInputs.forEach(input => {
      const percent = parseFloat(input.value) || 0;
      const branch = input.getAttribute("data-branch");
      const amtField = document.querySelector(`.amt[data-line='${lineId}'][data-branch='${branch}']`);
      amtField.value = ((percent / 100) * total).toFixed(2);
    });
  }
</script>

  <div class="mb-2 d-flex justify-content-between">
  <button onclick="addBudgetLine()" class="btn btn-secondary mt-3">➕ Add Budget Line</button>
  <button class="btn btn-primary mt-3">💾 Save Allocation</button>
  <button class="btn btn-success">✅ Finalize & Lock</button>
    
  </div>

@endsection
