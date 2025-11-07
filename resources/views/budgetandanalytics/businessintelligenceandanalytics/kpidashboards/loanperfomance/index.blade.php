@extends('layouts.app')
@section('title', 'Loan Book Performance')
@section('content')
    <div class="card p-4">
        <h5>📘 Loan Book Performance</h5>
        <p class="text-muted">Analyze NPL ratio, performing loans, and classifications as per CBK prudential
            guidelines.</p>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select class="form-select">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Group By</label>
                <select class="form-select">
                    <option>Branch</option>
                    <option>Officer</option>
                    <option>Product</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Loan Type</label>
                <select class="form-select">
                    <option>All</option>
                    <option>Personal Loan</option>
                    <option>SME Loan</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <canvas id="nplPerformanceChart" height="220"></canvas>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Entity</th>
                    <th>Total Loans (KES)</th>
                    <th>Performing Loans (KES)</th>
                    <th>NPL (KES)</th>
                    <th>NPL Ratio</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Central Branch</td>
                    <td>150,000,000</td>
                    <td>135,000,000</td>
                    <td>15,000,000</td>
                    <td>10%</td>
                    <td><span class="badge bg-danger">🔺 Above CBK Limit</span></td>
                </tr>
                </tbody>
            </table>
        </div>

        <h6>📊 Loan Classification (CBK Guidelines)</h6>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Entity</th>
                    <th>Normal</th>
                    <th>Watch</th>
                    <th>Substandard</th>
                    <th>Doubtful</th>
                    <th>Loss</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Central Branch</td>
                    <td>100,000,000</td>
                    <td>20,000,000</td>
                    <td>10,000,000</td>
                    <td>5,000,000</td>
                    <td>15,000,000</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <button class="btn btn-outline-secondary">🔙 Back to KPI Dashboard</button>
        </div>
    </div>

@endsection
