@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
    <div class="card p-4">
        <h5>📊 Drilldown – Net Interest Margin (NIM)</h5>
        <p class="text-muted">Compare interest income vs expense and NIM across branches, officers, or products.</p>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" id="nimPeriod">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select" id="nimBranch">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Product</label>
                <select class="form-select" id="nimProduct">
                    <option>All Products</option>
                    <option>Loans</option>
                    <option>Deposits</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="applyNIMFilters()">📊 Apply Filters</button>
            </div>
        </div>

        <!-- Chart Area -->
        <div class="mb-4">
            <canvas id="nimChart" height="200"></canvas>
        </div>

        <!-- NIM Detail Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Entity</th>
                    <th>Interest Income (KES)</th>
                    <th>Interest Expense (KES)</th>
                    <th>Average Earning Assets (KES)</th>
                    <th>NIM %</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Central Branch</td>
                    <td>18,000,000</td>
                    <td>4,800,000</td>
                    <td>200,000,000</td>
                    <td>6.6%</td>
                    <td><span class="badge bg-warning text-dark">⚠ Slight Deviation</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>West Branch</td>
                    <td>12,400,000</td>
                    <td>3,200,000</td>
                    <td>150,000,000</td>
                    <td>6.1%</td>
                    <td><span class="badge bg-success">✅ On Track</span></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <button class="btn btn-outline-secondary">🔙 Back to KPI Dashboard</button>
        </div>
    </div>
@endsection
