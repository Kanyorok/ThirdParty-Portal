@extends('layouts.app')

@section('title', 'Create Receipt')

@section('content')
<div class="card mb-4">
  <div class="card-header bg-success text-white">📥 Post Goods Receipt</div>
  <div class="card-body">
<form method="POST" action="{{ route('transactionsreceipts.store') }}" id="transferForm">

      @csrf

      {{-- Display Success Message --}}
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      {{-- Display Validation Errors --}}
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="row mb-3">
        <div class="col">
          <label class="form-label">Transfer Ref</label>
          <select id="transferId" name="TransferID" class="form-select" required>
            <option value="">Select Transfer</option>
            @foreach($transfers as $transfer)
              <option value="{{ $transfer->Id }}">{{ $transfer->TransferID }}</option>
            @endforeach
          </select>
        </div>
        <div class="col">
          <label class="form-label">Received By</label>
          <input type="text" name="ReceivedBy" class="form-control" required>
        </div>
        <div class="col">
          <label class="form-label">Receive Date</label>
          <input type="date" name="ReceivedDate" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Items Received</label>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Product</th>
              <th>Dispatched Qty</th>
              <th>Qty Received</th>
              <th>Qty Damaged</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody id="itemsTableBody">
          </tbody>
        </table>
      </div>

      <div class="mb-3">
        <label class="form-label">General Remarks</label>
        <textarea name="GeneralRemarks" class="form-control"></textarea>
      </div>

      <button type="submit" class="btn btn-success">Post GRN</button>
    </form>
  </div>
</div>

<script>
 document.getElementById('transferId').addEventListener('change', function() {
    let transferId = this.value; // Get selected value
    if (!transferId) return; // Prevent empty requests

    fetch(`/inventory/transactionsreceipts/${transferId}`)
        .then(response => response.json())
        .then(data => {
            let tableBody = document.getElementById('itemsTableBody');
            tableBody.innerHTML = "";

            data.items.forEach((item, index) => {
                let row = `<tr>
                    <td>${item.item?.ItemName ?? 'N/A'}</td>
                    <td><input type="number" name="items[${index}][dispatched_qty]" class="form-control" value="${item.DispatchedQty}" readonly></td>
                    <td><input type="number" name="items[${index}][received_qty]" class="form-control" min="0"></td>
                    <td><input type="number" name="items[${index}][damaged_qty]" class="form-control" min="0"></td>
                    <td><input type="text" name="items[${index}][remarks]" class="form-control" readonly></td>
                </tr>`;
                tableBody.innerHTML += row;
            });
        })
        .catch(error => console.error("Error fetching transfer data:", error));
});

</script>

@endsection
