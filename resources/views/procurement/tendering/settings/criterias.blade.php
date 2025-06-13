@extends('layouts.app')
@section('title', 'Technical Criteria')
@section('content')

    <!-- Items Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-3">📦 Criteria</h5>
                <h4></h4>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#addSectionModal">
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
                    <tfoot class="table-light fw-bold text-end">
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <!-- Edit Criteria Modal -->
    <div class="modal fade" id="editCriteriaModal" tabindex="-1" aria-labelledby="editCriteriaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCriteriaLabel">Edit Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="editCriteriaForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section_id" value="{{ $sectionID }}">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Criteria Name</label>
                            <input type="text" id="editCriteriaName" name="name" class="form-control">
                            @error('name')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editCriteriaDesc" name="desc" rows="2"></textarea>
                            @error('desc')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button
                            type="submit"
                            class="btn btn-primary"
                            onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();"
                        >
                            Update Criteria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Criteria Modal -->
    <div class="modal fade" id="deleteCriteriaModal" tabindex="-1" aria-labelledby="deleteCriteriaLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCriteriaLabel">Confirm Delete</h5>
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


    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{route('criterias.store')}}" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="section_id" value="{{ $sectionID }}">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Criteria Name</label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="e.g. Experience, Compliance, Methodology">
                            @error('name')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="desc" rows="2"
                                      placeholder="Describe the purpose of this criteria"></textarea>
                            @error('desc')
                            <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button
                            type="submit"
                            class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"
                        >
                            Save Criteria
                        </button>
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
                form.action = `/procurement/criterias/${id}`;
            });
        </script>
    @endpush
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('deleteCriteriaModal');
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                const form = deleteModal.querySelector('#deleteCriteriaForm');
                form.action = `/procurement/criterias/${id}`;

                const nameHolder = deleteModal.querySelector('#criteriaToDelete');
                nameHolder.textContent = name;
            });
        });
    </script>


@endsection
