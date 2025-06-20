@extends('layouts.app')

@section('title', 'Create Receipt')

@section('content')
<div class="card mb-4">
  <div class="card-header bg-success text-white">📥 Post Goods Receipt</div>
  <div class="card-body">
    <form method="POST" action="{{ route('transactionsreceipts.store') }}" id="transferForm">
      @csrf
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
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
              <option value="{{ $transfer->Id }}" {{ old('TransferID') == $transfer->Id ? 'selected' : '' }}>
                {{ $transfer->TransferID }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col">
          <label class="form-label">Received By</label>
          <input type="text" name="ReceivedBy" class="form-control" value="{{ old('ReceivedBy') }}" required>
        </div>
        <div class="col">
          <label class="form-label">Receive Date</label>
          <input type="date" name="ReceivedDate" class="form-control" value="{{ old('ReceivedDate', date('Y-m-d')) }}" required>
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
              <th>Discrepancy</th> 
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
        <textarea name="GeneralRemarks" class="form-control">{{ old('GeneralRemarks') }}</textarea>
      </div>

      <button type="submit" class="btn btn-success">Post Receipt</button>
    </form>
  </div>
</div>

<script>
  document.getElementById('transferId').addEventListener('change', function () {
    const transferId = this.value;
    if (!transferId) return;

    fetch(`/inventory/transactionsreceipts/transfer-items/${transferId}`)
      .then(response => response.json())
      .then(data => {
        const tableBody = document.getElementById('itemsTableBody');
        tableBody.innerHTML = "";

        data.items.forEach((item, index) => {
          const dispatchedQty = item.DispatchedQty ?? 0;

          const row = `
            <tr>
              <td>
                ${item.item?.ItemName ?? 'N/A'}
                <input type="hidden" name="items[${index}][item]" value="${item.Item ?? item.item?.Id ?? ''}">
              </td>
              <td>
                <input type="number" name="items[${index}][dispatched_qty]" class="form-control dispatched-qty" value="${dispatchedQty}" readonly>
              </td>
              <td>
                <input type="number" name="items[${index}][received_qty]" class="form-control received-qty" min="0" value="${dispatchedQty}">
              </td>
              <td>
                <input type="number" name="items[${index}][discrepancy]" class="form-control discrepancy" value="0" readonly>
              </td>
              <td>
                <input type="number" name="items[${index}][damaged_qty]" class="form-control" min="0" value="0">
              </td>
              <td>
                <input type="text" name="items[${index}][remarks]" class="form-control">
              </td>
            </tr>
          `;
          tableBody.innerHTML += row;
        });
      })
      .catch(error => {
        console.error("Error fetching transfer data:", error);
      });
  });

  // Recalculate discrepancy dynamically when user changes received qty
  document.addEventListener('input', function (event) {
    if (event.target.classList.contains('received-qty')) {
      const row = event.target.closest('tr');
      const dispatchedInput = row.querySelector('.dispatched-qty');
      const discrepancyInput = row.querySelector('.discrepancy');

      const dispatched = parseFloat(dispatchedInput.value) || 0;
      const received = parseFloat(event.target.value) || 0;
      const discrepancy = dispatched - received;

      discrepancyInput.value = discrepancy;
    }
  });
</script>
@endsection
