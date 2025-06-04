@extends('layouts.app')
@section('title', 'Bid Scoring Consolidation')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📊 Bid Scoring Consolidation – TND/PROC/2025/001</h4>

    <!-- Tender Info Summary -->
    <div class="row mb-3">
        <div class="col-md-6">
            <strong>Item:</strong> Supply of ICT Equipment
        </div>
        <div class="col-md-6">
            <strong>Evaluators:</strong> 3 Committee Members
        </div>
    </div>

    <!-- Consolidated Scoring Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">Bidder</th>
                    <th colspan="5">Average Score per Criterion</th>
                    <th rowspan="2">Total Weighted Score (%)</th>
                    <th rowspan="2">Rank</th>
                    <th rowspan="2">Recommendation</th>
                </tr>
                <tr>
                    <th>Compliance</th>
                    <th>Delivery</th>
                    <th>Experience</th>
                    <th>Warranty</th>
                    <th>Financial</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Tech Supplies Ltd</td>
                    <td>9.0</td>
                    <td>8.5</td>
                    <td>8.0</td>
                    <td>7.0</td>
                    <td>8.5</td>
                    <td><strong>85%</strong></td>
                    <td>1</td>
                    <td><span class="badge bg-success">Recommended</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Nova Systems</td>
                    <td>8.5</td>
                    <td>7.5</td>
                    <td>7.0</td>
                    <td>6.5</td>
                    <td>7.5</td>
                    <td>76%</td>
                    <td>2</td>
                    <td><span class="badge bg-secondary">Backup</span></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>EquiBuild Ltd</td>
                    <td>7.0</td>
                    <td>6.0</td>
                    <td>6.0</td>
                    <td>5.0</td>
                    <td>6.5</td>
                    <td>63%</td>
                    <td>3</td>
                    <td><span class="badge bg-danger">Not Recommended</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2 justify-content-end mb-5">
        <button class="btn btn-outline-success">Forward for Award</button>
        <button class="btn btn-outline-danger">Reject All Bids</button>
        <button class="btn btn-outline-dark">Back</button>
    </div>
</div>
@endsection
