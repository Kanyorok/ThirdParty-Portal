@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>Purchase Order Approval</h3>
            </div>

            <div class="card-body">
                <!-- PO Summary Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Supplier Information</h5>
                        <p><strong>Supplier:</strong> {{ $purchaseOrder->supplier->name }}</p>
                        <p><strong>Address:</strong> {{ $purchaseOrder->supplier->address }}</p>
                    </div>

                    <div class="col-md-6">
                        <h5>PO Details</h5>
                        <p><strong>LPO Number:</strong> {{ $purchaseOrder->lpo_number }}</p>
                        <p><strong>Date:</strong> {{ $purchaseOrder->date->format('d/m/Y') }}</p>
                        <p><strong>Reference Number:</strong> {{ $purchaseOrder->reference_number }}</p>
                        <p><strong>Priority:</strong> <span
                                class="badge badge-danger">{{ $purchaseOrder->priority }}</span></p>
                        <p><strong>Payment Terms:</strong> {{ $purchaseOrder->payment_terms }}</p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>Item Type</th>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Tax</th>
                            <th>Discount</th>
                            <th>Line Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($purchaseOrder->items as $item)
                            <tr>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->tax, 2) }}</td>
                                <td>{{ number_format($item->discount, 2) }}</td>
                                <td>{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totals Section -->
                <div class="row justify-content-end">
                    <div class="col-md-4">
                        <table class="table">
                            <tr>
                                <th>Exclusive Total:</th>
                                <td>{{ number_format($purchaseOrder->exclusive_total, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Tax Amount:</th>
                                <td>{{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                            </tr>
                            <tr class="table-active">
                                <th>Inclusive Total:</th>
                                <td><strong>{{ number_format($purchaseOrder->inclusive_total, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Notes Section -->
                @if($purchaseOrder->line_notes)
                    <div class="mb-4">
                        <h5>Notes to Supplier</h5>
                        <div class="alert alert-info">
                            {{ $purchaseOrder->line_notes }}
                        </div>
                    </div>
                @endif

                <!-- Approval Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <form action="{{ route('purchase-orders.approve', $purchaseOrder->id) }}" method="POST"
                              class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Approve PO
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger btn-lg ml-2" data-toggle="modal"
                                data-target="#rejectModal">
                            <i class="fas fa-times"></i> Reject PO
                        </button>

                        <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}"
                           class="btn btn-info btn-lg ml-2">
                            <i class="fas fa-print"></i> Print PO
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Purchase Order</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('purchase-orders.reject', $purchaseOrder->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejection_reason">Reason for Rejection</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"
                                      required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .table th {
            white-space: nowrap;
        }

        .badge {
            font-size: 0.9em;
        }
    </style>
@endsection
