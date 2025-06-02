@extends('layouts.app')
@section('title', 'Raise Need')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-4">📂 My Department's Procurement Needs</h4>
    <a href="{{ route('procurementdepartmentalplan.create') }}" class="btn btn-success">+ Add Need</a>
    </div>

    <table id="raisedneeds" class="table table-bordered table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Quantity</th>
        <th>Est. Cost</th>
        <th>Status</th>
        <th>Required By</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @forelse ($departmentneedviews as $index => $departmentneedview)
      <tr>
        <td>{{ $index + 1 ?? 'N/A'}}</td>
        <td>{{ $departmentneedview->item->ItemName ?? 'N/A' }}</td>
        <td>{{ $departmentneedview->item->Category->Name ?? 'N/A' }}</td>
        <td>{{ $departmentneedview->RequestedQty ?? 'N/A' }}</td>
        <td>{{ $departmentneedview->EstimatedUnitCost ?? 'N/A' }}</td>
        <td>{{ $departmentneedview->Status->label() ?? 'N/A' }}</td>
        <td>{{ $departmentneedview->creator->Name ?? 'N/A' }}</td>
        <td>
            <button onclick="openEditModal('{{ $departmentneedview->NeedID }}')" class="btn btn-sm btn-outline-primary">
                Edit
            </button>

            <form action="{{ route('procurementdepartmentalplan.destroy', $departmentneedview->NeedID) }}" method="POST"
                  class="d-inline" onsubmit="return confirm('Are you sure you want to delete this need?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
        </td>
      </tr>
    @empty

    @endforelse
    </tbody>
  </table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editNeedsModal" tabindex="-1" aria-labelledby="editNeedsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="{{ route('procurementdepartmentalplan.updateLine') }}">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department Need Lines</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="lineItemsContainer"></div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for fetching and populating modal -->
<script>
    function openEditModal(NeedID) {
        const fetchLineRouteTemplate = @json(route('procurementdepartmentalplan.view', ['NeedID' => 'REPLACE_ID']));
        const fetchUrl = fetchLineRouteTemplate.replace('REPLACE_ID', NeedID);
        fetch(fetchUrl)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('lineItemsContainer');
                container.innerHTML = '';

                data.forEach((need, index) => {
                    container.innerHTML += `
            <div class="row g-3 mb-3 border p-3 rounded shadow-sm bg-light" id="needRow-${index}">

              <div class="col-md-3">
                <label class="form-label">Need Id</label>
                <input type="text" class="form-control" name="Needs[${index}][NeedID]" value="${need.NeedID ?? 'Unknown'}" readonly>
              </div>

              <div class="col-md-3">
                <label class="form-label">Item</label>
                <input type="text" class="form-control" value="${need.ItemID ?? 'Unknown'}" readonly>
              </div>

              <div class="col-md-3">
                <label class="form-label">Requested Quantity</label>
                <input type="number" step="0.01" name="Needs[${index}][RequestedQty]" value="${need.RequestedQty ?? 0}" class="form-control">
              </div>

              <div class="col-md-3">
                <label class="form-label">Estimated Unit Cost</label>
                <input type="text" name="Needs[${index}][EstimatedUnitCost]" value="${need.EstimatedUnitCost ?? ''}" class="form-control">
              </div>

              <div class="col-md-2">
                <label class="form-label">Fiscal Year</label>
                <input type="text" name="Needs[${index}][FiscalYear]" value="${need.FiscalYear ?? ''}" class="form-control">
              </div>
            </div>
          `;
                });

                new bootstrap.Modal(document.getElementById('editNeedsModal')).show();
            })
            .catch(error => {
                console.error('Error loading need lines:', error);
                alert('Could not load department need lines.');
            });
    }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
  $(document).ready(function () {
    $('#raisedneeds').DataTable({
      pageLength: 10,
      ordering: true,
      searching: true,
      lengthChange: true,
    });
  });
</script>
@endsection
