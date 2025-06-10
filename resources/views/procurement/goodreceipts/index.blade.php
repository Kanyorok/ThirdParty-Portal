@extends('layouts.app')
@section('title', 'Goods Receipt')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">📑 GRN Listing – Goods Receipt Notes</h4>
    <a href="{{ route('procurementreceipts.create') }}" class="btn btn-success">
      + New GRN
    </a>
  </div>
  <div class="table-responsive">
      <table id="goodsreceipt" class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>GRN No</th>
          <th>PO No</th>
          <th>Supplier</th>
          <th>Date</th>
          <th>Status</th>
          <th>Received By</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($goodsReceipts as $key => $receipt)
          <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $receipt->GRNID }}</td>
            <td>{{ $receipt->POID }}</td>
              <td>{{ $receipt->supplier->SupplierName ?? 'N/A' }}</td>
              <td>{{ \Carbon\Carbon::parse($receipt->ReceivedDate)->format('d M Y') }}</td>
            <td>
              <span class="badge bg-{{ $receipt->InspectionStatus->badgeColor() }}">
                {{ $receipt->InspectionStatus->label() }}
              </span>
            </td>
              <td>{{ $receipt->receiver->Name ?? 'N/A' }}</td>
            <td>
                <a class="btn btn-sm btn-outline-primary" href="javascript:void(0);"
                   onclick="openEditModal('{{ $receipt->GRNID }}', '{{ $receipt->POID }}')">Edit</a>
                <a class="btn btn-sm btn-outline-success" href="javascript:void(0);"
                   onclick="postReceipt('{{ $receipt->GRNID }}', '{{ $receipt->POID }}')">Post</a>
                <a class="btn btn-sm btn-outline-danger" href="javascript:void(0);"
                   onclick="confirmDelete('{{ $receipt->GRNID }}', '{{ $receipt->POID }}')">Delete</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editGRNModal" tabindex="-1" aria-labelledby="editGRNLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <form method="POST" action="{{ route('procurementreceipts.updateLine') }}">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit GRN Line Items</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="lineItemsContainer"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save All Changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- delete form --}}
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>


<script>
function confirmDelete(grnId, poId) {
  if (confirm(`Are you sure you want to delete all items under GRN ${grnId} and PO ${poId}?`)) {
    const form = document.getElementById('deleteForm');
      form.action = `/procurement/procurementreceipts/delete/${grnId}/${poId}`;
    form.submit();
  }
}
</script>


{{-- Editing Receipt --}}
<script>
    function openEditModal(grnId, poId) {
        const url = `{{ url('/procurement/procurementreceipts/lines') }}/${grnId}/${poId}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const container = document.getElementById('lineItemsContainer');
                container.innerHTML = '';

                data.forEach((item, index) => {
                    container.innerHTML += `
            <div class="row g-2 mb-2 border p-2">
              <input type="hidden" name="items[${index}][id]" value="${item.id}">
              <div class="col-md-3">
                <label>Item No</label>
                <input type="text" value="${item.ItemNo}" class="form-control" readonly>
              </div>
              <div class="col-md-3">
                <label>Received Qty</label>
                <input type="number" step="0.01" name="items[${index}][ReceivedQTY]" value="${item.ReceivedQTY}" class="form-control">
              </div>
              <div class="col-md-3">
                <label>Transfer To</label>
                <input type="text" name="items[${index}][TransferTo]" value="${item.TransferTo ?? ''}" class="form-control">
              </div>
              <div class="col-md-3">
                <label>Tag Required</label>
                <select name="items[${index}][TagRequired]" class="form-select">
                  <option value="1" ${item.TagRequired == 1 ? 'selected' : ''}>Yes</option>
                  <option value="0" ${item.TagRequired == 0 ? 'selected' : ''}>No</option>
                </select>
              </div>
            </div>
          `;
                });

                new bootstrap.Modal(document.getElementById('editGRNModal')).show();
            })
            .catch(error => {
                alert('Failed to load GRN lines.');
                console.error('Fetch error:', error);
            });
    }
</script>

{{-- Posting Receipt --}}
<script>
    function postReceipt(grnId, poId) {
        if (!confirm('Are you sure you want to post this GRN?')) return;

        fetch('{{ route('procurementreceipts.post') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                grn_id: grnId,
                po_id: poId
            })
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message || 'GRN posted.');
                location.reload(); // Optional: reload the page to reflect changes
            })
            .catch(error => {
                alert('Error posting GRN.');
                console.error(error);
    });
}
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#goodsreceipt').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
