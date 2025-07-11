@extends('layouts.app')
@section('title', 'Insurance Product Setup')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📦 Insurance Products</h4>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
        ➕ Add Insurance Product
    </button>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Policy Type</th>
                <th>Cover Description</th>
                <th>Premium Mode</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- Example Rows --}}
            <tr>
                <td>1</td>
                <td>Credit Life Cover</td>
                <td>Life Insurance</td>
                <td>Loan protection in case of death</td>
                <td>Flat Rate</td>
                <td><span class="badge bg-success">Active</span></td>
                <td><button class="btn btn-sm btn-secondary">Edit</button></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Add Insurance Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="ProductName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Policy Type</label>
                        <select name="PolicyTypeID" class="form-select" required>
                            <option value="">-- Select Type --</option>
                            <option value="1">Life Insurance</option>
                            <option value="2">Fire Insurance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover Description</label>
                        <textarea name="CoverDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Premium Mode</label>
                        <select name="PremiumMode" class="form-select">
                            <option value="Flat Rate">Flat Rate</option>
                            <option value="% of Sum Assured">% of Sum Assured</option>
                            <option value="Custom">Custom</option>
                        </select>
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
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
