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

        <form action="{{ route('tendersubmission.index') }}" method="POST" enctype="multipart/form-data">
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
                                    <option value="{{ $tender->TenderNo }}">{{ $tender->Title }}--{{ $tender->TenderNo }}</option>
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
                     <!-- Financial & Terms -->
                     <h6 class="text-muted border-bottom pb-2 mb-3">Financials & Terms</h6>
                     <div class="row">
                        <!-- Currency -->
                        <div class="col-md-4 mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                <option selected disabled>-- Select --</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->Code }}">{{ $currency->Code }} - {{ $currency->Name }}</option>
                                @endforeach
                            </select>
                            @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Bid Amount -->
                        <div class="col-md-4 mb-3">
                            <label for="bidAmount" class="form-label">Bid Amount</label>
                            <input type="number" step="0.01" class="form-control @error('bid_amount') is-invalid @enderror" id="bidAmount" name="bid_amount" required>
                            @error('bid_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Payment Terms -->
                        <div class="col-md-4 mb-3">
                            <label for="paymentTerms" class="form-label">Payment Terms</label>
                            <input type="text" class="form-control @error('payment_terms') is-invalid @enderror" id="paymentTerms" name="payment_terms" placeholder="e.g. 30 Days Net">
                            @error('payment_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                     </div>

                     <div class="row">
                        <!-- Validity Period -->
                        <div class="col-md-6 mb-3">
                            <label for="validityPeriod" class="form-label">Bid Validity Period (Days)</label>
                            <input type="number" class="form-control @error('validity_period') is-invalid @enderror" id="validityPeriod" name="validity_period" required>
                            @error('validity_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Delivery Period -->
                        <div class="col-md-6 mb-3">
                            <label for="deliveryPeriod" class="form-label">Delivery Period (Days)</label>
                            <input type="number" class="form-control @error('delivery_period') is-invalid @enderror" id="deliveryPeriod" name="delivery_period" required>
                            @error('delivery_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                     </div>
                     
                     <h6 class="text-muted border-bottom pb-2 mb-3 mt-2">Logistics</h6>
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
                            <input type="text" class="form-control flatpickr-datetime @error('received_at') is-invalid @enderror" id="receivedDate" name="received_at" required>
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
                <button type="submit" class="btn btn-primary" id="btnSubmit">Save Submission</button>
                <button type="reset" class="btn btn-secondary">Clear</button>
            </div>
        </form>
    </div>
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prevent double submission
        const form = document.querySelector('form');
        const btnSubmit = document.getElementById('btnSubmit');
        
        form.addEventListener('submit', function() {
            if(form.checkValidity()) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
            }
        });
        
        // Initialize Select2
        $('#tenderSelect').select2({
            placeholder: '-- Select Tender --',
            allowClear: true,
            width: '100%'
        });

        $('#supplierSelect').select2({
            placeholder: '-- Select Supplier --',
            allowClear: true,
            width: '100%'
        });

        // Handle Tender Selection Change
        $('#tenderSelect').on('change', function() {
            const tenderId = $(this).val();
            const supplierSelect = $('#supplierSelect');

            // Clear existing options
            supplierSelect.empty().append('<option selected disabled>Loading...</option>');
            supplierSelect.trigger('change');

            if (tenderId) {
                // Show loading state
                supplierSelect.empty().append('<option selected disabled>Loading...</option>');
                
                fetch(`{{ url('procurement/tendersubmission/invited-suppliers') }}/${tenderId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        supplierSelect.empty();
                        supplierSelect.append('<option selected disabled>-- Select Supplier --</option>');

                        if (Array.isArray(data) && data.length === 0) {
                            supplierSelect.append('<option disabled>No suppliers found for this tender</option>');
                        } else if (Array.isArray(data)) {
                            data.forEach(supplier => {
                                const option = new Option(supplier.SupplierName, supplier.Id, false, false);
                                supplierSelect.append(option);
                            });
                        } else {
                            throw new Error('Invalid data format received');
                        }
                        supplierSelect.trigger('change');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        supplierSelect.empty().append('<option selected disabled>Error fetching suppliers</option>');
                        supplierSelect.trigger('change');
                        alert('❌ Failed to load suppliers. Please check your connection or try again.');
                    });
            } else {
                supplierSelect.empty().append('<option selected disabled>-- Select Supplier --</option>');
                supplierSelect.trigger('change');
            }
        });
    });
</script>
@endsection
@endsection
