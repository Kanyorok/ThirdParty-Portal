@extends('layouts.app')
@section('title', 'Item Type List')
@section('styles')
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
@section('content')

<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
             style="background-color: #add8e6;">
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
                        @foreach($itemTypes as $key => $itemtype)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $itemtype->type->Description ?? 'N/A' }}</td>
                                <td>{!! $itemtype->StockTracked ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                                <td>{!! $itemtype->RequiresTagging ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                                <td>{!! $itemtype->Active ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        {{-- View --}}
                                        <button type="button" class="btn btn-sm btn-info"
                                            onclick="showItemModal('{{ $itemtype->type->Description ?? 'N/A' }}', '{{ $itemtype->StockTracked ? 'Yes' : 'No' }}', '{{ $itemtype->RequiresTagging ? 'Yes' : 'No' }}', '{{ $itemtype->Active ? 'Yes' : 'No' }}')"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        {{-- Edit --}}
                                        <button type="button" class="btn btn-sm btn-warning"
                                            onclick="editItemModal('{{ route('itemtype.update', $itemtype->Id) }}', '{{ $itemtype->TypeName }}', {{ $itemtype->StockTracked ? 1 : 0 }}, {{ $itemtype->RequiresTagging ? 1 : 0 }}, {{ $itemtype->Active ? 1 : 0 }})"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Delete --}}
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('{{ $itemtype->Id }}')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <form id="delete-form-{{ $itemtype->Id }}"
                                              action="{{ route('itemtype.destroy', $itemtype->Id) }}"
                                              method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
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
                        <select class="form-select" id="editTypeName" name="TypeName" required>
                            <option value="">Select Item Type</option>
                            @foreach($itmTypes as $itmType)
                                <option value="{{ $itmType->ID }}">{{ $itmType->Description ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="StockTracked" value="0">
                        <input class="form-check-input" type="checkbox" id="editStockTracked" name="StockTracked" value="1">
                        <label class="form-check-label" for="editStockTracked">Stock Tracked</label>
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

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    // View Modal
    function showItemModal(typeName, stockTracked, requiresTagging, active) {
        document.getElementById('showTypeName').textContent = typeName;
        document.getElementById('showStockTracked').textContent = stockTracked;
        document.getElementById('showRequiresTagging').textContent = requiresTagging;
        document.getElementById('showActive').textContent = active;

        new bootstrap.Modal(document.getElementById('showItemModal')).show();
    }

    // Edit Modal
    function editItemModal(action, typeId, stockTracked, requiresTagging, active) {
        const form = document.getElementById('editItemForm');
        const typeSelect = document.getElementById('editTypeName');

        form.action = action;
        typeSelect.value = typeId;
        document.getElementById('editStockTracked').checked = stockTracked == 1;
        document.getElementById('editRequiresTagging').checked = requiresTagging == 1;
        document.getElementById('editActive').checked = active == 1;

        new bootstrap.Modal(document.getElementById('editItemModal')).show();
    }

    // Delete Confirmation
    function confirmDelete(Id) {
        if (confirm('⚠️ Are you sure you want to delete this item type?')) {
            document.getElementById('delete-form-' + Id).submit();
        }
    }

    // Initialize DataTable
    $(document).ready(function () {
        $('#itemtypeTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: { emptyTable: "No item types found" }
        });
    });
</script>
@endsection
