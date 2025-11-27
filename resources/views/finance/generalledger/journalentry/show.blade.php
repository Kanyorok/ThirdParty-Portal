@extends('layouts.app')

@section('styles')
    <style>
        .je-table { min-width: 1100px; }
        .je-table th, .je-table td { white-space: nowrap; vertical-align: middle; text-align: left; }
        .je-table th.col-gl, .je-table td.col-gl { min-width: 360px; }
        .je-table th.col-debit, .je-table td.col-debit,
        .je-table th.col-credit, .je-table td.col-credit,
        .je-table th.col-amount, .je-table td.col-amount { min-width: 160px; text-align: left; }
        .je-table th.col-narr, .je-table td.col-narr { min-width: 420px; white-space: normal; word-break: break-word; }

        /* Row color coding for DR/CR */
        .table tbody tr.row-debit,
        .table tbody tr.row-debit td {
            color: #dc3545 !important;
        }
        .table tbody tr.row-credit,
        .table tbody tr.row-credit td {
            color: #28a745 !important;
        }

        /* Journal container styling */
        .journal-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
        }

        /* Header - clean and minimalist */
        .journal-header {
            background: #ffffff;
            color: #343a40;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            position: relative;
        }

        .journal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .journal-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .status-badge {
            position: absolute;
            top: 0.75rem;
            right: 1rem;
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 0.5rem;
        }

        /* Info cards with modern design */
        .info-card {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            height: 100%;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .info-card .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .info-card .info-value {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            line-height: 1.3;
        }

        /* Audit trail with elegant design */
        .audit-trail-card {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem 1.25rem;
        }

        .audit-list { list-style: none; padding: 0; margin: 0; }
        .audit-list li { margin-bottom: .35rem; color: #495057; display: flex; gap: .5rem; align-items: baseline; }
        .audit-list .label { min-width: 160px; font-weight: 600; color: #6c757d; }

        /* Info list (merged journal details) */
        .info-list { list-style: none; padding: 0; margin: 0; }
        .info-list li { margin-bottom: .35rem; color: #495057; display: flex; gap: .5rem; align-items: baseline; }
        .info-list .label { min-width: 160px; font-weight: 600; color: #6c757d; }

        .audit-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: white;
            border-radius: 0.75rem;
            border-left: 4px solid #007bff;
            transition: all 0.2s ease;
        }

        .audit-item:hover {
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .audit-item.reversed {
            border-left-color: #dc3545;
        }

        .audit-item.approved {
            border-left-color: #28a745;
        }

        .audit-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .audit-icon.created { background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #1976d2; }
        .audit-icon.modified { background: linear-gradient(135deg, #fff3e0, #ffcc80); color: #f57c00; }
        .audit-icon.approved { background: linear-gradient(135deg, #e8f5e8, #c8e6c9); color: #388e3c; }
        .audit-icon.reversed { background: linear-gradient(135deg, #ffebee, #ffcdd2); color: #d32f2f; }

        .audit-details {
            flex: 1;
        }

        .audit-label {
            font-weight: 700;
            color: #495057;
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
        }

        .audit-value {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .audit-timestamp {
            font-size: 0.85rem;
            color: #868e96;
            font-style: italic;
        }

        /* Journal lines table */
        .journal-lines-card {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .journal-lines-header {
            background: #f8f9fa;
            color: #495057;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 600;
            border-bottom: 1px solid #e9ecef;
        }

        /* Print optimizations */
        @media print {
            .journal-container {
                max-width: none;
                margin: 0;
                padding: 1cm;
            }

            @page { size: A4; margin: 10mm; }

            .info-card {
                background: white !important;
                border: 1px solid #dee2e6 !important;
                break-inside: avoid;
            }

            .audit-trail-card { background: white !important; border: 1px solid #dee2e6 !important; }
            .audit-list { column-count: 2; column-gap: 16px; }

            .audit-item {
                break-inside: avoid;
                margin-bottom: 0.5rem;
            }

            .journal-lines-card {
                break-inside: avoid;
            }

            .btn, .no-print {
                display: none !important;
            }

            body { font-size: 11px; }
            .je-table th, .je-table td { padding: .35rem .5rem; font-size: 12px; }

            .info-card .info-value {
                font-size: 1rem;
            }

            .audit-label {
                font-size: 0.9rem;
            }

            .audit-value {
                font-size: 0.85rem;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .journal-header {
                padding: 1.5rem;
            }

            .journal-title {
                font-size: 1.5rem;
            }

            .info-card {
                padding: 1rem;
            }

            .audit-item {
                padding: 0.75rem;
            }

            .audit-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
        }

        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                font-size: 11px !important;
                line-height: 1.3 !important;
            }

            /* Hide navigation and header elements */
            .navbar, .sidebar, .breadcrumb, .top-navbar, .main-header,
            .btn:not(.print-btn), .no-print, .btn-outline-secondary,
            .modal, .dropdown, .alert { display: none !important; }

            /* Container and layout */
            .journal-container {
                max-width: none !important;
                margin: 0 !important;
                padding: 10mm !important;
                width: 100% !important;
            }

            .journal-header {
                background: #f8f9fa !important;
                color: #495057 !important;
                padding: 1rem !important;
                margin-bottom: 1rem !important;
                -webkit-print-color-adjust: exact !important;
            }

            /* Cards in print layout */
            .info-card, .audit-trail-card {
                background: white !important;
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
            }

            /* Two-column layout for cards */
            .print-cards-row {
                display: flex !important;
                gap: 12px !important;
                margin-bottom: 1rem !important;
            }

            .print-cards-col {
                width: 50% !important;
                flex: 1 !important;
            }

            /* Journal lines table */
            .journal-lines-card {
                page-break-inside: avoid !important;
                margin-top: 1rem !important;
            }

            .je-table {
                font-size: 10px !important;
                margin-bottom: 0 !important;
            }

            .je-table th, .je-table td {
                padding: 0.25rem 0.5rem !important;
                font-size: 10px !important;
            }

            /* Audit trail in columns */
            .audit-list {
                column-count: 2 !important;
                column-gap: 16px !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .audit-list li {
                break-inside: avoid !important;
                margin-bottom: 0.5rem !important;
                display: block !important;
            }

            /* Status badge positioning */
            .status-badge {
                position: relative !important;
                top: auto !important;
                right: auto !important;
                display: inline-block !important;
                margin-left: 1rem !important;
            }

            /* Typography adjustments */
            .journal-title {
                font-size: 1.2rem !important;
                margin-bottom: 0.25rem !important;
            }

            .journal-subtitle {
                font-size: 0.9rem !important;
            }

            .info-card .info-label {
                font-size: 0.8rem !important;
            }

            .info-card .info-value {
                font-size: 0.9rem !important;
            }

            .audit-label {
                font-size: 0.8rem !important;
            }

            .audit-value {
                font-size: 0.8rem !important;
            }

            /* Page break controls */
            .journal-lines-card {
                page-break-inside: avoid !important;
            }

            /* Remove hover effects and transitions */
            * {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="journal-container">
        <!-- Header Section -->
        <div class="journal-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="journal-title">📘 Journal Entry Details</h1>
                    <p class="journal-subtitle">Reference: {{ $journalEntry->RefNo }}</p>
                </div>
                <div class="status-badge">
                        @if($journalEntry->ApprovalStatus == 'posted')
                            <span class="badge bg-success">Approved</span>
                        @elseif($journalEntry->ApprovalStatus == 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @elseif($journalEntry->ApprovalStatus == 'draft')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                </div>
            </div>
            <button class="btn btn-light btn-sm mt-3 no-print" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Journal
            </button>
            </div>

        <div class="position-relative px-3 pb-3">
                @if(!empty($journalEntry->IsReversed) && $journalEntry->IsReversed)
                    <div style="position:absolute; inset:0; pointer-events:none; display:flex; align-items:center; justify-content:center; opacity:0.03; z-index:0;">
                        <div style="transform:rotate(-25deg); font-size: 140px; font-weight: 900; color:#dc3545;">REVERSED</div>
                    </div>
                @endif

                {{-- Top Row: Journal Info + Audit Trail --}}
                <div class="row mb-3 g-3 print-cards-row">
                    <div class="col-lg-6 print-cards-col">
                        <div class="info-card h-100">
                            <h6 class="mb-3 text-primary"><i class="fas fa-receipt me-2"></i>Journal Info</h6>
                            <ul class="info-list">
                            <li><span class="label">Reference Number</span><span>{{ $journalEntry->RefNo }}</span></li>
                            <li><span class="label">Date</span><span>{{ \Carbon\Carbon::parse($journalEntry->Date)->format('d M Y') }}</span></li>
                            <li><span class="label">Type</span>
                                <span>
                                    @if($journalEntry->Type === 'recurring')
                                        Recurring
                                    @elseif($journalEntry->Type === 'reversing')
                                        Reversing
                                    @else
                                        Normal
                                    @endif
                                </span>
                            </li>
                            <li><span class="label">Source</span><span><span class="badge bg-light text-dark border">{{ $journalEntry->source_module_name }}</span></span></li>
                            <li><span class="label">Description</span><span>{{ $journalEntry->Description }}</span></li>
                            </ul>
                    </div>
                    </div>

                    <div class="col-lg-6 print-cards-col">
                        <div class="audit-trail-card h-100">
                            <h6 class="mb-3 text-primary"><i class="fas fa-history me-2"></i>Audit Trail</h6>
                            <ul class="audit-list">
                                <li><span class="label">Created By</span><span>{{ $journalEntry->createdBy->Name ?? 'System' }} — {{ \Carbon\Carbon::parse($journalEntry->CreatedOn)->format('d M Y H:i') }}</span></li>
                                @if($journalEntry->ModifiedBy && $journalEntry->ModifiedBy != $journalEntry->CreatedBy)
                                    <li><span class="label">Last Modified By</span><span>{{ $journalEntry->modifiedBy->Name ?? 'System' }} — {{ \Carbon\Carbon::parse($journalEntry->ModifiedOn)->format('d M Y H:i') }}</span></li>
                                @endif
                                @if($journalEntry->ApprovalStatus == 'posted' || $journalEntry->ApprovalStatus == 'rejected')
                                    <li><span class="label">Approval Action</span><span>{{ ucfirst($journalEntry->ApprovalStatus) }} by {{ $journalEntry->modifiedBy->Name ?? $journalEntry->createdBy->Name ?? 'System' }} — {{ \Carbon\Carbon::parse($journalEntry->ModifiedOn)->format('d M Y H:i') }}</span></li>
                                @endif
                                @if($journalEntry->IsReversed && $journalEntry->reversed_by)
                                    <li><span class="label">Reversed By</span><span>{{ $journalEntry->reversed_by->Name ?? 'System' }}@if($journalEntry->reversal_info && $journalEntry->reversal_info->CreatedOn) — {{ \Carbon\Carbon::parse($journalEntry->reversal_info->CreatedOn)->format('d M Y H:i') }}@endif</span></li>
                                @elseif($journalEntry->Type === 'reversing')
                                    <li><span class="label">Reversal Journal</span><span>This is a reversing journal — {{ \Carbon\Carbon::parse($journalEntry->CreatedOn)->format('d M Y H:i') }}</span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Journal Lines --}}
                <div class="journal-lines-card">
                    <div class="journal-lines-header">
                        <i class="fas fa-list me-2"></i>Journal Lines
                    </div>
                <div class="table-responsive">
                        <table class="table table-sm mb-0 je-table">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th class="col-gl">GL Account</th>
                            <th class="col-debit">Debit</th>
                            <th class="col-credit">Credit</th>
                            <th class="col-amount">Amount</th>
                            <th class="col-narr">Narration</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($journalEntry->journalLines as $index => $line)
                            @php
                                $isDebit = $line->Debit > 0 || $line->IsDebit;
                                $rowClass = $isDebit ? 'row-debit' : 'row-credit';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td>{{ $index + 1 }}</td>
                                <td>{{$line->glAccount->GLCode}}  <small>({{ $line->glAccount->GLName }})</small></td>
                                <td class="fw-semibold text-start">
                                    {{ number_format($line->Debit, 2) }}
                                </td>
                                <td class="fw-semibold text-start">
                                    {{ number_format($line->Credit, 2) }}
                                </td>
                                <td class="text-start">{{ number_format($line->Amount, 2) }}</td>
                                <td>
                                    <details class="narration-details">
                                        <summary class="narration-summary">{{ Str::limit($line->Narration, 50) }}</summary>
                                        <div class="narration-full">{{ $line->Narration }}</div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if($journalEntry->ApprovalStatus=='draft')
                    <div class="mt-4 d-flex justify-content-end gap-3 no-print">
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#actionRejectModal" data-action="reject">
                            <i class="fas fa-times-circle me-1"></i> Reject
                        </button>
                        <button class="btn btn-outline-success" data-bs-toggle="modal"
                                data-bs-target="#actionApproveModal" data-action="approve">
                            <i class="fas fa-check-circle me-1"></i> Approve
                        </button>
                    </div>
                @endif

                <div class="mt-4 text-center no-print">
                    <a href="{{ route('journalentry.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Journal Entries
                    </a>
                </div>
            </div>
        </div>
    </div>


    @if($journalEntry->ApprovalStatus==='draft')
        {{-- Approve Modal --}}
        <div class="modal fade" id="actionApproveModal" tabindex="-1" aria-labelledby="actionModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('journalApproval', $journalEntry->Id) }}">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="action_type" value="approve" id="actionType">
                    <input type="hidden" name="journalID" value="{{ $journalEntry->Id}}" id="actionType">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-success" id="actionModalLabel">Confirm Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required
                                          placeholder="Enter reason here..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" id="postBtn" type="submit"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                                Approve
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="actionRejectModal" tabindex="-1" aria-labelledby="actionModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('journalApproval', $journalEntry->Id) }}">
                    @csrf
                    @method('Post')
                    <input type="hidden" name="action_type" value="reject" id="actionType">
                    <input type="hidden" name="journalID" value="{{ $journalEntry->Id}}" id="actionType">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-danger" id="actionModalLabel">Confirm Rejection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required
                                          placeholder="Enter reason here..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" id="postBtn" type="submit"
                                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                                Reject
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection
