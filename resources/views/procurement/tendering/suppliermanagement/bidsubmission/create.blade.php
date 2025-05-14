@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📥 Record Manual Bid Submission</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tendersubmission.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Tender & Supplier Info -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <strong>🔎 Tender & Supplier Details</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tenderSelect" class="form-label">Tender Reference</label>
                        <select class="form-select" id="tenderSelect" name="tender_ref" required>
                            <option selected disabled>-- Select Tender --</option>
                            <option value="TND/PROC/2025/001">TND/PROC/2025/001 - ICT Equipment</option>
                            <option value="TND/PROC/2025/002">TND/PROC/2025/002 - Office Furniture</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="supplierSelect" class="form-label">Supplier Name</label>
                        <select class="form-select" id="supplierSelect" name="supplier_name" required>
                            <option selected disabled>-- Select Supplier --</option>
                            <option value="Tech Supplies Ltd">Tech Supplies Ltd</option>
                            <option value="Nova Systems">Nova Systems</option>
                            <option value="EquiBuild Ltd">EquiBuild Ltd</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Details -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <strong>📄 Submission Details</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="submissionMode" class="form-label">Mode of Submission</label>
                        <select class="form-select" id="submissionMode" name="submission_mode" required>
                            <option selected disabled>-- Select Mode --</option>
                            <option value="Hand delivered">Hand Delivered</option>
                            <option value="Courier">Courier</option>
                            <option value="Email">Email</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="receivedDate" class="form-label">Date & Time Received</label>
                        <input type="datetime-local" class="form-control" id="receivedDate" name="received_at" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="receivedBy" class="form-label">Received By</label>
                        <input type="text" class="form-control" id="receivedBy" name="recorded_by" placeholder="e.g., Procurement Officer" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="remarks" class="form-label">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="e.g., Documents sealed, received via courier..."></textarea>
                </div>
            </div>
        </div>

        <!-- Document Upload -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <strong>📎 Attach Scanned Bid Documents</strong>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="bidFiles" class="form-label">Upload ZIP or PDF</label>
                    <input class="form-control" type="file" id="bidFiles" name="bid_files" accept=".zip,.pdf" required>
                    <div class="form-text">Combine technical & financial proposals into one ZIP or PDF file.</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Save Submission</button>
            <button type="reset" class="btn btn-secondary">Clear</button>
        </div>
    </form>
</div>
@endsection