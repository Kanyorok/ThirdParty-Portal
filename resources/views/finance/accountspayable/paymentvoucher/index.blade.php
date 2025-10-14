@extends('layouts.app')
@section('title', 'Payment Vouchers')

@section('content')
    <div class="container my-3">
        <!-- Card for Payment Vouchers -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header border bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="fab fa-wpforms me-1"></i> Payment Vouchers</h5>
                <a href="{{ route('paymentvoucher.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> Add Voucher
                </a>
            </div>

            <div class="card-body p-3">
                <form action="{{ route('paymentvoucher.index') }}" method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Voucher No</label>
                        <input type="text" class="form-control form-control-sm" name="voucher_no" value="{{ request('voucher_no') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Invoice #</label>
                        <input type="text" class="form-control form-control-sm" name="invoice_number" value="{{ request('invoice_number') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Payment Method</label>
                        <input type="text" class="form-control form-control-sm" name="payment_method" value="{{ request('payment_method') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select class="form-select form-select-sm" name="approval_status">
                            <option value="all" {{ request('approval_status')=='all' ? 'selected' : '' }}>All</option>
                            @foreach(['draft','posted','rejected'] as $s)
                                <option value="{{ $s }}" {{ request('approval_status')==$s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Payment Type</label>
                        <input type="text" class="form-control form-control-sm" name="payment_type" value="{{ request('payment_type') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Min Amount</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="amount_min" value="{{ request('amount_min') }}">
                    </div>
                    <div class="col-12 d-flex justify-content-end mt-2">
                        <button type="submit" class="btn btn-sm btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="{{ route('paymentvoucher.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
                <!-- Payment Vouchers Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped text-center"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Voucher No</th>
                            <th>Invoice Ref</th>
                            <th>Payment Method</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th>Payment Type</th>
                            <th>Description</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($vouchers as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->VoucherNo ?? '-' }}</td>
                                <td>{{ $item->invoice->InvoiceNumber ?? '-' }}</td>
                                <td>{{ $item->PaymentMethod ?? '-' }}</td>
                                <td class="text-end">{{ $item->TotalAmount ? number_format($item->TotalAmount, 2) : '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($item->ApprovalStatus) {
                                            'posted' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'draft' => 'bg-warning text-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($item->ApprovalStatus) ?? 'Pending' }}
                                        </span>
                                </td>
                                <td>{{ $item->PaymentType ?? '-' }}</td>
                                <td title="{{ $item->Description ?? '-' }}" style="white-space: nowrap;">
                                    {{ $item->Description ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('paymentvoucher.show', $item->Id) }}"
                                       class="btn btn-sm btn-outline-info me-1"
                                       title="View Voucher">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($item->ApprovalStatus === 'draft')
                                        <a href="{{ route('paymentvoucher.edit', $item->Id) }}"
                                        class="btn btn-sm btn-outline-primary me-1"
                                        title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('paymentvoucher.edit', $item->Id) }}"
                                            class="btn btn-sm btn-outline-primary me-1 disabled"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif

                                    @if($item->ApprovalStatus === 'draft')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger custom-delete-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#customDeleteConfirmModal"
                                                data-name="{{$item->VoucherNo}}"    {{-- Pass item name --}}
                                                title="Delete Voucher"
                                                data-route="{{ route('paymentvoucher.destroy', $item->Id) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger custom-delete-btn disabled"
                                            data-bs-toggle="modal"
                                            data-bs-target="#customDeleteConfirmModal"
                                            data-name="{{$item->VoucherNo}}"    {{-- Pass item name --}}
                                            title="Delete Voucher"
                                            data-route="{{ route('paymentvoucher.destroy', $item->Id) }}">
                                            <i  class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-0">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <i>No payment vouchers found.</i>
                                        </p>
                                        <a href="{{ route('paymentvoucher.create') }}" class="btn btn-info px-4 py-2">
                                            <i class="fas fa-plus-circle me-2"></i> Add Voucher
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if(method_exists($vouchers,'links'))
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $vouchers->firstItem() ?? 0 }} to {{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() ?? $vouchers->count() }} entries
                        </div>
                        <div>
                            {{ $vouchers->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@include('components.modals.delete-confirm')
@endsection

@section('styles')
    <style>
        /* Hover effect for rows */
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }

        /* Compact buttons */
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Table padding */
        .table-sm th, .table-sm td {
            padding: 0.5rem;
        }

        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.875rem;
            }
            .btn-sm {
                padding: 0.2rem 0.4rem;
            }
        }
    </style>
@endsection
