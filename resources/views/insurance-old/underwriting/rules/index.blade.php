@extends('layouts.app')
@section('title', 'Underwriting Rules Setup')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🔧 Underwriting Rules</h4>

    <!-- Add Rule Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addRuleModal">
        ➕ Add New Rule
    </button>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Rule Type</th>
                <th>Applicable Product</th>
                <th>Criteria</th>
                <th>Value</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {{-- Static Rows Example --}}
            <tr>
                <td>1</td>
                <td>Age Limit</td>
                <td>Credit Life</td>
                <td>Max Age</td>
                <td>65</td>
                <td><span class="badge bg-success">Yes</span></td>
                <td><button class="btn btn-sm btn-secondary">Edit</button></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal for Adding Rule -->
<div class="modal fade" id="addRuleModal" tabindex="-1" aria-labelledby="addRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Add New Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rule Type</label>
                        <select name="RuleType" class="form-select" required>
                            <option value="Age Limit">Age Limit</option>
                            <option value="Exclusion">Exclusion</option>
                            <option value="Coverage Limit">Coverage Limit</option>
                            <option value="Document Requirement">Document Requirement</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="ProductID" class="form-select" required>
                            <option value="">-- Select Product --</option>
                            <option value="1">Credit Life</option>
                            <option value="2">Fire Cover</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Criteria</label>
                        <input type="text" name="Criteria" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Value</label>
                        <input type="text" name="Value" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Is Active?</label>
                        <select name="IsActive" class="form-select">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Rule</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
