@extends('layouts.app')
@section('title', 'Processed Payments')

@section('content')
    <div class="container my-3">
        <!-- Card for Processed Payments -->
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">
                    <i class="fab fa-wpforms me-1"></i> Payments Processed
                </h5>
                <a href="{{ route('paymentprocessing.create') }}" class="btn btn-info btn-sm p-2">
                    <i class="fas fa-plus me-1"></i> Process Payments
                </a>
            </div>

            <div class="card-body p-3">
                <!-- Payments Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle table-striped text-center"
                           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Payment ID</th>
                            <th>Voucher No</th>
                            <th>Supplier</th>
                            <th class="text-end">Amount Paid</th>
                            <th>Payment Method</th>
                            <th>Bank</th>
                            <th>Payment Date</th>
                            <th>Narration</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>#P-0001</td>
                            <td>VCH-2025-0001</td>
                            <td>ABC Suppliers Ltd</td>
                            <td class="text-end">KES 50,000.00</td>
                            <td>Bank Transfer</td>
                            <td>KCB Main Account</td>
                            <td>2025-07-29</td>
                            <td title="First installment payment">First installment payment</td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-outline-info me-1" title="View Payment">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" title="Edit Payment">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="#" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this payment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <tr>
                            <td>2</td>
                            <td>#P-0002</td>
                            <td>VCH-2025-0002</td>
                            <td>XYZ Traders</td>
                            <td class="text-end">KES 75,000.00</td>
                            <td>Cheque</td>
                            <td>Equity Payments</td>
                            <td>2025-07-29</td>
                            <td title="Final payment for services">Final payment for services</td>
                            <td class="text-center">
                                <a href="#" class="btn btn-sm btn-outline-info me-1" title="View Payment">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" title="Edit Payment">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="#" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this payment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
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
