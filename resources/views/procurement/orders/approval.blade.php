@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3>Purchase Order Approval</h3>
            </div>

            <div class="card-body">

                <!-- PO Summary Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Supplier Information</h5>
                        <p><strong>Supplier:</strong> {{$orderInfo->SupplierName ?? 'N/A'}}</p>
                        {{--                        <p><strong>Address:</strong> {{ $purchaseOrder->supplier->address }}</p>--}}
                    </div>

                    <div class="col-md-6">
                        <h5>PO Details</h5>
                        <p><strong>LPO Number:</strong> {{$orderInfo->OrderNo ?? 'N/A'}}</p>
                        <p>
                            <strong>Date:</strong> {{ isset($orderInfo->OrderDate) ? \Carbon\Carbon::parse($orderInfo->OrderDate)->format('Y-m-d') : '' }}
                        </p>
                        <p><strong>Reference Number:</strong> {{$orderInfo->ExtOrdNum ?? 'N/A'}}</p>
                        <p><strong>Priority:</strong> <span
                                class="badge badge-danger">{{$orderInfo->Priority ?? 'N/A'}}</span></p>
                        <p><strong>Payment Terms:</strong> {{$orderInfo->Priority ?? 'N/A'}}</p>
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
                        @foreach($lineInfo as $line)
                            <tr>
                                <td>{{$line ->ItemType}}</td>
                                <td>{{$line ->ItemName}}</td>
                                <td>{{$line ->Description}}</td>
                                <td>{{$line ->fQuantity}}</td>
                                <td>{{ number_format($line ->fUnitPriceExcl, 2) }}</td>
                                <td>{{ number_format($line ->fTaxRate, 2) }}</td>
                                <td>{{ number_format($line ->fLineDiscount, 2) }}</td>
                                <td>{{ number_format($line ->LineTotal, 2) }}</td>
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
                                <td>{{ number_format($orderInfo->OrdTotExcl, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Tax Amount:</th>
                                <td>{{ number_format($orderInfo->OrdTotTax , 2) }}</td>
                            </tr>
                            <tr class="table-active">
                                <th>Inclusive Total:</th>
                                <td><strong>{{ number_format($orderInfo->OrdTotIncl, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Notes Section -->
                @if($orderInfo->OrderNo)
                    <div class="mb-4">
                        <h5>Notes to Supplier</h5>
                        <div class="alert alert-info">
                            {{ $orderInfo->OrderNo }}
                        </div>
                    </div>
                @endif

                <!-- Approval Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <form action="{{ route('purchaseOrder.approve', $orderInfo->Id) }}" method="POST"
                              class="d-inline">
                            @csrf

                            <input type="hidden" name="document_type" value="purchase_order">
                            <input type="hidden" name="order_total" value="{{ $orderInfo->OrdTotIncl }}">
                            <input type="hidden" name="order_id" value="{{ $orderInfo->Id }}">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Approve PO
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger btn-lg ml-2" data-toggle="modal"
                                data-target="#rejectModal">
                            <i class="fas fa-times"></i> Reject PO
                        </button>

                        {{--                        <a href="{{ route('purchaseOrder.approve', $orderInfo->Id) }}" class="btn btn-info btn-lg ml-2">--}}
                        {{--                            <i class="fas fa-print"></i> Print PO--}}
                        {{--                        </a>--}}
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
                <form action="{{ route('purchaseOrder.approve', $orderInfo->Id) }}" method="POST">
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
