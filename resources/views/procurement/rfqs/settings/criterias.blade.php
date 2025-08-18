@extends('layouts.app')
@section('title', 'RFQ Criteria')
@section('content')

    <!-- Criteria Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-3">📦 RFQ Criteria</h5>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#addCriteriaModal">
                    + Add Criteria
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped1 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Criteria Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($criterias as $item)
                        <tr>
                            <td>{{$loop->index+1}}</td>
                            <td>{{$item->CriteriaName}}</td>
                            <td>{{$item->Description}}</td>
                            <td>
                                <a
                                    href="#"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCriteriaModal"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->CriteriaName }}"
                                    data-desc="{{ $item->Description }}"
                                >
                                    <i class="fas fa-edit"></i>
                                </a>

                                <button
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteCriteriaModal"
                                    data-id="{{ $item->id }}"
                                    data-name="{{ $item->CriteriaName }}"
                                    title="Delete"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>

                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Criteria Modal -->
    <div class="modal fade" id="addCriteriaModal" tabindex="-1" aria-labelledby="addCriteriaModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCriteriaModalLabel">Add New RFQ Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('rfqsettingcriterias.store') }}" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="section_id" value="{{ $sectionID }}">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Criteria Name</label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="e.g. Experience, Compliance">
                            @error('name')
                            <div class="text-danger mt-2">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="desc" class="form-control" rows="2"
                                      placeholder="Optional description"></textarea>
                            @error('desc')
                            <div class="text-danger mt-2">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Criteria Modal -->
    <div class="modal fade" id="editCriteriaModal" tabindex="-1" aria-labelledby="editCriteriaModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCriteriaModalLabel">Edit RFQ Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST" id="editCriteriaForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section_id" value="{{ $sectionID }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Criteria Name</label>
                            <input type="text" id="editCriteriaName" name="name" class="form-control">
                            @error('name')
                            <div class="text-danger mt-2">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="editCriteriaDesc" name="desc" class="form-control" rows="2"></textarea>
                            @error('desc')
                            <div class="text-danger mt-2">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Criteria Modal -->
    <div class="modal fade" id="deleteCriteriaModal" tabindex="-1" aria-labelledby="deleteCriteriaModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCriteriaModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form method="POST" id="deleteCriteriaForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong id="criteriaToDelete"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const editModal = document.getElementById('editCriteriaModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const desc = button.getAttribute('data-desc');

                editModal.querySelector('#editCriteriaName').value = name;
                editModal.querySelector('#editCriteriaDesc').value = desc;

                const form = editModal.querySelector('#editCriteriaForm');
                form.action = `/procurement/rfq/criterias/${id}`;
            });

            const deleteModal = document.getElementById('deleteCriteriaModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                const form = deleteModal.querySelector('#deleteCriteriaForm');
                form.action = `/procurement/rfq/criterias/${id}`;

                deleteModal.querySelector('#criteriaToDelete').textContent = name;
            });
        </script>
    @endpush

@endsection
