@extends('layouts.app')
@section('title', 'Inventory Types')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
             style="background-color: #add8e6;">
            <h4 class="mb-0">Inventory Types</h4>
            <a href="{{ route('inventorytype.create') }}" class="btn btn-success">➕ Add New Inventory Type</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="inventorytypesTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($types as $key => $type)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $type->type->Description ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $type->Status ? 'bg-success' : 'bg-warning' }}">
                                        {{ $type->Status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        {{-- View Button --}}
                                        <button type="button"
                                            class="btn btn-sm btn-info"
                                            title="View"
                                            data-bs-toggle="modal"
                                            data-bs-target="#showTypeModal"
                                            data-type="{{ $type->type->Description ?? 'N/A' }}"
                                            data-status="{{ $type->Status ? 'Active' : 'Inactive' }}">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        {{-- Edit Button --}}
                                        <button type="button"
                                            class="btn btn-sm btn-warning"
                                            onclick="editTypeModal('{{ route('inventorytype.update', $type->Id) }}', '{{ $type->Type }}', {{ $type->Status ? 1 : 0 }})"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        {{-- Delete Button --}}
                                        <button type="button"
                                            class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('{{ $type->Id }}')"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <form id="delete-form-{{ $type->Id }}"
                                              action="{{ route('inventorytype.destroy', $type->Id) }}"
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

    <!-- Show Modal -->
    <div class="modal fade" id="showTypeModal" tabindex="-1" aria-labelledby="showTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showTypeModalLabel">Inventory Type Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Type:</strong> <span id="showType"></span></p>
                    <p><strong>Status:</strong> <span id="showStatus"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editTypeModal" tabindex="-1" aria-labelledby="editTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editTypeForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTypeModalLabel">Edit Inventory Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editType" class="form-label">Inventory Type<span class="text-danger">*</span></label>
                            <select class="form-select" id="editType" name="Type" required>
                                    <option value="">--Select Inventory Type--</option>
                                    @foreach($inventoryTypes as $inventoryType)
                                        <option value="{{ $inventoryType->ID }}">
                                            {{ $inventoryType->Description }}
                                        </option>
                                    @endforeach
                                </select>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="Status" value="0">
                            <input class="form-check-input" type="checkbox" id="editStatus" name="Status" value="1">
                            <label class="form-check-label" for="editStatus">Active</label>
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
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    // Populate show modal dynamically
    const showTypeModal = document.getElementById('showTypeModal');
    showTypeModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const type = button.getAttribute('data-type');
        const status = button.getAttribute('data-status');

        document.getElementById('showType').textContent = type;
        document.getElementById('showStatus').textContent = status;
    });

    function editTypeModal(action, type, status) {
        document.getElementById('editTypeForm').action = action;
        document.getElementById('editType').value = type;
        document.getElementById('editStatus').checked = status == 1;
        new bootstrap.Modal(document.getElementById('editTypeModal')).show();
    }

    function confirmDelete(Id) {
        if (confirm('⚠️ Are you sure you want to delete this type?')) {
            document.getElementById('delete-form-' + Id).submit();
        }
    }

    $(document).ready(function () {
        $('#inventorytypesTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: { emptyTable: "No inventory types found" }
        });
    });
</script>
@endsection
