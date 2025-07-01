@extends('layouts.app')
@section('title', 'Document Repository')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📁 Document Management System</h4>

    {{-- 🔍 Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" placeholder="e.g. Procurement Plan">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Document Type</label>
                    <select class="form-select">
                        <option>All Types</option>
                        <option>Invoice</option>
                        <option>Policy</option>
                        <option>Contract</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select class="form-select">
                        <option>All Categories</option>
                        <option>Finance</option>
                        <option>Legal</option>
                        <option>HR</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 📂 Document List --}}
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span>📂 All Documents</span>
            <a href="{{ route('drepositorymanagement.create') }}" class="btn btn-success btn-sm">➕ Add New Document</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Tags</th>
                    <th>Uploaded By</th>
                    <th>Modified</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>contract_supplier_2025.pdf</td>
                    <td>Contract</td>
                    <td>supplier, legal</td>
                    <td>Alice K.</td>
                    <td>3 days ago</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>invoice_april.pdf</td>
                    <td>Invoice</td>
                    <td>finance, april</td>
                    <td>Daniel M.</td>
                    <td>1 week ago</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🕓 My Recent Documents --}}
    <div class="card">
        <div class="card-header bg-info text-white">🕓 My Recent Documents</div>
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between">
                    <span>procurement_plan_q3.pdf</span>
                    <small class="text-muted">5 mins ago</small>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>legal_notice.pdf</span>
                    <small class="text-muted">2 days ago</small>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
