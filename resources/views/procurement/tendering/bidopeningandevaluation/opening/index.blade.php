@extends('layouts.app')
@section('title', 'Tender Bid Opening Ceremony')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container mt-4">
    <h4 class="mb-4">🔓 Tender Bid Opening Ceremony</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {!! nl2br(e(session('success'))) !!}
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
            <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Tenders Ready for Opening (Opening Date ≤ Today)</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="tenderNo" class="form-label fw-bold">Select Tender:</label>
                    <select class="form-select" id="tenderNo">
                        <option selected disabled>-- Choose Tender Ready for Opening --</option>
                        @foreach ($tenders as $item)
                            <option value="{{ $item->TenderNo }}" {{ (isset($tender) && $tender->TenderNo === $item->TenderNo) ? 'selected' : '' }}>
                                {{ $item->TenderNo }} | {{ $item->Title }} 
                                <small>(Opening: {{ $item->OpeningDate->format('d/m/Y H:i') }})</small>
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(isset($tender))
                <div class="row">
                    <div class="col-md-4">
                        <strong>Tender Title:</strong><br>
                        <span class="text-muted">{{ $tender->Title }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Opening Date:</strong><br>
                        <span class="badge bg-info">{{ $tender->OpeningDate->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Submission Deadline:</strong><br>
                        <span class="badge bg-secondary">{{ $tender->SubmissionDeadline->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($data && isset($submissions))
        <!-- Ceremony Status -->
        @if(isset($ceremonyStatus))
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            @if($ceremonyStatus['status'] === 'ready')
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-play-circle text-success fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-1 text-success">Ready for Opening Ceremony</h6>
                                        <p class="mb-0 text-muted">{{ $ceremonyStatus['message'] }}</p>
                                    </div>
                                </div>
                            @elseif($ceremonyStatus['status'] === 'completed')
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle text-primary fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-1 text-primary">Ceremony Completed</h6>
                                        <p class="mb-0 text-muted">{{ $ceremonyStatus['message'] }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-warning fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-1 text-warning">Ceremony in Progress</h6>
                                        <p class="mb-0 text-muted">{{ $ceremonyStatus['message'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            @if($ceremonyStatus['can_start'])
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#startCeremonyModal">
                                    <i class="fas fa-unlock"></i> Start Opening Ceremony
                                </button>
                            @elseif($ceremonyStatus['status'] === 'partial' && isset($tender))
                                <button class="btn btn-primary" onclick="completeCeremony('{{ $tender->TenderNo }}')">
                                    <i class="fas fa-flag-checkered"></i> Complete Ceremony
                                </button>
                            @elseif($ceremonyStatus['status'] === 'completed')
                                <a href="{{ route('bid-responsiveness.index') }}?tender={{ $tender->TenderNo ?? '' }}" class="btn btn-info">
                                    <i class="fas fa-arrow-right"></i> Proceed to Responsiveness Check
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Bid Submissions Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-envelope"></i> Bid Submissions</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Supplier Name</th>
                            <th>Bid Amount</th>
                            <th>Submission Details</th>
                            <th>Security</th>
                            <th>Status</th>
                            <th>Opening Details</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
                    @forelse ($submissions as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->SupplierName }}</strong><br>
                                <small class="text-muted">
                                    <i class="fas fa-{{ $item->SubmissionSource === 'portal' ? 'globe' : 'hand-paper' }}"></i>
                                    {{ ucfirst($item->SubmissionSource) }}
                                </small>
                            </td>
                            <td>
                                @if($item->BidAmount && $item->OpenedAt)
                                    <strong>{{ $item->Currency }} {{ number_format($item->BidAmount, 2) }}</strong><br>
                                    <small class="text-muted">{{ $item->ValidityPeriod }} days validity</small>
                                @elseif($item->BidAmount && !$item->OpenedAt)
                                    <span class="badge bg-warning">🔒 Sealed</span>
                                @else
                                    <span class="text-muted">Not available</span>
                                @endif
                            </td>
                            <td>
                                <strong>Received:</strong> {{ \Carbon\Carbon::parse($item->ReceivedAt)->format('d/m/Y H:i') }}<br>
                                <strong>Documents:</strong> {{ $item->document_count }} files<br>
                                <small class="text-muted">
                                    @if($item->submission_timely ?? true)
                                        <i class="fas fa-check text-success"></i> On time
                                    @else
                                        <i class="fas fa-exclamation text-warning"></i> Late
                                    @endif
                                </small>
                            </td>
                            <td>
                                @if($item->BidSecurityPresent === true)
                                    <span class="badge bg-success">✅ Present</span>
                                @elseif($item->BidSecurityPresent === false)
                                    <span class="badge bg-danger">❌ Missing</span>
                                @elseif($item->has_bid_security ?? false)
                                    <span class="badge bg-info">📋 To Check</span>
                                @else
                                    <span class="badge bg-secondary">❓ Unknown</span>
                                @endif
                            </td>
                            <td>{!! $item->status_badge !!}</td>
                            <td>
                                @if($item->OpenedAt)
                                    <strong>Opened:</strong> {{ $item->OpenedAt->format('d/m/Y H:i') }}<br>
                                    <strong>By:</strong> {{ $item->openedByUser->name ?? 'Unknown' }}<br>
                                    @if($item->CeremonyType)
                                        <span class="badge bg-info">{{ ucfirst($item->CeremonyType) }} Opening</span>
                                    @endif
                                @else
                                    <span class="text-muted">Not opened yet</span>
                                @endif
                            </td>
                            <td>
                                @if($item->OpenedAt)
                                    <!-- Bid is opened - show view/read-out actions -->
                                    <button class="btn btn-sm btn-outline-info mb-1" 
                                            onclick="showBidDetails({{ $item->Id }})">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                    <button class="btn btn-sm btn-outline-success mb-1" 
                                            onclick="showReadOutSummary({{ $item->Id }})">
                                        <i class="fas fa-microphone"></i> Read Out
                                    </button>
                                @elseif(isset($tender) && $tender->OpeningDate && $tender->OpeningDate <= now())
                                    <!-- Ceremony started but bid not opened yet - show opening action -->
                                    <button class="btn btn-sm btn-warning mb-1" 
                                            onclick="openIndividualBid({{ $item->Id }})" 
                                            id="open-btn-{{ $item->Id }}">
                                        <i class="fas fa-unlock"></i> Open Bid
                                    </button>
                                @else
                                    <!-- Ceremony not started - show sealed status -->
                                    <span class="text-muted">🔒 Sealed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                <i class="fas fa-inbox"></i> No bid submissions found for this tender.
                </td>
            </tr>
                    @endforelse
        </tbody>
    </table>
            </div>
</div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Please select a tender to view its bid submissions ready for opening ceremony.
        </div>
    @endif
</div>

<!-- Start Ceremony Modal -->
<div class="modal fade" id="startCeremonyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('tender-opening.start-ceremony') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-unlock"></i> Start Opening Ceremony</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="tender_ref" value="{{ $tender->TenderNo ?? '' }}">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Opening Type *</label>
                        <select class="form-select" name="opening_type" required>
                            <option value="">-- Select Opening Type --</option>
                            <option value="public">Public Opening (Open to suppliers/public)</option>
                            <option value="recorded">Recorded Opening (Internal record)</option>
                        </select>
                        <small class="text-muted">Public openings allow supplier attendance, recorded are for internal documentation</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Officers Present</label>
                        <textarea class="form-control" name="officers_present" rows="2" 
                                  placeholder="List procurement officers and witnesses present during ceremony"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ceremony Notes</label>
                        <textarea class="form-control" name="ceremony_notes" rows="3" 
                                  placeholder="Any special notes or observations about the opening ceremony"></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Important:</strong> Starting the ceremony will publicly read out bid basics 
                        (supplier names, amounts, security presence) and mark all bids as opened for responsiveness check.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-unlock"></i> Start Ceremony
        </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bid Details Modal -->
<div class="modal fade" id="bidDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Bid Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bidDetailsContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Read Out Summary Modal -->
<div class="modal fade" id="readOutModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-microphone"></i> Public Read-Out Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="readOutContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('tenderNo').addEventListener('change', function () {
        const tenderId = this.value;
        if (tenderId) {
            window.location.href = `/procurement/tenderopening/${tenderId}`;
        }
    });

    // Open individual bid during ceremony
    function openIndividualBid(bidId) {
        const button = document.getElementById(`open-btn-${bidId}`);
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening...';
        }

        fetch(`/procurement/tender-opening/open-bid/${bidId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert(`🎉 Bid Opened Successfully!\n\nRead Out: ${data.data.read_out_summary}`);
                
                // Refresh page to update status
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-unlock"></i> Open Bid';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Failed to open bid. Please try again.');
            if (button) {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-unlock"></i> Open Bid';
            }
        });
    }

    // Show detailed bid information
    function showBidDetails(bidId) {
        const modal = new bootstrap.Modal(document.getElementById('bidDetailsModal'));
        const content = document.getElementById('bidDetailsContent');
        
        content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading bid details...</div>';
        modal.show();

        fetch(`/procurement/tender-opening/bid-details/${bidId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const details = data.data;
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Submission Information</h6>
                            <p><strong>Supplier:</strong> ${details.submission_info.supplier_name}</p>
                            <p><strong>Tender:</strong> ${details.submission_info.tender_ref}</p>
                            <p><strong>Title:</strong> ${details.submission_info.tender_title}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Bid Details</h6>
                            <p><strong>Amount:</strong> ${details.bid_details.currency} ${parseFloat(details.bid_details.bid_amount).toLocaleString()}</p>
                            <p><strong>Validity:</strong> ${details.bid_details.validity_period}</p>
                            <p><strong>Delivery:</strong> ${details.bid_details.delivery_period}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">Submission Details</h6>
                            <p><strong>Received:</strong> ${details.submission_details.received_at}</p>
                            <p><strong>Source:</strong> ${details.submission_details.submission_source}</p>
                            <p><strong>Documents:</strong> ${details.submission_details.document_count} files</p>
                            <p><strong>Security:</strong> ${details.submission_details.bid_security_present ? '✅ Present' : '❌ Missing'}</p>
                            <p><strong>Timing:</strong> ${details.submission_details.received_on_time ? '✅ On Time' : '⏰ Late'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-info">Opening Details</h6>
                            <p><strong>Opened:</strong> ${details.opening_details.opened_at}</p>
                            <p><strong>Opened By:</strong> ${details.opening_details.opened_by}</p>
                            <p><strong>Ceremony:</strong> ${details.opening_details.ceremony_type}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-warning">Documents</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Filename</th><th>Size</th><th>Uploaded</th></tr></thead>
                                    <tbody>
                                        ${details.documents.map(doc => `
                                            <tr>
                                                <td>${doc.filename}</td>
                                                <td>${doc.size}</td>
                                                <td>${doc.uploaded_at || 'Unknown'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `<div class="alert alert-danger">❌ Failed to load bid details</div>`;
        });
    }

    // Show read-out summary
    function showReadOutSummary(bidId) {
        const modal = new bootstrap.Modal(document.getElementById('readOutModal'));
        const content = document.getElementById('readOutContent');
        
        content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading read-out summary...</div>';
        modal.show();

        fetch(`/procurement/tender-opening/read-out/${bidId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const readOut = data.data;
                content.innerHTML = `
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">📢 Public Announcement</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <strong>READ OUT:</strong><br>
                                "${readOut.public_read_out}"
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Tender Information</h6>
                            <p><strong>Tender No:</strong> ${readOut.tender_info.tender_no}</p>
                            <p><strong>Title:</strong> ${readOut.tender_info.tender_title}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">Ceremony Information</h6>
                            <p><strong>Opened At:</strong> ${readOut.ceremony_info.opened_at}</p>
                            <p><strong>Opened By:</strong> ${readOut.ceremony_info.opened_by}</p>
                            <p><strong>Ceremony Type:</strong> ${readOut.ceremony_info.ceremony_type}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-warning">Read-Out Components</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr><th>Supplier</th><td>${readOut.read_out_components.supplier_name}</td></tr>
                                    <tr><th>Bid Amount</th><td>${readOut.read_out_components.bid_amount}</td></tr>
                                    <tr><th>Validity Period</th><td>${readOut.read_out_components.validity_period}</td></tr>
                                    <tr><th>Delivery Period</th><td>${readOut.read_out_components.delivery_period}</td></tr>
                                    <tr><th>Bid Security</th><td>${readOut.read_out_components.bid_security}</td></tr>
                                    <tr><th>Received Status</th><td>${readOut.read_out_components.received_status}</td></tr>
                                    <tr><th>Documents</th><td>${readOut.read_out_components.document_count}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `<div class="alert alert-danger">❌ ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `<div class="alert alert-danger">❌ Failed to load read-out summary</div>`;
        });
    }

    // Complete ceremony
    function completeCeremony(tenderRef) {
        if (!confirm('Are you sure you want to complete the opening ceremony? This will finalize all opened bids and move them to responsiveness check.')) {
            return;
        }

        fetch(`/procurement/tender-opening/complete-ceremony/${tenderRef}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`🎉 ${data.message}`);
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Failed to complete ceremony. Please try again.');
        });
    }
</script>
@endsection