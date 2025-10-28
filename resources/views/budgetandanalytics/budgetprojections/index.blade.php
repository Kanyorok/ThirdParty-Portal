@extends('layouts.app')
@section('title', 'Driver Projections')
@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">📊 KPI Driver Projections</h5>
                <a href="{{ route('budgetprojections.create') }}" class="btn btn-info btn-sm px-3 py-2">
                    <i class="fas fa-plus me-1"></i> New Mapping
                </a>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 13px;">
                        <thead class="table-light">
                        <tr class="text-start">
                            <th>#</th>
                            <th>Period</th>
                            <th>Branch</th>
                            <th>Product</th>
                            <th>KPI Type</th>
                            <th>Projected Amount</th>
                            <th>Growth Rate (%)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="text-start">
                            <td>1</td>
                            <td>FY2025-Q1</td>
                            <td>Main Branch</td>
                            <td>Personal Loan</td>
                            <td>Loan</td>
                            <td>10,000,000</td>
                            <td>6.50%</td>
                        </tr>
                        <tr class="text-start">
                            <td>2</td>
                            <td>FY2025-Q1</td>
                            <td>Westlands Branch</td>
                            <td>Fixed Deposit</td>
                            <td>Deposit</td>
                            <td>12,500,000</td>
                            <td>4.00%</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        .table th, .table td {
            text-align: left;
            vertical-align: middle;
            padding: 0.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.6rem;
            font-size: 0.8rem;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
        }
    </style>
@endsection
