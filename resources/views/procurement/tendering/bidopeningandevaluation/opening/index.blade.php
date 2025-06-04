
@extends('layouts.app')
@section('title', 'Tender Bid Opening Ceremony')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📜 Tender Bid Opening Ceremony</h4>

    <!-- Tender Details -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="tenderNo" class="form-label fw-bold">Tender No:</label>
                    <select class="form-select" id="tenderNo">
                        <option selected disabled>Search from List</option>
                        <option>TND/PROC/2025/001</option>
                        <option>TND/PROC/2025/002</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tenderName" class="form-label fw-bold">Tender Name:</label>
                    <input type="text" class="form-control" id="tenderName" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Bidding Closed:</label>
                    <input type="text" class="form-control" value="2025-05-09 17:00" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Submissions Table -->
<div class="table-responsive mb-4">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>Supplier Name</th>
                <th>Submission Status</th>
                <th>Decryption Request Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Supplier A Ltd.</td>
                <td><span class="badge bg-info">Encrypted</span></td>
                <td><span class="badge bg-warning">Awaiting Vendor Key</span></td>
                <td>
                    <a href="{{ route('tenderdecrypt.index') }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#decryptModal">
                        Enter Key & Decrypt 🔐 </a>
                </td>
            </tr>
            <tr>
                <td>Supplier B Ltd.</td>
                <td><span class="badge bg-secondary">Withdrawn</span></td>
                <td><span class="badge bg-light text-dark">Not Applicable</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary" disabled>
                        N/A
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

    <!-- Bulk Action -->
    <div class="mb-4 text-end">
        <button class="btn btn-success">
            Send Decryption Request to All Suppliers
        </button>
    </div>
</div>

@endsection
