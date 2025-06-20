@extends('layouts.app')
@section('title', 'Bid Responsiveness Overview')
@section('content')
<div class="container mt-4">
<h4 class="mb-4">📑 Bid Responsiveness Overview</h4>

    <!-- Filters -->
    <form class="row g-3 mb-3">
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Tenders</option>
                <option>TENDER/ICT/2025/004</option>
                <option>TENDER/HR/2025/002</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option selected>All Responsiveness</option>
                <option value="1">Responsive</option>
                <option value="0">Non-Responsive</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" placeholder="Search Bidder Name">
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>#</th>
                            <th>Bidder Name</th>
                            <th>Tender Ref</th>
                            <th>Submission Date</th>
                            <th>Mandatory Docs</th>
                            <th>Eligibility</th>
                            <th>Timely?</th>
                            <th>Status</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Row -->
                        <tr>
                            <td>1</td>
                            <td>CompTech Solutions Ltd</td>
                            <td>TENDER/ICT/2025/004</td>
                            <td>2025-05-01</td>
                            <td>✅</td>
                            <td>✅</td>
                            <td>✅</td>
                            <td><span class="badge bg-success">Responsive</span></td>
                            <td>All requirements met</td>
                            <td>
                                <a href="{{ route('bidresponsiveness.create') }}"  class="btn btn-sm btn-outline-secondary">Review</a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>NetWave Technologies</td>
                            <td>TENDER/ICT/2025/004</td>
                            <td>2025-05-01</td>
                            <td>❌</td>
                            <td>✅</td>
                            <td>✅</td>
                            <td><span class="badge bg-danger">Non-Responsive</span></td>
                            <td>Missing tax clearance certificate</td>
                            <td>
                                <a href="{{ route('bidresponsiveness.create') }}"  class="btn btn-sm btn-outline-secondary">Review</a>
                            </td>
                        </tr>
                        <!-- Additional rows dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection