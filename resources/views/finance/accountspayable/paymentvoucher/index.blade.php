@extends('layouts.app')
@section('title', 'Payment Vouchers')

@section('content')
    <div class="container my-3">
        <!-- Card for Payment Vouchers -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info"><i class="fab fa-wpforms me-1"></i> Payment Vouchers</h5>
                <a href="{{ route('paymentvoucher.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> Add Voucher
                </a>
            </div>

            <div class="card-body p-3">
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
                                    <a href="{{ route('paymentvoucher.edit', $item->Id) }}"
                                       class="btn btn-sm btn-outline-primary me-1"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('paymentvoucher.destroy', $item->Id) }}" method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this voucher?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
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
            </div>
        </div>
    </div>
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
