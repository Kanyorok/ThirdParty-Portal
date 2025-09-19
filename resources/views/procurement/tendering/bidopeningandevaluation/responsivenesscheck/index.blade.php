@extends('layouts.app')
@section('title', 'Bid Responsiveness Check')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container mt-4">
    <h4 class="mb-4">📋 Bid Responsiveness Check</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tender Selection -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-search"></i> Select Tender for Responsiveness Check</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row align-items-end">
            <div class="col-md-8">
                    <label for="tender" class="form-label fw-bold">Tender with Opened Bids:</label>
                    <select name="tender" class="form-select" id="tenderSelect" onchange="this.form.submit()">
                        <option value="">-- Select Tender --</option>
                        @foreach($tenders as $tender)
                            @if($tender && $tender->TenderNo)
                                <option value="{{ $tender->TenderNo }}" 
                                    {{ ($selectedTender && $selectedTender->TenderNo === $tender->TenderNo) ? 'selected' : '' }}>
                                    {{ $tender->TenderNo }} - {{ $tender->Title ?? 'Unknown Title' }}
                                </option>
                            @endif
                        @endforeach
                        </select>
                    </div>
                <div class="col-md-4">
                    @if($selectedTender && $selectedTender->OpeningDate)
                        <div class="text-muted small">
                            <strong>Opening Date:</strong> {{ $selectedTender->OpeningDate->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($selectedTender && $submissions->isNotEmpty())
        <!-- Bulk Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1 text-primary">Responsiveness Check for: {{ $selectedTender->TenderNo ?? 'Unknown Tender' }}</h6>
                        <p class="mb-0 text-muted">{{ $submissions->count() }} opened bids ready for responsiveness evaluation</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm" onclick="showBulkModal('responsive')">
                                <i class="fas fa-check-double"></i> Mark All Responsive
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="showBulkModal('non-responsive')">
                                <i class="fas fa-times-circle"></i> Bulk Non-Responsive
                            </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Bid Submissions for Responsiveness Check</h5>
            </div>
                <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">
                                <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()">
                            </th>
                            <th>Supplier</th>
                            <th>Bid Details</th>
                            <th>Submission Info</th>
                            <th>Opening Status</th>
                            <th>Responsiveness</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($submissions as $submission)
                            <tr id="submission-{{ $submission->Id }}">
                                <td>
                                    @if(!$submission->isOpened())
                                        <span class="text-muted">🔒</span>
                                    @elseif(in_array($submission->BidStatus, ['responsive', 'non-responsive']))
                                        <span class="text-muted">✓</span>
                                    @else
                                        <input type="checkbox" class="bid-checkbox" value="{{ $submission->Id }}">
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $submission->SupplierName }}</strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-{{ $submission->SubmissionSource === 'portal' ? 'globe' : 'hand-paper' }}"></i>
                                        {{ ucfirst($submission->SubmissionSource) }} Submission
                                    </small>
                                </td>
                                <td>
                                    @if($submission->isOpened())
                                        <strong>{{ $submission->Currency }} {{ number_format($submission->BidAmount, 2) }}</strong><br>
                                        <small class="text-muted">
                                            Validity: {{ $submission->ValidityPeriod }} days<br>
                                            Delivery: {{ $submission->DeliveryPeriod }} days
                                        </small>
                                    @else
                                        <span class="badge bg-warning">🔒 Sealed</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>Received:</strong> {{ $submission->ReceivedAt->format('d/m/Y H:i') }}<br>
                                    <strong>Documents:</strong> {{ count(json_decode($submission->EncryptedDocuments, true) ?? []) }} files<br>
                                    <strong>Security:</strong> 
                                    @if($submission->BidSecurityPresent === true)
                                        <span class="text-success">✅ Present</span>
                                    @elseif($submission->BidSecurityPresent === false)
                                        <span class="text-danger">❌ Missing</span>
                                    @else
                                        <span class="text-muted">❓ Unknown</span>
                                    @endif
                                    <br>
                                    <strong>Timing:</strong> 
                                    @if($submission->ReceivedOnTime === true)
                                        <span class="text-success">✅ On Time</span>
                                    @elseif($submission->ReceivedOnTime === false)
                                        <span class="text-warning">⏰ Late</span>
                                    @else
                                        <span class="text-muted">❓ Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$submission->isOpened())
                                        <span class="badge bg-warning">🔒 Not Opened</span><br>
                                        <small class="text-muted">Must be opened first</small>
                                    @else
                                        <span class="badge bg-success">✅ Opened</span><br>
                                        <small class="text-muted">
                                            {{ $submission->OpenedAt->format('d/m/Y H:i') }}<br>
                                            by {{ $submission->openedByUser->name ?? 'Unknown' }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @switch($submission->BidStatus)
                                        @case('responsive')
                                            <span class="badge bg-success">✅ Responsive</span><br>
                                            @if($submission->ResponsivenessRemarks)
                                                <small class="text-success">{{ Str::limit($submission->ResponsivenessRemarks, 50) }}</small>
                                            @endif
                                            @break
                                        @case('non-responsive')
                                            <span class="badge bg-danger">❌ Non-Responsive</span><br>
                                            @if($submission->ResponsivenessRemarks)
                                                <small class="text-danger">{{ Str::limit($submission->ResponsivenessRemarks, 50) }}</small>
                                            @endif
                                            @break
                                        @case('submitted')
                                            @if($submission->isOpened())
                                                <span class="badge bg-warning">⏳ Pending Review</span>
                                            @else
                                                <span class="badge bg-secondary">🔒 Awaiting Opening</span>
                                            @endif
                                            @break
                                        @default
                                            <span class="badge bg-info">{{ ucfirst($submission->BidStatus) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if(!$submission->isOpened())
                                        <span class="text-muted">🔒 Sealed</span>
                                    @elseif(in_array($submission->BidStatus, ['responsive', 'non-responsive']))
                                        <!-- Already processed - show view/edit options -->
                                        <button class="btn btn-sm btn-outline-info mb-1" 
                                                onclick="viewResponsivenessDetails({{ $submission->Id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning mb-1" 
                                                onclick="editResponsiveness({{ $submission->Id }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    @else
                                        <!-- Ready for responsiveness check -->
                                        <div class="btn-group w-100" role="group">
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="showDetailedCheck({{ $submission->Id }})">
                                                <i class="fas fa-clipboard-list"></i> Detailed Check
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="showBidDetails({{ $submission->Id }})">
                                                <i class="fas fa-eye"></i> Drill Down
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
            </div>
        </div>
    @elseif($selectedTender && $submissions->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No opened bids found for this tender. Bids must be opened in the opening ceremony before responsiveness check.
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Please select a tender to view opened bids ready for responsiveness check.
        </div>
    @endif
</div>

<!-- Quick responsiveness modal removed - now using detailed check modal only -->

<!-- Detailed Responsiveness Check Modal -->
<div class="modal fade" id="detailedCheckModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-list"></i> Detailed Responsiveness Check
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="detailedCheckForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Submission Timing</h6>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="submitted_timely" id="timing_yes" value="1" required>
                                <label class="form-check-label text-success" for="timing_yes">
                                    <i class="fas fa-check"></i> Submitted Timely
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="submitted_timely" id="timing_no" value="0" required>
                                <label class="form-check-label text-danger" for="timing_no">
                                    <i class="fas fa-times"></i> Late Submission
                                </label>
                            </div>
                            <textarea class="form-control mt-2" name="timely_remarks" placeholder="Remarks on submission timing..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Document Compliance</h6>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_mandatory_documents" id="docs_yes" value="1" required>
                                <label class="form-check-label text-success" for="docs_yes">
                                    <i class="fas fa-check"></i> Complete Documents
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="has_mandatory_documents" id="docs_no" value="0" required>
                                <label class="form-check-label text-danger" for="docs_no">
                                    <i class="fas fa-times"></i> Missing Documents
                                </label>
                            </div>
                            <textarea class="form-control mt-2" name="document_remarks" placeholder="Remarks on document compliance..."></textarea>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="text-primary">Supplier Eligibility</h6>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_eligible" id="eligible_yes" value="1" required>
                                <label class="form-check-label text-success" for="eligible_yes">
                                    <i class="fas fa-check"></i> Eligible
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="is_eligible" id="eligible_no" value="0" required>
                                <label class="form-check-label text-danger" for="eligible_no">
                                    <i class="fas fa-times"></i> Not Eligible
                                </label>
                            </div>
                            <textarea class="form-control mt-2" name="eligibility_remarks" placeholder="Remarks on eligibility..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Overall Assessment</h6>
                            <textarea class="form-control" name="overall_remarks" rows="3" placeholder="Overall remarks on bid responsiveness..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitDetailedCheck()">
                    <i class="fas fa-save"></i> Save Detailed Check
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bid Drill-Down Modal -->
<div class="modal fade" id="bidDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-search-plus"></i> Bid Details Drill-Down
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bidDetailsContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading bid details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-double"></i> Bulk Responsiveness Check
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="bulkContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitBulkCheck()">
                    Confirm Bulk Action
                </button>
            </div>
        </div>
    </div>
</div>

    <script>
    let currentBidId = null;
    let bulkAction = null;

    // Toggle all checkboxes
    function toggleAllCheckboxes() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.bid-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    // Quick responsive/non-responsive functions removed - now using detailed check only

    // Show bulk modal
    function showBulkModal(action) {
        const selectedBids = document.querySelectorAll('.bid-checkbox:checked');
        if (selectedBids.length === 0) {
            alert('Please select at least one bid for bulk action.');
            return;
        }

        bulkAction = action;
        const actionText = action === 'responsive' ? 'RESPONSIVE' : 'NON-RESPONSIVE';
        const alertClass = action === 'responsive' ? 'alert-success' : 'alert-danger';
        const iconClass = action === 'responsive' ? 'fa-check-circle' : 'fa-exclamation-triangle';

        document.getElementById('bulkContent').innerHTML = `
            <div class="alert ${alertClass}">
                <i class="fas ${iconClass}"></i> 
                <strong>Bulk ${actionText} Action</strong><br>
                This will mark ${selectedBids.length} bid(s) as ${actionText.toLowerCase()}.
            </div>
            <div class="mb-3">
                <label class="form-label">Bulk Action Remarks:</label>
                <textarea class="form-control" id="bulkRemarks" rows="3" 
                          placeholder="Reason for bulk ${actionText.toLowerCase()} action" 
                          ${action === 'non-responsive' ? 'required' : ''}></textarea>
            </div>
        `;

        new bootstrap.Modal(document.getElementById('bulkModal')).show();
    }

    // Submit bulk check
    function submitBulkCheck() {
        const selectedBids = Array.from(document.querySelectorAll('.bid-checkbox:checked')).map(cb => cb.value);
        const remarks = document.getElementById('bulkRemarks').value;

        if (bulkAction === 'non-responsive' && !remarks.trim()) {
            alert('Please provide remarks for bulk non-responsive action.');
            return;
        }

        const bidChecks = selectedBids.map(bidId => ({
            bid_id: bidId,
            is_responsive: bulkAction === 'responsive',
            remarks: remarks
        }));

        fetch('/procurement/bid-responsiveness/bulk-check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                tender_ref: '{{ $selectedTender ? $selectedTender->TenderNo : "" }}',
                bid_checks: bidChecks
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to perform bulk action. Please try again.');
        });
    }

    // Show detailed responsiveness check form
    function showDetailedCheck(bidId) {
        currentBidId = bidId;
        
        // Reset form
        document.getElementById('detailedCheckForm').reset();
        
        new bootstrap.Modal(document.getElementById('detailedCheckModal')).show();
    }

    // Submit detailed responsiveness check
    function submitDetailedCheck() {
        const form = document.getElementById('detailedCheckForm');
        const formData = new FormData(form);

        // Validate required fields
        if (!formData.get('submitted_timely') || !formData.get('has_mandatory_documents') || !formData.get('is_eligible')) {
            alert('Please complete all required fields.');
            return;
        }

        const checkData = {
            submitted_timely: formData.get('submitted_timely') === '1',
            has_mandatory_documents: formData.get('has_mandatory_documents') === '1',
            is_eligible: formData.get('is_eligible') === '1',
            timely_remarks: formData.get('timely_remarks'),
            document_remarks: formData.get('document_remarks'),
            eligibility_remarks: formData.get('eligibility_remarks'),
            overall_remarks: formData.get('overall_remarks')
        };

        fetch(`/procurement/bid-responsiveness/${currentBidId}/detailed-check`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(checkData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('detailedCheckModal')).hide();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to perform detailed check. Please try again.');
        });
    }

    // Show bid details drill-down
    function showBidDetails(bidId) {
        document.getElementById('bidDetailsContent').innerHTML = `
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Loading bid details...</p>
            </div>
        `;

        new bootstrap.Modal(document.getElementById('bidDetailsModal')).show();

        fetch(`/procurement/bid-responsiveness/${bidId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderBidDetails(data.data);
            } else {
                document.getElementById('bidDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('bidDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Failed to load bid details.
                </div>
            `;
        });
    }

    // Render bid details in the modal
    function renderBidDetails(data) {
        const submissionInfo = data.submission_info;
        const supplierDetails = data.supplier_details;
        const documents = data.documents;
        const responsivenessSummary = data.responsiveness_summary;
        const openingDetails = data.opening_details;

        let documentsHtml = '';
        if (documents && documents.length > 0) {
            documentsHtml = documents.map(doc => `
                <tr>
                    <td><i class="fas fa-file"></i> ${doc.filename}</td>
                    <td>${doc.size}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewDocument(${submissionInfo.id}, '${doc.id}')">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            documentsHtml = '<tr><td colspan="3" class="text-center text-muted">No documents found</td></tr>';
        }

        // Responsiveness status badges
        const getStatusBadge = (status) => {
            if (status === true) return '<span class="badge bg-success">✅ Yes</span>';
            if (status === false) return '<span class="badge bg-danger">❌ No</span>';
            return '<span class="badge bg-secondary">❓ Unknown</span>';
        };

        document.getElementById('bidDetailsContent').innerHTML = `
            <div class="row">
                <!-- Submission Info -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-file-alt"></i> Submission Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Tender:</strong></td><td>${submissionInfo.tender_ref} - ${submissionInfo.tender_title}</td></tr>
                                <tr><td><strong>Bid Amount:</strong></td><td>${submissionInfo.currency} ${parseFloat(submissionInfo.bid_amount).toLocaleString()}</td></tr>
                                <tr><td><strong>Received:</strong></td><td>${submissionInfo.received_at}</td></tr>
                                <tr><td><strong>Source:</strong></td><td><span class="badge bg-info">${submissionInfo.submission_source}</span></td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Supplier Details -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-building"></i> Supplier Details</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Name:</strong></td><td>${supplierDetails.supplier_name}</td></tr>
                                <tr><td><strong>Trading Name:</strong></td><td>${supplierDetails.trading_name}</td></tr>
                                <tr><td><strong>Registration:</strong></td><td>${supplierDetails.registration_number}</td></tr>
                                <tr><td><strong>Contact Person:</strong></td><td>${supplierDetails.contact_person}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${supplierDetails.email}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${supplierDetails.phone}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-folder-open"></i> Submitted Documents</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Document</th>
                                            <th>Size</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${documentsHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Responsiveness Summary -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-clipboard-check"></i> Responsiveness Summary</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Timely:</strong></td><td>${getStatusBadge(responsivenessSummary.submitted_timely.status)}</td></tr>
                                <tr><td><strong>Documents:</strong></td><td>${getStatusBadge(responsivenessSummary.has_mandatory_documents.status)}</td></tr>
                                <tr><td><strong>Eligible:</strong></td><td>${getStatusBadge(responsivenessSummary.is_eligible.status)}</td></tr>
                                <tr><td><strong>Overall:</strong></td><td>${getStatusBadge(responsivenessSummary.overall_responsive)}</td></tr>
                                ${responsivenessSummary.checked_at ? `<tr><td><strong>Checked:</strong></td><td>${responsivenessSummary.checked_at} by ${responsivenessSummary.checked_by}</td></tr>` : ''}
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Opening Details -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0"><i class="fas fa-unlock"></i> Opening Details</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Opened:</strong></td><td>${openingDetails.opened_at || 'Not opened'}</td></tr>
                                <tr><td><strong>Opened By:</strong></td><td>${openingDetails.opened_by || 'N/A'}</td></tr>
                                <tr><td><strong>Ceremony:</strong></td><td>${openingDetails.ceremony_type || 'N/A'}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // View document (placeholder for DMS integration)
    function viewDocument(bidId, documentId) {
        fetch(`/procurement/bid-responsiveness/${bidId}/document/${documentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Document Info:\n\nSupplier: ${data.data.supplier}\nTender: ${data.data.tender}\n\nNote: ${data.data.note}`);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load document.');
        });
    }

    // View responsiveness details (placeholder)
    function viewResponsivenessDetails(bidId) {
        showBidDetails(bidId);
    }

    // Edit responsiveness (placeholder)
    function editResponsiveness(bidId) {
        showDetailedCheck(bidId);
    }
    </script>
@endsection