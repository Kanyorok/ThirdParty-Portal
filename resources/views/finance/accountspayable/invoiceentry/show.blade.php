@extends('layouts.app')
@section('title', 'Invoice Details')

@section('content')
    <div class="container my-3">
        <!-- Invoice Header -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-file-invoice me-2"></i> Invoice #{{ $invoice->InvoiceNumber ?? 'N/A' }}
                </h5>
                <div class="d-flex justify-content-between align-items-center">
{{--                    <h5 class="mb-0 text-info">📘 Journal Entry Details</h5>--}}
                    <span class="text-end">Approval Status:
                        @if($invoice->ApprovalStatus === 'posted')
                            <span class="badge bg-success">Approved</span>
                        @elseif($invoice->ApprovalStatus === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @elseif($invoice->ApprovalStatus === 'draft')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </span>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- Invoice Metadata -->
                <div class="row g-2">
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-light p-2 rounded">
                            <strong class="text-dark">Supplier:</strong>
                            <div class="text-muted small">{{ $invoice->supplier->SupplierName ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-light p-2 rounded">
                            <strong class="text-dark">Currency:</strong>
                            <div class="text-muted small">
                                {{ $invoice->currency->Code ?? '-' }} ({{ $invoice->currency->Symbol ?? '' }})
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-light p-2 rounded">
                            <strong class="text-dark">Order:</strong>
                            @if($invoice->order)
                                <div class="small">{{ $invoice->order->OrderNo }} - {{ $invoice->order->Description }}</div>
                                <div class="text-muted small">Total: {{ number_format($invoice->order->OrdTotExcl, 2) }}</div>
                            @else
                                <div class="text-muted small">-</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-light p-2 rounded">
                            <strong class="text-dark">GRN:</strong>
                            <div class="text-muted small">{{ $invoice->grn->GRNID ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-light p-2 rounded">
                            <strong class="text-dark">Invoice Date:</strong>
                            <div class="text-muted small">{{ $invoice->InvoiceDate ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('Y-m-d') : '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="bg-light p-2 rounded">
                            <strong class="text-dark">Amount:</strong>
                            <div class="fw-bold text-primary small">{{ number_format($invoice->InvoiceAmount, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-2 rounded">
                            <strong class="text-dark">Description:</strong>
                            <p class="mb-0 text-muted small">{{ $invoice->Description ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PO Lines -->
        @if(!empty($poItems) && count($poItems))
            <div class="card shadow-sm border-0 rounded-3 mt-3">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i> Purchase Order Lines</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                            <tr>
                                <th class="px-2 py-1">#</th>
                                <th class="px-2 py-1">Item Name</th>
                                <th class="px-2 py-1">Description</th>
                                <th class="px-2 py-1 text-end">Quantity</th>
                                <th class="px-2 py-1 text-end">Unit Cost</th>
                                <th class="px-2 py-1 text-end">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($poItems as $index => $item)
                                <tr>
                                    <td class="px-2 py-1">{{ $index + 1 }}</td>
                                    <td class="px-2 py-1">{{ $item->ItemName ?? '-' }}</td>
                                    <td class="px-2 py-1">{{ $item->Description ?? '-' }}</td>
                                    <td class="px-2 py-1 text-end">{{ number_format($item->Quantity, 2) }}</td>
                                    <td class="px-2 py-1 text-end">{{ number_format($item->UnitCost, 2) }}</td>
                                    <td class="px-2 py-1 text-end">{{ number_format($item->Quantity * $item->UnitCost, 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        @endif

        {{-- Action Buttons --}}
        @if($invoice->ApprovalStatus==='draft')
            <div class="mt-4 d-flex justify-content-end gap-3">
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#actionRejectModal" data-action="reject">
                    <i class="fas fa-times-circle me-1"></i> Reject
                </button>
                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#actionApproveModal" data-action="approve">
                    <i class="fas fa-check-circle me-1"></i> Approve
                </button>
            </div>
        @endif
    </div>



    @if($invoice->ApprovalStatus==='draft')
        {{-- Approve Modal --}}
        <div class="modal fade" id="actionApproveModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('journalApproval', $invoice->Id) }}">
                    @csrf
                    @method('POST')
                    <input type="hidden" name="action_type" value="approve" id="actionType">
                    <input type="hidden" name="journalID" value="{{ $invoice->Id}}" id="actionType">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-success" id="actionModalLabel">Confirm Approval</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required placeholder="Enter reason here..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" id="postBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">Approve</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="actionRejectModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('journalApproval', $invoice->Id) }}">
                    @csrf
                    @method('Post')
                    <input type="hidden" name="action_type" value="reject" id="actionType">
                    <input type="hidden" name="journalID" value="{{ $invoice->Id}}" id="actionType">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-danger" id="actionModalLabel">Confirm Rejection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required placeholder="Enter reason here..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" id="postBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">Reject</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
