@extends('layouts.app')
@section('title', 'Policy Types')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🏷️ Policy Types</h4>

    <!-- Add Policy Type Modal Trigger -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPolicyTypeModal">
        ➕ Add New Policy Type
    </button>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Policy Type Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- Example Static Rows --}}
            <tr>
                <td>1</td>
                <td>Life Insurance</td>
                <td>Protects against death risk</td>
                <td><span class="badge bg-success">Active</span></td>
                <td><button class="btn btn-sm btn-secondary">Edit</button></td>
            </tr>
            <tr>
                <td>2</td>
                <td>Fire Insurance</td>
                <td>Protects properties against fire risk</td>
                <td><span class="badge bg-success">Active</span></td>
                <td><button class="btn btn-sm btn-secondary">Edit</button></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="addPolicyTypeModal" tabindex="-1" aria-labelledby="addPolicyTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Add New Policy Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Policy Type Name</label>
                        <input type="text" name="PolicyTypeName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="Status" class="form-select">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Type</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
