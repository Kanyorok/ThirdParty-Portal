@extends('layouts.app')
@section('title', 'Segment Configuration')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🧩 Segment Configuration</h4>

    <!-- Add Segment Type -->
    <div class="card mb-4">
        <div class="card-header">Add Segment Type</div>
        <div class="card-body">
            <form method="POST" action="#">
                <div class="mb-3">
                    <label class="form-label">Segment Code</label>
                    <input type="text" name="SegmentCode" class="form-control" placeholder="e.g., BRANCH" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Segment Name</label>
                    <input type="text" name="SegmentName" class="form-control" placeholder="e.g., Branch" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Segment</button>
            </form>
        </div>
    </div>

    <!-- Segment Values -->
    <div class="card">
        <div class="card-header">Segment Values</div>
        <div class="card-body">
            <form method="POST" action="#">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Segment</label>
                        <select name="SegmentID" class="form-select">
                            <option value="1">BRANCH - Branch</option>
                            <option value="2">DEPT - Department</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Segment Value Code</label>
                        <input type="text" name="SegmentValueCode" class="form-control" placeholder="e.g., 001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Segment Value Name</label>
                        <input type="text" name="SegmentValueName" class="form-control" placeholder="e.g., HQ">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Add Segment Value</button>
            </form>

            <!-- Segment Value List -->
            <hr>
            <h6 class="mt-4">Existing Segment Values</h6>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Segment</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>BRANCH</td>
                        <td>001</td>
                        <td>HQ</td>
                        <td><span class="badge bg-success">Active</span></td>
                    </tr>
                    <tr>
                        <td>DEPT</td>
                        <td>100</td>
                        <td>Finance</td>
                        <td><span class="badge bg-success">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection