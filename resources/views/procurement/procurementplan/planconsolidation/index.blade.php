@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">✅ Approve Procurement Plan</h4>

    <form>
        <!-- Plan Selection -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Select Plan</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Plan Reference</label>
                        <select class="form-select" id="planRef">
                            <option selected disabled>-- Select Plan Reference --</option>
                            <option value="PLAN/ICT/2025/001">PLAN/ICT/2025/001 – ICT – Nairobi HQ</option>
                            <option value="PLAN/FIN/2025/002">PLAN/FIN/2025/002 – Finance – Mombasa</option>
                            <option value="PLAN/ADM/2025/003">PLAN/ADM/2025/003 – Admin – Kisumu</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button class="btn btn-outline-primary w-100">🔍 Load Plan Details</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Summary Section (Populated After Selection) -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Plan Summary</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>
                        <input type="text" class="form-control" value="Nairobi HQ" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" value="ICT" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Plan Period</label>
                        <input type="text" class="form-control" value="01-Jan-2025 to 31-Dec-2025" readonly>
                    </div>
                </div>
                <p class="mt-3"><strong>Total Items:</strong> 7 | <strong>Estimated Cost:</strong> KES 4,200,000</p>
            </div>
        </div>

        <!-- Approval Decision -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Approval Decision</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Decision</label>
                        <select class="form-select" required>
                            <option selected disabled>-- Select --</option>
                            <option>Approve</option>
                            <option>Reject</option>
                            <option>Return for Revision</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Comments</label>
                        <textarea class="form-control" rows="3" placeholder="Reason or notes for approval/rejection..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-end">
            <button type="submit" class="btn btn-success">Submit Decision</button>
            <button type="button" class="btn btn-outline-secondary">Cancel</button>
        </div>
    </form>
</div>

@endsection
