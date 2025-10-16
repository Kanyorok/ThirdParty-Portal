@extends('layouts.app')


@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="container py-4">

        {{-- OFFICIAL DOCUMENT --}}
        <div id="note-print-section" class="official-document">

            {{-- Header --}}
            <div class="doc-header d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="mb-1">{{ strtoupper($note->NoteType) }} NOTE</h2>
                    <p class="mb-0">Document No: {{ strtolower($note->NoteType) === 'credit' ? 'CN-' : 'DN-' }}{{ $note->CDNumber }}</p>
                    <p class="mb-0">Date: {{ \Carbon\Carbon::parse($note->NoteDate)->format('d-m-Y') }}</p>
                    <p class="mb-0">
                        Status:
                        <span class="badge
                        @if(strtolower($note->ApprovalStatus) === 'posted') bg-success
                        @elseif(strtolower($note->ApprovalStatus) === 'rejected') bg-danger
                        @elseif(strtolower($note->ApprovalStatus) === 'draft') bg-warning
                        @else bg-secondary
                        @endif">
                        {{ ucfirst($note->ApprovalStatus) }}
                    </span>
                    </p>
                </div>
                <div class="text-end">
                    {{-- Optional logo (remove if not needed) --}}
                    {{-- <img src="{{ asset('images/company-logo.png') }}" alt="Company Logo" height="60"> --}}
                    <h4 class="fw-bold mt-2"><i>{{$note->invoice->currency->Code}} </i> {{ number_format($note->NoteAmount, 2) }}</h4>
                </div>
            </div>

            <hr>

            {{-- Supplier / Invoice --}}
            <table class="table table-bordered table-sm mb-4">
                <thead class="table-light">
                <tr>
                    <th style="width:50%">Supplier</th>
                    <th style="width:50%">Invoice</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        @if($note->invoice && $note->invoice->thirdParty)
                            <strong>{{ $note->invoice->thirdParty->TradingName ?? $note->invoice->thirdParty->ThirdPartyName }}</strong><br>
                            {{ $note->invoice->thirdParty->Email ?? '-' }}<br>
                            {{ $note->invoice->thirdParty->Phone ?? '-' }}<br>
                            {{ $note->invoice->thirdParty->Address ?? '-' }}<br>
                            @php($taxNo = $note->invoice->thirdParty->TaxNumber ?? $note->invoice->thirdParty->RegistrationNumber ?? null)
                            <span>Tax No: {{ $taxNo ?? '-' }}</span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($note->invoice)
                            <strong>{{ $note->invoice->InvoiceNumber }}</strong><br>
                            Date: {{ \Carbon\Carbon::parse($note->invoice->InvoiceDate)->format('d-m-Y') }}<br>
                            Amount: {{ number_format($note->invoice->InvoiceAmount, 2) }} {{ $note->invoice->currency->Code ?? '' }}<br>
                            PO Ref: {{ $note->invoice->order->OrderNo ?? $note->invoice->POReference ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>

            {{-- Description --}}
            <div class="mb-4">
                <h6 class="fw-semibold">Description</h6>
                <p class="border p-2 mb-0">{{ $note->Description ?? '-' }}</p>
            </div>

            {{-- Prepared / Authorized --}}
            <div class="row mt-5">
                <div class="col-md-6 text-center">
{{--                    <p>__________________________</p>--}}
                    <p class="mb-0">Prepared By</p>
                    <small class="text-muted">
                        {{ $note->createdBy->Name ?? 'N/A' }}
                        @isset($note->CreatedOn)
                            • {{ \Carbon\Carbon::parse($note->CreatedOn)->format('d-m-Y H:i') }}
                        @endisset
                    </small>
                </div>
                <div class="col-md-6 text-center">
{{--                    <p>__________________________</p>--}}
                    <p class="mb-0">
                        @if(strtolower($note->ApprovalStatus) === 'posted')
                            Authorized By
                        @elseif(strtolower($note->ApprovalStatus) === 'rejected')
                            Rejected By
                        @else
                            Status
                        @endif
                    </p>
                    <small class="text-muted">
                        @if(strtolower($note->ApprovalStatus) === 'posted' or $note->ApprovalStatus === 'rejected')
                            {{ $note->modifiedBy->Name ?? 'N/A' }}
                            @isset($note->ModifiedOn)
                                • {{ \Carbon\Carbon::parse($note->ModifiedOn)->format('d-m-Y H:i') }}
                            @endisset
                        @else
                            Pending
                        @endif
                    </small>
                </div>
            </div>

        </div>

            {{-- Actions (hidden on print) --}}
            <div class="d-flex justify-content-end gap-2 no-print mt-3">
                <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                @if($note->ApprovalStatus==='draft')
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times me-1"></i> Reject
                    </button>
                @endif
            </div>


    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('cdnote.approve', $note->Id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Approve {{ ucfirst($note->NoteType) }} Note</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Reason</label>
                        <textarea name="Reason" class="form-control" rows="3" required></textarea>
                        <p class="text-muted mt-2 mb-0">This will mark the note as approved.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-success" id="postBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">Approve</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('cdnote.reject', $note->Id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Reject {{ ucfirst($note->NoteType) }} Note</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Reason</label>
                        <textarea name="Reason" class="form-control" rows="3" required></textarea>
                        <p class="text-muted mt-2 mb-0">This will mark the note as rejected.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" id="postBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Print Styles --}}
        <style>
            .official-document {
                background: #fff;
                padding: 22px;
                border: 1px solid #000;
            }
            .doc-header h2 { font-size: 1.5rem; font-weight: 700; }

            @media print {
                html, body {
                    margin: 0 !important;
                    padding: 0 !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                body * { visibility: hidden; }
                #note-print-section, #note-print-section * { visibility: visible; }
                #note-print-section {
                    position: absolute;
                    inset: 0;
                    width: 100%;
                    border: 1px solid #000;
                    box-shadow: none !important;
                    padding: 28px;
                }
                .no-print { display: none !important; }
            }
        </style>

@endsection
