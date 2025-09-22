@extends('layouts.app')
@section('title','Receipts')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-receipt text-info me-2"></i> Receipts
                </h6>
                <a href="{{ route('receiptsposting.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i> New Receipt
                </a>
            </div>

            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle text-center">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Receipt No.</th>
                            <th>Customer</th>
                            <th>Payment Method</th>
                            <th>Reference No.</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>RCPT-2025-001</td>
                            <td>ABC Properties Ltd</td>
                            <td>Bank Transfer</td>
                            <td>TRX-883728</td>
                            <td>5,000,000 (KES)</td>
                            <td>2025-08-20</td>
                            <td><span class="badge bg-success">Confirmed</span></td>
                            <td>
                                <a href="{{ route('receiptsposting.show',1) }}" class="btn btn-sm btn-outline-info"
                                   title="View Receipt">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>RCPT-2025-002</td>
                            <td>XYZ Traders Ltd</td>
                            <td>Cheque</td>
                            <td>CHQ-44882</td>
                            <td>750,000 (KES)</td>
                            <td>2025-08-21</td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td>
                                <a href="{{ route('receiptsposting.show',2) }}" class="btn btn-sm btn-outline-info"
                                   title="View Receipt">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
