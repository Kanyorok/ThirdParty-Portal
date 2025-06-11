@extends('layouts.app')
@section('title', 'Assign Evaluation Sections')
@section('content')

 
 <!-- Items Table --> 
<div class="card shadow-sm mb-4">
<div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
<h5 class="card-title mb-3">📦 Sections</h5>
        <h4></h4>
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSectionModal">
    + Add section
</button>
    </div>
<div class="table-responsive">
<table class="table table-bordered table-striped1 align-middle">
<thead class="table-light">
<tr>
<th>#</th>
<th>Section Name</th>
<th>Description</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
    @foreach ($sections as $index => $item)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $item->SectionName }}</td>
        <td>{{ $item->Description }}</td>
        <td>
            <button 
                class="btn btn-sm btn-outline-primary"
                data-bs-toggle="modal" 
                data-bs-target="#editSectionModal"
                data-id="{{ $item->id }}"
                data-name="{{ $item->SectionName }}"
                data-desc="{{ $item->Description }}"
            >
                <i class="fas fa-edit"></i>
            </button>

            <button 
                class="btn btn-sm btn-outline-danger"
                data-bs-toggle="modal"
                data-bs-target="#deleteSectionModal"
                data-id="{{ $item->id }}"
                data-name="{{ $item->SectionName }}"
            >
                <i class="fas fa-trash-alt"></i>
            </button>

            <a href="{{ route('criterias.show', $item->id) }}" class="btn btn-sm btn-outline-success">
                View Criterias
            </a>
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
<!-- Edit Section Modal -->
<div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-3 shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="editSectionLabel">Edit Section</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editSectionForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Section Name</label>
            <input type="text" id="editSectionName" name="name" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="editSectionDesc" name="desc" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"
                  onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">
            Update Section
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Delete Section Modal -->
<div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-labelledby="deleteSectionLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-3 shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteSectionLabel">Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" id="deleteSectionForm">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete <strong id="sectionToDelete"></strong>?</p>
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
                <h5 class="modal-title" id="addItemModalLabel">Add New Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('sections.store')}}" method="POST">
                @csrf
                @method('POST')

                <div class="modal-body">
                  <div class="mb-3">
                        <label class="form-label">Section Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Technical, Financial, Legal">
                        @error('name')
                        <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                  </div>
                  <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" name="desc" placeholder="Describe the purpose of this section"></textarea>
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
                        Save Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
 <script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editSectionModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const desc = button.getAttribute('data-desc');

        editModal.querySelector('#editSectionName').value = name;
        editModal.querySelector('#editSectionDesc').value = desc;

        const form = editModal.querySelector('#editSectionForm');
        form.action = `/procurement/sections/${id}`;
    });

    const deleteModal = document.getElementById('deleteSectionModal');
    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');

        const form = deleteModal.querySelector('#deleteSectionForm');
        form.action = `/procurement/sections/${id}`;

        const nameHolder = deleteModal.querySelector('#sectionToDelete');
        nameHolder.textContent = name;
    });
});
</script>

@endsection
