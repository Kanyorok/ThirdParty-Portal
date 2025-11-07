@extends('layouts.app')
@section('title', 'Evaluation Criteria Setup')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📊 Tender Evaluation Criteria Management</h4>

    <!-- Header Controls -->
    <div class="mb-3 d-flex gap-2">
        <button class="btn btn-outline-primary">New Criteria</button>
        <button class="btn btn-outline-secondary">Edit</button>
    </div>

    <!-- Criteria Setup Form -->
    <form>
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="criteriaNo" class="form-label">Criteria No</label>
                <input type="text" class="form-control" id="criteriaNo" placeholder="AutoGenerate" readonly>
            </div>
            <div class="col-md-3">
                <label for="criteriaType" class="form-label">Type</label>
                <select class="form-select" id="criteriaType">
                    <option>RFQ</option>
                    <option>Tender</option>
                </select>
            </div>
        </div>

        <hr>

        <!-- Add Criteria -->
        <h6 class="fw-bold">➕ Criteria List</h6>
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="category" class="form-label">Category (Optional)</label>
                <select class="form-select" id="category">
                    <option selected disabled>Select Item Category</option>
                    <option>ICT Equipment</option>
                    <option>Construction</option>
                    <option>General Supplies</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="section" class="form-label">Section</label>
                <select class="form-select" id="section">
                    <option>Technical</option>
                    <option>Financial</option>
                    <option>Legal</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="weight" class="form-label">Weight</label>
                <input type="number" class="form-control" id="weight">
            </div>
            <div class="col-md-2">
                <label for="maxScore" class="form-label">Max Score</label>
                <input type="number" class="form-control" id="maxScore">
            </div>
        </div>

        <div class="mb-3">
            <label for="criteriaDescription" class="form-label">Criteria Description</label>
            <input type="text" class="form-control" id="criteriaDescription" placeholder="e.g., Delivery Time and Schedule Commitment">
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="reset" class="btn btn-secondary">Cancel</button>
        </div>
    </form>

    <!-- Evaluation Criteria Table -->
    <h6 class="fw-bold">🗂️ Current Criteria Entries</h6>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Section</th>
                    <th>Criteria Description</th>
                    <th>Weight</th>
                    <th>Max Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Technical</td>
                    <td>Compliance with Specification</td>
                    <td>30</td>
                    <td>10</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>Technical</td>
                    <td>Delivery Time and Schedule Commitment</td>
                    <td>20</td>
                    <td>10</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
                    </td>
                </tr>
                <!-- Add additional criteria here -->
            </tbody>
        </table>
    </div>
</div>
@endsection
