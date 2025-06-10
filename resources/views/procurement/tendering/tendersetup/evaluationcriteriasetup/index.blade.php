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
                    <tr>
                        <td>1</td>
                        <td>Technical</td>
                        <td>Assesses technical capacity and qualifications</td>
                        <td>
                            <a href="{{route('tender-criteria.index')}}" class="btn btn-sm btn-outline-primary">Add
                                Criteria</a>
                            <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
                        </td>
                    </tr>

                    </tbody>
                    <tfoot class="table-light fw-bold text-end">
                    </tfoot>
                </table>
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

                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Section Name</label>
                            <input type="text" class="form-control" placeholder="e.g. Technical, Financial, Legal">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="2"
                                      placeholder="Describe the purpose of this section"></textarea>
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


@endsection
