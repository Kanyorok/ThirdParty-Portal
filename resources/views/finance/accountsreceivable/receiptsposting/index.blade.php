@extends('layouts.app')
@section('title','Receipts')

@section('content')
    <div class="container my-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-muted">
                    <i class="fas fa-receipt text-info me-2"></i> Receipt Posting
                </h6>
                <a href="{{ route('receiptsposting.create') }}" class="btn btn-sm btn-info shadow-sm">
                    <i class="fas fa-plus me-1"></i> New Receipt
                </a>
            </div>

            <div class="card-body px-4 py-3">
                <form action="{{ route('receiptsposting.index') }}" method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Receipt No</label>
                        <input type="text" class="form-control form-control-sm" name="receipt_number" value="{{ request('receipt_number') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Customer</label>
                        <input type="text" class="form-control form-control-sm" name="customer" value="{{ request('customer') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="all" {{ request('status')=='all' ? 'selected' : '' }}>All</option>
                            @foreach(['Draft','Posted'] as $s)
                                <option value="{{ $s }}" {{ request('status')==$s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Min Amount</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="amount_min" value="{{ request('amount_min') }}">
                    </div>
                    <div class="col-12 d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="{{ route('receiptsposting.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="table-light">
                        <tr class="text-center small text-muted fw-semibold">
                            <th>#</th>
                            <th>Receipt No.</th>
                            <th>Customer</th>
                            <th>Receipt Date</th>
                            <th>Amount Received</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Invoices</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="text-center">
                        @forelse($receipts as $receipt)
                            <tr>
                                <td>{{ ($receipts->currentPage() - 1) * $receipts->perPage() + $loop->iteration }}</td>
                                <td class="fw-medium">{{ $receipt->ReceiptNumber }}</td>
                                <td>{{ $receipt->customer->ThirdPartyName ?? '-' }}</td>
                                <td>{{ $receipt->ReceiptDate->format('M d, Y') }}</td>
                                <td>KSh {{ number_format($receipt->AmountReceived, 2) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $receipt->paymentMethod->Description ?? $receipt->PaymentMethod }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $status = strtolower($receipt->Status ?? 'draft');
                                        $statusClass = match($status) {
                                            'posted' => 'bg-success',
                                            'draft' => 'bg-warning text-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-2 py-1">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        <div class="fw-semibold">{{ $receipt->allocations->count() }} invoice(s)</div>
                                        <div class="text-muted">KSh {{ number_format($receipt->total_allocated, 2) }} applied</div>
                                        @if($receipt->UnappliedAmount > 0)
                                            <div class="text-warning small">+KSh {{ number_format($receipt->UnappliedAmount, 2) }} to wallet</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('receiptsposting.show', $receipt->Id) }}" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($receipt->Status === 'Draft')
                                            <button type="button" class="btn btn-sm btn-outline-success" title="Post Receipt"
                                                    data-bs-toggle="modal" data-bs-target="#postReceiptModal"
                                                    data-receipt-id="{{ $receipt->Id }}"
                                                    data-receipt-number="{{ $receipt->ReceiptNumber }}">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-secondary" onclick="window.open('{{ route('receiptsposting.show', $receipt->Id) }}', '_blank')" title="Print">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-0">
                                    <div class="text-center p-5 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            No receipts have been posted yet.
                                        </p>
                                        <a href="{{ route('receiptsposting.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Create First Receipt
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($receipts->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $receipts->firstItem() }} to {{ $receipts->lastItem() }} of {{ $receipts->total() }} results
                        </div>
                        <nav>
                            {{ $receipts->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>

        {{-- Post Receipt Modal --}}
        <div class="modal fade" id="postReceiptModal" tabindex="-1" aria-labelledby="postReceiptModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" id="postReceiptForm">
                    @csrf
                    <input type="hidden" name="action_type" value="approve">
                    <div class="modal-content rounded-4 shadow">
                        <div class="modal-header bg-light border-0">
                            <h5 class="modal-title text-success" id="postReceiptModalLabel">Post Receipt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Are you sure you want to post receipt <strong id="receiptNumberSpan"></strong>?</p>
                            <p class="small text-muted mb-3">This will create GL entries and the receipt cannot be modified afterward.</p>
                            <div class="mb-3">
                                <label for="reason" class="form-label">Posting Reason</label>
                                <textarea class="form-control" name="Reason" id="reason" rows="3" required 
                                    placeholder="Enter reason for posting...">Receipt verified and ready for GL posting</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" type="submit" id="postBtn">
                                <i class="fas fa-check-circle me-1"></i> Post Receipt
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }
        body, .card, .table { font-family: var(--font-sans); }
        .table-hover tbody tr:hover {
            background-color: #fafafa;
            transition: background-color .2s ease-in-out;
        }
        .card { border-radius: .75rem; }
        .btn-sm { padding: .25rem .55rem; }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const postReceiptModal = document.getElementById('postReceiptModal');
            const postReceiptForm = document.getElementById('postReceiptForm');
            const receiptNumberSpan = document.getElementById('receiptNumberSpan');
            const postBtn = document.getElementById('postBtn');

            postReceiptModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const receiptId = button.getAttribute('data-receipt-id');
                const receiptNumber = button.getAttribute('data-receipt-number');
                
                receiptNumberSpan.textContent = receiptNumber;
                postReceiptForm.action = `/finance/receiptsposting/${receiptId}/approve`;
            });

            postReceiptForm.addEventListener('submit', function(e) {
                if (postBtn.disabled) {
                    e.preventDefault();
                    return;
                }

                // Disable submit button and show loading state
                postBtn.disabled = true;
                postBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Posting...';
                
                // Re-enable after 10 seconds as failsafe
                setTimeout(() => {
                    if (postBtn.disabled) {
                        postBtn.disabled = false;
                        postBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Post Receipt';
                    }
                }, 10000);
            });
        });
    </script>
@endsection