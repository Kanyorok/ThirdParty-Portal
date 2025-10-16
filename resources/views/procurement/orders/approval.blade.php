@extends('layouts.app')
@section('title', 'Approve Purchase Order')
@section('content')

    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Approve Purchase Order</h5>
            <a href="{{ route('purchaseOrder.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i>
                Back</a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                @if(isset($orderInfo))
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>LPO No:</strong> {{ $orderInfo->ExtOrdNum ?? '--' }}</div>
                        <div class="col-md-4"><strong>Order No:</strong> {{ $orderInfo->OrderNo ?? '--' }}</div>
                        <div class="col-md-4">
                            <strong>Date:</strong> {{ isset($orderInfo->OrderDate) ? \Carbon\Carbon::parse($orderInfo->OrderDate)->format('d/m/Y') : '--' }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <strong>Supplier:</strong> {{ $orderInfo->SupplierName ?? $orderInfo->TradingName ?? ('Supplier #' . ($orderInfo->SupplierId ?? '')) }}
                        </div>
                        <div class="col-md-6"><strong>Payment Terms:</strong> {{ $orderInfo->TermsDescription ?? '--' }}
                        </div>
                </div>

                    <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax %</th>
                            <th class="text-end">Discount %</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(($lineInfo ?? []) as $i => $line)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $line->ItemName ?? ('#'.$line->ItemId) }}</td>
                                <td>{{ $line->ItemDescription ?? '' }}</td>
                                <td class="text-end">{{ number_format((float)($line->Quantity ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($line->UnitPrice ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($line->Tax ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($line->Discount ?? 0), 2) }}</td>
                                <td class="text-end">{{ number_format((float)($line->LineTotal ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        @if(!($isFullyApproved ?? false))
            <form action="{{ route('purchaseOrder.approve', $orderInfo->Id ?? 0) }}" method="POST">
                @csrf
                <input type="hidden" name="document_type" value="purchase_order">
                <input type="hidden" name="order_total" value="{{ $orderInfo->OrdTotIncl ?? 0 }}">
                <input type="hidden" name="order_id" value="{{ $orderInfo->Id ?? 0 }}">
                <input type="hidden" name="action" value="approve">
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                </div>
            </form>
        @else
            <div class="alert alert-success d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-check-circle me-1"></i>
                    This purchase order is fully approved.
                    @if(!empty($approvedBy))
                        <br><small>Approved by: {{ implode(', ', $approvedBy) }}</small>
                    @endif
            </div>
        </div>
        @endif
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
