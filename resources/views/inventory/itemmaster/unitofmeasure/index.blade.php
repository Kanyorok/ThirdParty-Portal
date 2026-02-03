@extends('layouts.app')
@section('title', 'Unit Of Measure (UOM) List')
@section('styles')
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
@section('content')

    <div class="container mt-5">
        <div class="card shadow rounded-4">
            <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
                 style="background-color: #add8e6;">
                <h4 class="mb-0">Unit of Measure</h4>
                <a href="{{ route('unitofmeasure.create') }}" class="btn btn-success">➕ Add New Unit of Measure</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                    <table id="unitofmeasureTable" class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>UOM Code</th>
                            <th>UOM Name</th>
                            <th>Base Unit?</th>
                            <th>Is Active?</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($units as $key => $unit)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $unit->Code }}</td>
                                <td>{{ $unit->Name }}</td>
                                <td>{!! $unit->BaseUnit ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                                <td>{!! $unit->Active ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-info"
                                           onclick="showUOMModal('{{ $unit->Code }}', '{{ $unit->Name }}', '{{ $unit->BaseUnit ? 1 : 0 }}', {{ $unit->Active ? 1 : 0 }})"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning"
                                           onclick="editUOMModal('{{ route('unitofmeasure.update', $unit->Id) }}', '{{ $unit->Code }}', '{{ $unit->Name }}', '{{ $unit->BaseUnit ? 1 : 0 }}', {{ $unit->Active ? 1 : 0 }})"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                           onclick="confirmDelete('{{ $unit->Id }}')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $unit->Id }}"
                                              action="{{ route('unitofmeasure.destroy', $unit->Id) }}" method="POST"
                                              style="display:none;">
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
    <div class="modal fade" id="showUOMModal" tabindex="-1" aria-labelledby="showUOMModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showUOMModalLabel">Unit of Measure Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Code:</strong> <span id="showCode"></span></p>
                    <p><strong>Name:</strong> <span id="showName"></span></p>
                    <p><strong>Base Unit?:</strong> <span id="showBaseUnit"></span></p>
                    <p><strong>Active?:</strong> <span id="showActive"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editUOMModal" tabindex="-1" aria-labelledby="editUOMModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editUOMForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUOMModalLabel">Edit Unit of Measure</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editCode" class="form-label">UOM Code<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCode" name="Code" required>
                        </div>
                        <div class="mb-3">
                            <label for="editName" class="form-label">UOM Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editName" name="Name" required>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="BaseUnit" value="0">
                            <input class="form-check-input" type="checkbox" id="editBaseUnit" name="BaseUnit" value="1">
                            <label class="form-check-label" for="editBaseUnit">Base Unit</label>
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
        function showUOMModal(code, name, baseUnit, active) {
            document.getElementById('showCode').textContent = code;
            document.getElementById('showName').textContent = name;
            document.getElementById('showBaseUnit').textContent = baseUnit == 1 ? 'Yes' : 'No';
            document.getElementById('showActive').textContent = active == 1 ? 'Active' : 'Inactive';
            new bootstrap.Modal(document.getElementById('showUOMModal')).show();
        }

        function editUOMModal(action, code, name, baseUnit, active) {
            document.getElementById('editUOMForm').action = action;
            document.getElementById('editCode').value = code;
            document.getElementById('editName').value = name;
            document.getElementById('editBaseUnit').checked = baseUnit == 1;
            document.getElementById('editActive').checked = active == 1;
            new bootstrap.Modal(document.getElementById('editUOMModal')).show();
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
            $('#unitofmeasureTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    emptyTable: "No units of measure found"
                }
            });
        });
    </script>
@endsection