@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')

<div class="container mt-4">
    <h4 class="mb-4">📈 Procurement Module Reports</h4>

    <!-- Report Categories -->
    <div class="row g-4">
        <!-- Requisition Reports -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">📋 Requisition Reports</h5>
                    <ul class="list-unstyled mb-3">
                        <li><a href="#" class="btn btn-sm btn-outline-primary w-100 mb-2">Requisition Summary</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-primary w-100 mb-2">Pending Approvals</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-primary w-100">Rejected Requisitions</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tender Reports -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">📑 Tender Reports</h5>
                    <ul class="list-unstyled mb-3">
                        <li><a href="#" class="btn btn-sm btn-outline-success w-100 mb-2">Active Tenders</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-success w-100 mb-2">Awarded Tenders</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-success w-100">Tender Evaluation Summary</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Supplier Reports -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">🏢 Supplier Reports</h5>
                    <ul class="list-unstyled mb-3">
                        <li><a href="#" class="btn btn-sm btn-outline-info w-100 mb-2">Prequalified Suppliers</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-info w-100 mb-2">Blacklisted Suppliers</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-info w-100">Supplier Performance Report</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Purchase Order Reports -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">🧾 Purchase Order Reports</h5>
                    <ul class="list-unstyled mb-3">
                        <li><a href="#" class="btn btn-sm btn-outline-warning w-100 mb-2">PO Summary</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-warning w-100 mb-2">Pending Deliveries</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-warning w-100">Completed Orders</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Procurement KPIs -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">📊 Procurement KPIs</h5>
                    <ul class="list-unstyled mb-3">
                        <li><a href="#" class="btn btn-sm btn-outline-dark w-100 mb-2">Average Lead Time</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-dark w-100 mb-2">PO Value by Category</a></li>
                        <li><a href="#" class="btn btn-sm btn-outline-dark w-100">Requisition to PO Conversion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection