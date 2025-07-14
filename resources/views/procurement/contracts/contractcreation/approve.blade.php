@extends('layouts.app')
@section('title', 'Contract Approval')

@section('content')
    <div class="container mt-4">
        <h4>✅ Contract Approval & Sign-Off</h4>

        <!-- Contract Overview -->
        <div class="mb-4">
            <h5>📝 CONTRACT/PROC/2025/009 – Supply of Office Furniture</h5>
            <p><strong>Awarded To:</strong> OfficePro Suppliers</p>
            <p><strong>Duration:</strong> 01-Jul-2025 to 31-Dec-2025</p>
        </div>

        <!-- Internal Approvers Table -->
        <h5 class="mb-2">👤 Internal Approvers</h5>
        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>Role</th>
                <th>Approver</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Legal</td>
                <td>Mary Wanjiru</td>
                <td><span class="badge bg-success">Approved</span></td>
                <td>✓</td>
            </tr>
            <tr>
                <td>Budget Officer</td>
                <td>Daniel Ochieng</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>
                    <form class="d-flex gap-2">
                        <select class="form-select form-select-sm w-auto">
                            <option value="A">Approve</option>
                            <option value="R">Reject</option>
                        </select>
                        <input type="text" class="form-control form-control-sm" placeholder="Comment">
                        <button class="btn btn-sm btn-outline-primary">Submit</button>
                    </form>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- Vendor Upload Section -->
        <h5 class="mt-4">📎 Vendor Signed Contract</h5>
        <div class="mb-3">
            <p><strong>Status:</strong> <span class="badge bg-warning text-dark">Pending Upload</span></p>
            <label class="form-label">Upload Signed Copy (PDF)</label>
            <input type="file" class="form-control mb-2">
            <button class="btn btn-success btn-sm">📤 Upload</button>
        </div>

        <!-- Finalize -->
        <div class="mt-4 text-end">
            <button class="btn btn-outline-secondary">⬅ Back</button>
            <button class="btn btn-primary">✅ Mark Contract as Signed</button>
        </div>
    </div>
@endsection
