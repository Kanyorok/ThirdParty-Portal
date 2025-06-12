@extends('layouts.app')
@section('title', 'Regulatory Ratios Dashboard')
@section('content')
    <div class="card p-4">
        <h5>📊 Regulatory Ratios Dashboard</h5>
        <p class="text-muted">Track compliance with key CBK prudential ratios.</p>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select">
                    <option>Q1 2025</option>
                    <option>Q2 2025</option>
                    <option>YTD 2025</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select class="form-select">
                    <option>All Branches</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Product Type</label>
                <select class="form-select">
                    <option>All</option>
                    <option>Loan</option>
                    <option>Deposit</option>
                </select>
            </div>
        </div>

        <!-- Ratio Tiles -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm p-3">
                    <h6>🛡️ Capital Adequacy Ratio</h6>
                    <h4 class="text-primary">17.5%</h4>
                    <p class="text-muted small">Target ≥ 14.5%</p>
                    <a href="{{ route('capitaladequacyratio.index') }}" class="btn btn-outline-secondary btn-sm">🔍
                        Drilldown</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm p-3">
                    <h6>💧 Liquidity Ratio</h6>
                    <h4 class="text-success">38.2%</h4>
                    <p class="text-muted small">Target ≥ 20%</p>
                    <a href="{{ route('liquidityratio.index') }}" class="btn btn-outline-secondary btn-sm">🔍
                        Drilldown</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm p-3">
                    <h6>📉 Loan-to-Deposit Ratio</h6>
                    <h4 class="text-warning">83.7%</h4>
                    <p class="text-muted small">Target ≤ 90%</p>
                    <a href="{{ route('loantodepositratio.index') }}" class="btn btn-outline-secondary btn-sm">🔍
                        Drilldown</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm p-3">
                    <h6>⚙️ Cost to Income Ratio</h6>
                    <h4 class="text-danger">62.4%</h4>
                    <p class="text-muted small">Target ≤ 55%</p>
                    <a href="{{ route('costtoincomeratio.index') }}" class="btn btn-outline-secondary btn-sm">🔍
                        Drilldown</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm p-3">
                    <h6>📈 Return on Assets (ROA)</h6>
                    <h4 class="text-success">3.1%</h4>
                    <p class="text-muted small">Target ≥ 1.5%</p>
                    <a href="{{ route('returnonassetsratio.index') }}" class="btn btn-outline-secondary btn-sm">🔍
                        Drilldown</a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm p-3">
                    <h6>🏦 Return on Equity (ROE)</h6>
                    <h4 class="text-primary">19.2%</h4>
                    <p class="text-muted small">Target ≥ 15%</p>
                    <a href="{{ route('returnonequityratio.index') }}" class="btn btn-outline-secondary btn-sm">🔍
                        Drilldown</a>
                </div>
            </div>
        </div>
    </div>
@endsection
