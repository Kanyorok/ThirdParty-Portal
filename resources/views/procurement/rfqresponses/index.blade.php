@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'RFQ Responses')
@section('content')
    <div class="container">
        <h3></h3>

        <div class="container mt-3">
            <a href="{{ route('rfqresponses.create') }}" class="btn btn-primary mb-2">Create RFQ Response</a>

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>RFQ Response Number</th>
                    <th>RFQ Number</th>
                    <th>Supplier Name</th>
                    <th>Items Quoted</th>
                    <th>Total Price</th>
                    <th>Days to Delivery</th>
                    <th>Delivery Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rfqResponses as $response)
                    @php
                        $deliveryDate = Carbon::parse($response->CreatedOn)->addDays((int) $response->DurationDays)->startOfDay();
                        $today = Carbon::now()->startOfDay();
                        $daysRemaining = $today->diffInDays($deliveryDate, false);
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $response->RFQResponseNumber }}</td>
                        <td>{{ $response->RFQNumber }}</td>
                        <td>{{ $response->SupplierName }}</td>
                        <td>
                            @if ($response->items->isNotEmpty())
                                <ul class="mb-0">
                                    @foreach($response->items as $item)
                                        <li>
                                            {{ $item->ItemName }} — Price: {{ number_format($item->QuotedPrice, 2) }} —
                                            Total: {{ number_format($item->TotalPayable, 2) }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p>No items found</p>
                            @endif
                        </td>
                        <td>{{ $response->Currency }}{{ number_format($response->TotalPayable, 2) }}</td>
                        <td>
                            @if ($daysRemaining > 0)
                                {{ $daysRemaining }} day{{ $daysRemaining > 1 ? 's' : '' }} remaining
                            @elseif ($daysRemaining === 0)
                                Delivery is today
                            @else
                                Delivered {{ abs($daysRemaining) }} day{{ abs($daysRemaining) > 1 ? 's' : '' }} ago
                            @endif
                        </td>
                        <td>{{ $deliveryDate->format('d/m/Y') }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#viewModal{{ $response->Id }}">View
                            </button>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $response->Id }}">Edit
                            </button>
                            <form action="{{ route('rfqresponses.destroy', $response->Id) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{-- Modals --}}
            @foreach($rfqResponses as $response)
                {{-- View Modal --}}
                <div class="modal fade" id="viewModal{{ $response->Id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $response->Id }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">RFQ Response Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-borderless">
                                    <tr><th>RFQ Response Number</th><td>{{ $response->RFQResponseNumber }}</td></tr>
                                    <tr><th>RFQ Number</th><td>{{ $response->RFQNumber }}</td></tr>
                                    <tr><th>Supplier Name</th><td>{{ $response->SupplierName }}</td></tr>
                                    <tr><th>Currency</th><td>{{ $response->Currency }}</td></tr>
                                    <tr><th>Total Payable</th><td>{{ number_format($response->TotalPayable, 2) }}</td></tr>
                                    <tr><th>Duration (Days)</th><td>{{ $response->DurationDays }}</td></tr>
                                </table>

                                <h6>Quoted Items</h6>
                                <table class="table table-bordered">
                                    <thead>
                                    <tr><th>Item Name</th><th>UOM</th><th>Qty</th><th>Quoted Price</th><th>Total</th></tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($response->items as $item)
                                        <tr>
                                            <td>{{ $item->ItemName }}</td>
                                            <td>{{ $item->uom->Name ?? 'N/A' }}</td>
                                            <td>{{ $item->Quantity }}</td>
                                            <td>{{ number_format($item->QuotedPrice, 2) }}</td>
                                            <td>{{ number_format($item->TotalPayable, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Edit Modal --}}
                <div class="modal fade" id="editModal{{ $response->Id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $response->Id }}" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <form action="{{ route('rfqresponses.update', $response->Id) }}" method="POST" class="modal-content">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit RFQ Response</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col"><label>RFQ Number</label><input type="text" name="RFQNumber" class="form-control" value="{{ $response->RFQNumber }}" readonly></div>
                                    <div class="col"><label>Supplier Name</label><input type="text" name="SupplierName" class="form-control" value="{{ $response->SupplierName }}" readonly></div>
                                    <div class="col"><label>Currency</label><input type="text" name="Currency" class="form-control" value="{{ $response->Currency }}" readonly></div>
                                    <div class="col"><label>Duration (Days)</label><input type="number" name="DurationDays" class="form-control" value="{{ $response->DurationDays }}" required></div>
                                    <div class="col"><label>Total Payable</label><input type="number" step="0.01" name="TotalPayable" class="form-control" value="{{ $response->TotalPayable }}" readonly></div>
                                </div>

                                <h6>Items</h6>
                                <table class="table table-bordered">
                                    <thead><tr><th>Item Name</th><th>UOM</th><th>Qty</th><th>Quoted Price</th><th>Total</th></tr></thead>
                                    <tbody>
                                    @foreach ($response->items as $item)
                                        <tr>
                                            <input type="hidden" name="RequisitionItems[{{ $loop->index }}][id]" value="{{ $item->Id }}">
                                            <td><input type="text" name="RequisitionItems[{{ $loop->index }}][name]" class="form-control" value="{{ $item->ItemName }}" readonly></td>
                                            <td><input type="text" name="RequisitionItems[{{ $loop->index }}][uom]" class="form-control" value="{{ $item->uom->Name ?? 'N/A' }}" readonly></td>
                                            <td><input type="number" name="RequisitionItems[{{ $loop->index }}][quantity]" class="form-control" value="{{ $item->Quantity }}" readonly></td>
                                            <td><input type="number" step="0.01" name="RequisitionItems[{{ $loop->index }}][quotedprice]" class="form-control" value="{{ $item->QuotedPrice }}" required></td>
                                            <td><input type="number" step="0.01" name="RequisitionItems[{{ $loop->index }}][totalpayable]" class="form-control" value="{{ $item->TotalPayable }}" readonly></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modals = document.querySelectorAll('.modal');

            modals.forEach(modal => {
                modal.addEventListener('input', function (e) {
                    if (e.target.name.includes('[quotedprice]')) {
                        const row = e.target.closest('tr');
                        const quotedPriceInput = e.target;
                        const quantityInput = row.querySelector('input[name*="[quantity]"]');
                        const totalPayableInput = row.querySelector('input[name*="[totalpayable]"]');

                        const quantity = parseFloat(quantityInput.value) || 0;
                        const quotedPrice = parseFloat(quotedPriceInput.value) || 0;
                        const itemTotal = (quantity * quotedPrice).toFixed(2);

                        totalPayableInput.value = itemTotal;

                        // Update overall TotalPayable (top field)
                        updateOverallTotal(modal);
                    }
                });
            });

            function updateOverallTotal(modal) {
                const totalInputs = modal.querySelectorAll('input[name*="[totalpayable]"]');
                let sum = 0;

                totalInputs.forEach(input => {
                    sum += parseFloat(input.value) || 0;
                });

                const overallTotalField = modal.querySelector('input[name="TotalPayable"]');
                overallTotalField.value = sum.toFixed(2);
            }
        });
    </script>
@endsection
