@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
    <div class="card p-4">
        <h5>🔍 Officer-Level KPI Drilldown</h5>
        <p class="text-muted">Review how individual officers are performing against their assigned KPIs.</p>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">KPI</label>
                <input type="text" class="form-control" value="Loan Book Growth" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select class="form-select">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>Full Year</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <select class="form-select">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Officer Name</th>
                    <th>Target</th>
                    <th>Actual</th>
                    <th>Variance</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Moses Kariuki</td>
                    <td>15%</td>
                    <td>13.2%</td>
                    <td>-1.8%</td>
                    <td><span class="badge bg-warning text-dark">⚠ Below Target</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Janet Chebet</td>
                    <td>10%</td>
                    <td>11%</td>
                    <td>+1%</td>
                    <td><span class="badge bg-success">✅ On Track</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
