@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📑 Evaluation Criteria Templates</h4>
        <a href="{{ route('evaluationcriteria.create') }}" class="btn btn-sm btn-success">+ New Criteria</a>
    </div>

    <!-- Optional Filter -->
    <div class="row mb-3">
        <div class="col-md-3">
            <select class="form-select">
                <option selected>Filter by Type</option>
                <option>Tender</option>
                <option>RFQ</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>Filter by Section</option>
                <option>Technical</option>
                <option>Financial</option>
                <option>Legal</option>
            </select>
        </div>
    </div>

    <!-- Index Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Criteria No</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Section</th>
                    <th>Total Items</th>
                    <th>Total Weight</th>
                    <th>Max Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example Row -->
                <tr>
                    <td>1</td>
                    <td>CRIT-2025-001</td>
                    <td><span class="badge bg-info">Tender</span></td>
                    <td>ICT Equipment</td>
                    <td>Technical</td>
                    <td>5</td>
                    <td>100</td>
                    <td>50</td>
                    <td>
                        <a href="/evaluation-criteria/view/1" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="/evaluation-criteria/edit/1" class="btn btn-sm btn-outline-success">Edit</a>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>CRIT-2025-002</td>
                    <td><span class="badge bg-warning">RFQ</span></td>
                    <td>General Supplies</td>
                    <td>Technical</td>
                    <td>3</td>
                    <td>60</td>
                    <td>30</td>
                    <td>
                        <a href="/evaluation-criteria/view/2" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="/evaluation-criteria/edit/2" class="btn btn-sm btn-outline-success">Edit</a>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>
                <!-- More rows dynamically -->
            </tbody>
        </table>
    </div>
</div>
@endsection
