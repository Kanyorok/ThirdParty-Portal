@extends('layouts.app')
@section('title', 'Add Budget Line Entry')
@section('content')

<div class="card mt-4">
  <div class="card-header bg-info text-white">➕ Add Budget Line Entry</div>
  <div class="card-body">
    <form>
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Budget Period</label>
          <select class="form-select">
            <option selected>FY2025-Q1</option>
            <option>FY2025-Q2</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Branch</label>
          <select class="form-select">
            <option selected>Main Branch</option>
            <option>Westlands Branch</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Budget Line</label>
        <select class="form-select">
          <option>Salaries – Staff Costs</option>
          <option>Marketing Expense</option>
          <option>Loan Interest Income</option>
          <option>Non Funded Income</option>
          <option>Fixed Deposit Interest Expense</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Entry Method</label>
        <select class="form-select" id="entryMethodSelect">
          <option selected>Manual</option>
          <option>Driver-Based</option>
          <option>Activity-Driven</option>
        </select>
      </div>

      <!-- Manual Entry -->
      <div class="manual-entry">
        <div class="mb-3">
          <label class="form-label">Amount</label>
          <input type="number" step="0.01" class="form-control" placeholder="e.g. 500000">
        </div>
      </div>

      <!-- Driver-Based Entry -->
      <div class="driver-entry" style="display: none;">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Product</label>
            <select class="form-select">
              <option selected>Consumer Loan</option>
              <option>Agri Loan</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Driver KPI Type</label>
            <select class="form-select">
              <option selected>Loan</option>
              <option>Deposit</option>
            </select>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Projected Amount (KPI)</label>
            <input type="text" class="form-control" readonly value="10,000,000">
          </div>
          <div class="col-md-6">
            <label class="form-label">Applied Rate (%)</label>
            <input type="text" class="form-control" readonly value="11.00%">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Computed Budget Value</label>
          <input type="text" class="form-control" readonly value="1,100,000">
        </div>
      </div>

      <!-- Activity-Driven Entry -->
      <div class="activity-entry" style="display: none;">
        <label class="form-label">Activity Breakdown</label>
        <table class="table table-bordered">
          <thead class="table-light">
            <tr>
              <th>Activity</th>
              <th>Unit</th>
              <th>Quantity</th>
              <th>Unit Cost</th>
              <th>Total</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="activityRows">
            <tr>
              <td><input type="text" class="form-control" placeholder="e.g. Radio Ads"></td>
              <td><input type="text" class="form-control" placeholder="e.g. Slot"></td>
              <td><input type="number" class="form-control qty" value="1"></td>
              <td><input type="number" class="form-control unit-cost" value="100000"></td>
              <td><input type="text" class="form-control total" readonly value="100000"></td>
              <td><button type="button" class="btn btn-danger btn-sm remove-row">❌</button></td>
            </tr>
          </tbody>
        </table>
        <button type="button" class="btn btn-secondary btn-sm" onclick="addActivityRow()">➕ Add Activity</button>
        <div class="mt-3">
          <strong>Total Activity Budget: </strong> <span id="activityTotal">100000</span> KES
        </div>
      </div>

      <div class="mb-3 mt-3">
        <label class="form-label">Remarks (optional)</label>
        <textarea class="form-control" rows="2"></textarea>
      </div>

      <button type="submit" class="btn btn-success">💾 Save Budget Line</button>
    </form>
  </div>
</div>

<script>
  const methodSelect = document.getElementById('entryMethodSelect');
  const manualEntry = document.querySelector('.manual-entry');
  const driverEntry = document.querySelector('.driver-entry');
  const activityEntry = document.querySelector('.activity-entry');

  methodSelect.addEventListener('change', function () {
    manualEntry.style.display = this.value === 'Manual' ? 'block' : 'none';
    driverEntry.style.display = this.value === 'Driver-Based' ? 'block' : 'none';
    activityEntry.style.display = this.value === 'Activity-Driven' ? 'block' : 'none';
  });

  function addActivityRow() {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td><input type="text" class="form-control" placeholder="e.g. Roadshow"></td>
      <td><input type="text" class="form-control" placeholder="e.g. Day"></td>
      <td><input type="number" class="form-control qty" value="1"></td>
      <td><input type="number" class="form-control unit-cost" value="50000"></td>
      <td><input type="text" class="form-control total" readonly value="50000"></td>
      <td><button type="button" class="btn btn-danger btn-sm remove-row">❌</button></td>
    `;
    document.getElementById('activityRows').appendChild(row);
    attachActivityRowEvents();
    calculateActivityTotal();
  }

  function attachActivityRowEvents() {
    document.querySelectorAll('.qty, .unit-cost').forEach(input => {
      input.addEventListener('input', calculateActivityTotal);
    });
    document.querySelectorAll('.remove-row').forEach(button => {
      button.addEventListener('click', function () {
        this.closest('tr').remove();
        calculateActivityTotal();
      });
    });
  }

  function calculateActivityTotal() {
    let total = 0;
    document.querySelectorAll('#activityRows tr').forEach(row => {
      const qty = parseFloat(row.querySelector('.qty').value) || 0;
      const unitCost = parseFloat(row.querySelector('.unit-cost').value) || 0;
      const rowTotal = qty * unitCost;
      row.querySelector('.total').value = rowTotal.toFixed(2);
      total += rowTotal;
    });
    document.getElementById('activityTotal').textContent = total.toLocaleString();
  }

  document.addEventListener('DOMContentLoaded', () => {
    attachActivityRowEvents();
  });
</script>

@endsection
