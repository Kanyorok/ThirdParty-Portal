@extends('layouts.app')
@section('title', 'Item Type List')
@section('content')


<div class="container mt-5">
  <div class="card shadow rounded-4">
    <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center" style="background-color: #add8e6;">
      <h4 class="mb-0">Item Type</h4>
      <a href="{{ route('itemtype.create') }}" class="btn btn-success">➕ Add New Item Type</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="itemtypeTable" class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Type Name</th>
              <th>Stock Tracked?</th>
              <th>Requires Tagging?</th>
              <th>Is Active?</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($itemtypes as $key => $itemtype)
              <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $itemtype->TypeName }}</td>
                <td>{!! $itemtype->StockTracked ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                <td>{!! $itemtype->RequiresTagging ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                <td>{!! $itemtype->Active ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                <td>
                  <a href="javascript:void(0);" class="btn btn-info btn-sm"
                     onclick="showItemModal('{{ $itemtype->TypeName }}', '{{ $itemtype->StockTracked }}', '{{ $itemtype->RequiresTagging ? 1 : 0 }}', {{ $itemtype->Active ? 1 : 0 }})">Show</a>
                  <a href="javascript:void(0);" class="btn btn-warning btn-sm"
                     onclick="editItemModal('{{ route('itemtype.update', $itemtype->Id) }}', '{{ $itemtype->TypeName }}', '{{ $itemtype->StockTracked }}', '{{ $itemtype->RequiresTagging ? 1 : 0 }}', {{ $itemtype->Active ? 1 : 0 }})">Edit</a>
                  <a href="#" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $itemtype->Id }}')">Delete</a>
                  <form id="delete-form-{{ $itemtype->Id }}" action="{{ route('itemtype.destroy', $itemtype->Id) }}" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Show Modal -->
<div class="modal fade" id="showItemModal" tabindex="-1" aria-labelledby="showItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="showItemModalLabel">Item Type Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Type Name:</strong> <span id="showTypeName"></span></p>
        <p><strong>Stock Tracked:</strong> <span id="showStockTracked"></span></p>
        <p><strong>Requires Tagging:</strong> <span id="showRequiresTagging"></span></p>
        <p><strong>Active:</strong> <span id="showActive"></span></p>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editItemForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editItemModalLabel">Edit Item Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="editTypeName" class="form-label">Type Name</label>
            <input type="text" class="form-control" id="editTypeName" name="TypeName" required>
          </div>
          <div class="form-check mb-3">
            <input type="hidden" name="StockTracked" value="0">
            <input class="form-check-input" type="checkbox" id="StockTracked" name="StockTracked" value="1">
            <label class="form-check-label" for="StockTracked">Stock Tracked</label>
          </div>
          <div class="form-check mb-3">
            <input type="hidden" name="RequiresTagging" value="0">
            <input class="form-check-input" type="checkbox" id="editRequiresTagging" name="RequiresTagging" value="1">
            <label class="form-check-label" for="editRequiresTagging">Requires Tagging</label>
          </div>
          <div class="form-check mb-3">
            <input type="hidden" name="Active" value="0">
            <input class="form-check-input" type="checkbox" id="editActive" name="Active" value="1">
            <label class="form-check-label" for="editActive">Active</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function showItemModal(typename, stocktracked, requirestagging, active) {
    document.getElementById('showTypeName').textContent = typename;
    document.getElementById('showStockTracked').textContent = stocktracked == 1 ? 'Yes' : 'No';
    document.getElementById('showRequiresTagging').textContent = requirestagging == 1 ? 'Yes' : 'No';
    document.getElementById('showActive').textContent = active == 1 ? 'Active' : 'Inactive';
    new bootstrap.Modal(document.getElementById('showItemModal')).show();
}

function editItemModal(action, typename, stocktracked, requirestagging, active) {
    document.getElementById('editItemForm').action = action;
    document.getElementById('editTypeName').value = typename;
    document.getElementById('StockTracked').checked = stocktracked == 1;
    document.getElementById('editRequiresTagging').checked = requirestagging == 1;
    document.getElementById('editActive').checked = active == 1;
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

function confirmDelete(Id) {
    if (confirm('⚠️ Are you sure you want to delete this unit?')) {
        document.getElementById('delete-form-' + Id).submit();
    }
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#itemtypeTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>

@endsection
