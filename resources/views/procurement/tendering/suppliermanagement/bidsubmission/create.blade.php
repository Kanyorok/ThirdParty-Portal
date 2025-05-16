@extends('layouts.app')
@section('title', 'Manual Bid Submission')
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
                            <select class="form-select @error('tender_ref') is-invalid @enderror" id="tenderSelect" name="tender_ref" required>
                                <option selected disabled>-- Select Tender --</option>
                                @foreach ($tenders as $tender)
                                    <option value="{{ $tender->TenderNo }}">{{ $tender->TenderNo }}</option>
                                @endforeach
                            </select>
                            @error('tender_ref')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="supplierSelect" class="form-label">Supplier Name</label>
                            <select class="form-select @error('supplier_name') is-invalid @enderror" id="supplierSelect" name="supplier_name" required>
                                <option selected disabled>-- Select Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->SupplierName }}">{{ $supplier->SupplierName }}</option>
                                @endforeach
                            </select>
                            @error('supplier_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                        <!-- Mode of Submission -->
                        <div class="col-md-4 mb-3">
                            <label for="submissionMode" class="form-label">Mode of Submission</label>
                            <select class="form-select @error('submission_mode') is-invalid @enderror" id="submissionMode" name="submission_mode" required>
                                <option selected disabled>-- Select Mode --</option>
                                @foreach ($submissionModes as $mode)
                                    <option value="{{ $mode->Description }}">{{ $mode->Description }}</option>
                                @endforeach
                            </select>
                            @error('submission_mode')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Date & Time Received -->
                        <div class="col-md-4 mb-3">
                            <label for="receivedDate" class="form-label">Date & Time Received</label>
                            <input type="datetime-local" class="form-control @error('received_at') is-invalid @enderror" id="receivedDate" name="received_at" required>
                            @error('received_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Received By -->
                        <div class="col-md-4 mb-3">
                            <label for="receivedBy" class="form-label">Received By</label>
                            <input type="text" class="form-control @error('recorded_by') is-invalid @enderror" id="receivedBy" name="recorded_by" placeholder="e.g., Procurement Officer" required>
                            @error('recorded_by')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="2" placeholder="e.g., Documents sealed, received via courier..."></textarea>
                            @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
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
                        <input class="form-control @error('bid_files') is-invalid @enderror" type="file" id="bidFiles" name="bid_files" accept=".zip,.pdf" required>
                        <div class="form-text">Combine technical & financial proposals into one ZIP or PDF file.</div>
                        @error('bid_files')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
