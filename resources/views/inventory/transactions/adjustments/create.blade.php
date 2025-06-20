@extends('layouts.app')
@section('title', 'Stock Adjustment')
@section('content')
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">Stock Adjustment Form</h4>

    <form method="POST" action="{{ route('transactionsadjustment.store') }}">
        @csrf
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="adjustmentDate" class="form-label">Adjustment Date</label>
            <input type="date" class="form-control" id="adjustmentDate" name="AdjustmentDate" required>
        </div>
        <div class="col-md-4">

            <label for="branch" class="form-label">Branch</label>
            <select class="form-select" id="branch" name="Branch" required>
                <option selected disabled>Select Branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label for="reason" class="form-label">Adjustment Reason</label>
            <select class="form-select" id="reason" name="Reason" required>
            <option>Damage</option>
            <option>Expired</option>
            <option>Shrinkage</option>
            <option>Stock Found</option>
            <option>Other</option>
          </select>
        </div>
      </div>

      <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Item Code</th>
              <th>Item Name</th>
                <th>Current Qty</th>
              <th>Adjustment Qty</th>
                <th>New Qty</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
          <!-- Populated by AJAX -->
          </tbody>
        </table>
      </div>

      <div class="mb-3">
        <label for="adjustedBy" class="form-label">Adjusted By</label>
          <input type="text" class="form-control" id="adjustedBy" name="AdjustedBy" placeholder="e.g. Daniel Mbugua"
                 required>
      </div>

      <button type="submit" class="btn btn-primary">✅ Submit Adjustment</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('branch').addEventListener('change', function () {
        const branchId = this.value;
        if (!branchId) return;

        fetch(`/inventory/branch-stock/${branchId}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = '';
                data.forEach((stock, index) => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <input type="text" class="form-control" value="${stock.item?.ItemCode ?? ''}" readonly>
                                <input type="hidden" name="items[${index}][Item]" value="${stock.ItemID}">
                            </td>
                            <td>
                                <input type="text" class="form-control" value="${stock.item?.ItemName ?? ''}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control" value="${stock.CurrentQty}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control adjustment-qty"
                                       name="items[${index}][AdjustmentQty]"
                                       placeholder="+/-"
                                       onchange="calculateNewQty(this)">
                            </td>
                            <td>
                                <input type="number" class="form-control new-qty" >
                            </td>

                            <td>
                                <input type="text" class="form-control" name="items[${index}][Remarks]" placeholder="Optional remarks">
                            </td>
                        </tr>
                    `;
                });
            });
    });

    function calculateNewQty(input) {
        const row = input.closest('tr');
        const currentQty = parseFloat(row.querySelector('td:nth-child(4) input').value) || 0;
        const adjustmentQty = parseFloat(input.value) || 0;
        const newQty = currentQty + adjustmentQty;
        row.querySelector('.new-qty').value = newQty;
    }
</script>
  @endSection
