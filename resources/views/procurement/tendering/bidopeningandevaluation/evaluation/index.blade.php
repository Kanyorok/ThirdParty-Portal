@extends('layouts.app')
@section('title', 'Bid Evaluation Scoring Form')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">🎯 Bid Evaluation Scoring Form</h4>

    <!-- Evaluator Info -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label fw-bold">Committee Member</label>
            <input type="text" class="form-control" value="Grace A." readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-bold">User ID</label>
            <input type="text" class="form-control" value="GA123" readonly>
        </div>
    </div>

    <!-- Tender Selection -->
    <div class="row mb-4">
        <div class="col-md-6">
            <label class="form-label fw-bold">Tender No</label>
            <select class="form-select">
                <option selected disabled>Select Tender</option>
                <option>TND/PROC/2025/001</option>
                <option>TND/PROC/2025/002</option>
            </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button class="btn btn-outline-secondary">Load Tender Info</button>
        </div>
    </div>

    <!-- Bidders Table -->
    <h5 class="fw-bold">📦 Bidders Summary</h5>
    <table class="table table-bordered align-middle mb-4">
        <thead class="table-light">
            <tr>
                <th>Bidders</th>
                <th>Total Quoted</th>
                <th>Delivery Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Bidder 1</td>
                <td>KES 2,500</td>
                <td>6 Days</td>
                <td><span class="badge bg-success">Submitted</span></td>
                <td><a href="#" class="btn btn-sm btn-outline-primary">View Documents</a></td>
            </tr>
            <tr>
                <td>Bidder 2</td>
                <td>KES 3,000</td>
                <td>5 Days</td>
                <td><span class="badge bg-success">Submitted</span></td>
                <td><a href="#" class="btn btn-sm btn-outline-primary">View Documents</a></td>
            </tr>
            <tr>
                <td>Bidder 3</td>
                <td>-</td>
                <td>-</td>
                <td><span class="badge bg-secondary">Open / Not Confirmed</span></td>
                <td><button class="btn btn-sm btn-outline-secondary" disabled>Pending</button></td>
            </tr>
        </tbody>
    </table>

    <!-- Scoring for Bidder 1 -->
    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">Scoring: Bidder 1</div>
        <div class="card-body">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Evaluation Criteria</th>
                        <th>Weight (%)</th>
                        <th>Score (1-10)</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Compliance with Specification</td>
                        <td>30%</td>
                        <td><input type="number" class="form-control" min="0" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Delivery Time and Schedule Commitment</td>
                        <td>20%</td>
                        <td><input type="number" class="form-control" min="0" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Relevant Experience and References</td>
                        <td>20%</td>
                        <td><input type="number" class="form-control" min="0" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Warranty Terms</td>
                        <td>10%</td>
                        <td><input type="number" class="form-control" min="0" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Financial Stability</td>
                        <td>20%</td>
                        <td><input type="number" class="form-control" min="0" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Duplicate the above card for Bidder 2 and Bidder 3 if needed -->

    <!-- Final Declaration -->
    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" id="confirmFairScoring" required>
        <label class="form-check-label" for="confirmFairScoring">
            I confirm that this scoring is done independently and fairly.
        </label>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2 mb-5">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="button" class="btn btn-secondary">Save</button>
        <button type="reset" class="btn btn-outline-dark">Cancel</button>
    </div>
</div>

@endsection