@extends('layouts.app')
@section('title','Credit Management')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-user-shield text-info me-2"></i> Credit Profiles
                </h6>
                <a href="{{ route('creditmanagement.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i> New Credit Profile
                </a>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                        <tr class="text-center">
                            <th>#</th>
                            <th>Customer</th>
                            <th>ID Number</th>
                            <th>Credit Limit</th>
                            <th style="min-width:180px;">Utilization</th>
                            <th>Risk</th>
                            <th>Status</th>
                            <th>Last Review</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="text-center">
                        <tr>
                            <td>1</td>
                            <td class="text-start">
                                <div class="fw-semibold">ABC Properties Ltd</div>
                                <div class="small text-muted">abc@props.co.ke • +254 722 555 000</div>
                            </td>
                            <td>12345678</td>
                            <td>KSh 5,000,000.00</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress w-100" style="height:8px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: 62%"></div>
                                    </div>
                                    <span class="small text-muted">62%</span>
                                </div>
                                <div class="small text-muted">Used: KSh 3,100,000 • Avail: KSh 1,900,000</div>
                            </td>
                            <td><span class="badge bg-warning text-dark">Medium</span></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>2025-08-01</td>
                            <td class="text-center">
                                <a href="{{ route('creditmanagement.show',1) }}" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('creditmanagement.edit',1) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <td class="text-start">
                                <div class="fw-semibold">XYZ Traders Ltd</div>
                                <div class="small text-muted">accounts@xyz.co.ke • +254 733 888 777</div>
                            </td>
                            <td>98765432</td>
                            <td>KSh 2,000,000.00</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress w-100" style="height:8px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 95%"></div>
                                    </div>
                                    <span class="small text-muted">95%</span>
                                </div>
                                <div class="small text-muted">Used: KSh 1,900,000 • Avail: KSh 100,000</div>
                            </td>
                            <td><span class="badge bg-danger">High</span></td>
                            <td><span class="badge bg-warning text-dark">Review</span></td>
                            <td>2025-07-15</td>
                            <td class="text-center">
                                <a href="{{ route('creditmanagement.show',2) }}" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('creditmanagement.edit',2) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>

                {{-- Pagination placeholder (static demo) --}}
                <nav class="mt-2">
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <li class="page-item disabled"><span class="page-link">«</span></li>
                        <li class="page-item active"><span class="page-link">1</span></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">»</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        :root { --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif; }
        body, .card, .table { font-family: var(--font-sans); }
        .table-hover tbody tr:hover { background-color: #f8f9fa; transition: background-color .2s; }
        .card { border: none; border-radius: .5rem; }
        .btn-sm { padding: .25rem .5rem; }
    </style>
@endsection
