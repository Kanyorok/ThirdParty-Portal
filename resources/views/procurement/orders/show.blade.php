@extends('layouts.app')
@section('title', 'View Purchase Order')
@section('content')

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Purchase Order</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark" onclick="window.print()"><i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('purchaseOrder.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i>
                Back</a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if(isset($orderInfo))
            <div class="row mb-3">
                <div class="col-md-4"><strong>LPO No:</strong> {{ $orderInfo->ExtOrdNum ?? '--' }}</div>
                <div class="col-md-4"><strong>Order No:</strong> {{ $orderInfo->OrderNo ?? '--' }}</div>
                <div class="col-md-4">
                    <strong>Date:</strong> {{ isset($orderInfo->OrderDate) ? \Carbon\Carbon::parse($orderInfo->OrderDate)->format('d/m/Y') : '--' }}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Supplier:</strong> {{ $orderInfo->SupplierName ?? $orderInfo->TradingName ?? ('Supplier #' . ($orderInfo->SupplierId ?? '')) }}
                </div>
                <div class="col-md-6"><strong>Address:</strong> {{ $orderInfo->SupplierAddress ?? '' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Priority:</strong> {{ $orderInfo->Priority ?? '--' }}</div>
                <div class="col-md-4"><strong>Payment Terms:</strong> {{ $orderInfo->TermsDescription ?? '--' }}
                </div>
                <div class="col-md-4">
                    <strong>Branch:</strong> {{ $orderInfo->BranchName ?? $orderInfo->BranchID ?? '--' }}
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
                        @forelse($lineInfo ?? [] as $i => $line)
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
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No line items</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-4 offset-md-8">
                    <div class="d-flex justify-content-between">
                        <span>Exclusive Total</span><strong>{{ number_format((float)($orderInfo->ExclusiveTotal ?? 0), 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tax Amount</span><strong>{{ number_format((float)($orderInfo->TaxAmount ?? 0), 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Inclusive Total</span><strong>{{ number_format((float)($orderInfo->InclusiveTotal ?? 0), 2) }}</strong>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">Order details not available.</div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    @media print {

        nav,
        .btn,
        .breadcrumb,
        .navbar,
        .footer {
            display: none !important;
        }

        .card {
            border: none;
        }
    }
</style>
@endpush
@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Purchase Order')
@section('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<style>
    .select2-container {
        width: 100% !important;
    }

    /* Print styles */
    @media print {
        body {
            background: #fff !important;
        }

        .container {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .card,
        .card-body,
        .table-responsive {
            box-shadow: none !important;
            border: none !important;
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .btn,
        .d-flex,
        .mb-3,
        .mb-4,
        [type="button"],
        [type="submit"],
        #printOrder {
            display: none !important;
        }

        table {
            font-size: 11px !important;
            width: 100% !important;
            table-layout: auto !important;
        }

        th,
        td {
            padding: 4px 6px !important;
            word-break: break-word !important;
        }

        label {
            font-size: 11px !important;
        }

        input,
        textarea,
        select {
            border: 1px solid #ccc !important;
            background: #fff !important;
            font-size: 11px !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            color: #000 !important;
        }

        .row,
        .col-md-4,
        .col-md-6,
        .offset-md-8 {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Hide navigation, footer, and other non-essential elements if present */
        nav,
        footer,
        .navbar,
        .sidebar,
        .alert,
        .pagination {
            display: none !important;
        }
    }
</style>
@endsection
@section('content')
{{-- <div class="mb-3"> --}}
{{-- <h1 class="h3 d-inline align-middle">@yield('title')</h1> --}}
{{-- </div> --}}

<div class="container">
    <h2 class="text-center my-4">@yield('title')</h2>

    <!-- Top Buttons -->
    <div class="d-flex justify-content-between mb-3">
        <div>
            <button type="button" class="btn btn-info" id="printOrder">Print</button>
        </div>
    </div>
    <form action="{{ route('purchaseOrder.store') }}" method="post" id="purchaseOrdersForm">
        @csrf
        <!-- Supplier & Details -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label>Supplier</label>
                <select class="form-control supplier" id="supplier" name="supplier" disabled>
                    <option selected>{{$orderInfo->SupplierName ?? 'N/A'}}</option>
                </select>
            </div>
            {{-- <div class="col-md-6">--}}
            {{-- <label>Address</label>--}}
            {{-- <input type="text" class="form-control" name="address" placeholder="Supplier address"/>--}}
            {{-- </div>--}}
        </div>

        <!-- LPO Details -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label>LPO Number</label>
                <input type="text" name="LPONo" class="form-control" value="{{$orderInfo->OrderNo ?? 'N/A'}}"
                    readonly />
            </div>
            <div class="col-md-4">
                <label>Date</label>
                <input type="date" class="form-control poDate" name="pODate"
                    value="{{ isset($orderInfo->OrderDate) ? Carbon::parse($orderInfo->OrderDate)->format('Y-m-d') : '' }}" readonly />
            </div>
            <div class="col-md-4">
                <label>Reference Number</label>
                <input type="text" class="form-control refNo" name="refNo" placeholder="RFQ Number"
                    value="{{$orderInfo->ExtOrdNum ?? 'N/A'}}" readonly />
            </div>
            <div class="col-md-4 mt-2">
                <label>Priority</label>
                <select class="form-control priority" name="priority" disabled>
                    <option selected>{{$orderInfo->Priority ?? 'N/A'}}</option>
                </select>
            </div>
            <div class="col-md-4 mt-2">
                <label>Payment Terms</label>
                <input type="text" name="terms" class="form-control terms" value="{{ $orderInfo->terms_description ?? 'N/A' }}" readonly />
            </div>
        </div>

        <!-- Hide Add Item button on show page -->


        <!-- Line Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th style="width: 1%; min-width: 10px;">#</th>
                        <th style="width: 10%; min-width: 100px;">Item Type</th>
                        <th style="width: 15%; min-width: 150px;">Item Name</th>
                        <th style="width: 20%; min-width: 200px;">Item Description</th>
                        <th style="width: 5%; min-width: 80px;">Quantity</th>
                        <th style="width: 10%; min-width: 100px;">Unit Price</th>
                        <th style="width: 7%; min-width: 80px;">Tax</th>
                        <th style="width: 5%; min-width: 80px;">Discount</th>
                        <th style="width: 15%; min-width: 150px;">Line Total</th>
                    </tr>
                </thead>
                <tbody id="po-items">

                    @foreach($lineInfo as $line)
                    <tr>
                        <td class="line-no">1.</td>
                        <td class="text-start">
                            <select class="form-select form-select-sm type" name="type[]" id="Type" disabled>
                                <option selected>{{$line ->ItemType}}</option>
                            </select>
                        </td>
                        <td class="text-start">
                            <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item" disabled>
                                <option selected>{{$line ->ItemName}}</option>
                            </select>
                        </td>
                        <td class="text-start">
                            <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]"
                                id="Description"
                                readonly
                                style="display: flex; align-items: center; justify-content: center; text-align: center; padding: 0; resize: none;">{{$line ->Description}}</textarea>
                        </td>
                        <td class="text-start"><input type="number"
                                class="form-control form-control-sm qty quantity"
                                name="quantity[]" id="Quantity" value="{{$line ->fQuantity}}" readonly>
                        </td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm unit-price "
                                name="unitPrice[]" id="Price"
                                value="{{$line ->fUnitPriceExcl}}" readonly></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm tax"
                                name="tax[]" id="Tax" value="{{$line ->fTaxRate}}" readonly></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm discount"
                                name="discount[]" id="Discount"
                                value="{{$line ->fLineDiscount}}" readonly></td>
                        <td class="text-start"><input type="number" class="form-control form-control-sm line-total"
                                name="lineTotal[]" id="lineTotal" step=""
                                value="{{$line ->LineTotal}}" readonly></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <!-- Optional Note -->
        <div class="mb-4">
            <label>Line Note</label>
            <textarea class="form-control" rows="3" placeholder="Optional message to supplier" readonly></textarea>
        </div>

        <!-- Totals -->
        <div class="row mb-4">
            <div class="col-md-4 offset-md-8">
                <div class="mb-2">
                    <label>Exclusive Total</label>
                    <input type="text" class="form-control" value="{{$orderInfo->OrdTotExcl ?? 'N/A'}}" readonly />
                </div>
                <div class="mb-2">
                    <label>Tax Amount</label>
                    <input type="text" class="form-control" value="{{$orderInfo->OrdTotTax ?? 'N/A'}}" readonly />
                </div>
                <div>
                    <label>Inclusive Total</label>
                    <input type="text" class="form-control" value="{{$orderInfo->OrdTotIncl ?? 'N/A'}}" readonly />
                </div>
            </div>
        </div>

        <!-- Workflow History -->
        @if(isset($history) && $history->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Approval History</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $record)
                        <tr>
                            <td>{{ $record->CreatedOn ? \Carbon\Carbon::parse($record->CreatedOn)->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $record->creator->Name ?? 'System' }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $record->status->Description ?? $record->StatusId }}
                                </span>
                            </td>
                            <td>{{ $record->Notes }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 no-print">
            @if(isset($canApprove) && $canApprove)
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="fas fa-check"></i> Approve
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="fas fa-times"></i> Reject
            </button>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#returnModal">
                <i class="fas fa-undo"></i> Return
            </button>
            @endif

            @if(isset($orderInfo) && (!isset($isFullyApproved) || !$isFullyApproved) && (!isset($history) || $history->count() == 0))
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitModal">
                <i class="fas fa-paper-plane"></i> Submit for Approval
            </button>
            @endif
        </div>
    </form>
</div>

<!-- Modals -->
<!-- Submit Modal -->
<div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('purchaseOrder.submit', $orderInfo->Id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit for Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('purchaseOrder.approve', $orderInfo->Id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this order?</p>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('purchaseOrder.reject', $orderInfo->Id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this order?</p>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('purchaseOrder.return', $orderInfo->Id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Return Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to return this order for modification?</p>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Return</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>
    // Activate print button
    document.addEventListener('DOMContentLoaded', function() {
        const printBtn = document.getElementById('printOrder');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                window.print();
            });
        }
    });
</script>
<script>
    function fetchSuppliers() {
        const supplierUrl = "{{ route('purchaseOrder.getSuppliers') }}"


        console.log(supplierUrl);

        $.ajax({
            url: supplierUrl,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('AJAX Response:', response);


                if (!response || !response.data || response.data.length === 0) {
                    console.warn('No suppliers found');
                    $('#supplier').html('<option selected disabled>No suppliers available</option>');
                    return;
                }

                let supplierSelect = $('#supplier');
                if (supplierSelect.children().length <= 1) {
                    supplierSelect.empty().append('<option selected disabled>Select supplier</option>');

                    $.each(response.data, function(key, item) {
                        supplierSelect.append(
                            `<option value="${item.id}">${item.name}</option>`
                        );
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error: ', status, error);
                console.error('Raw response:', xhr.responseText); // This is key!
                $('#supplier').html('<option selected disabled>Error loading suppliers</option>');
            }
        });
    }

    // $(document).on('change','#supplier',function () {
    //     fetchSuppliers();
    //
    //
    //     // alert('eric');
    // });

    $(function() {
        // Handle item type change using event delegation

        $('form#purchaseOrdersForm').submit(async function(e) {
            // alert($('#RequisitionID').val());
            e.preventDefault();
            if (await saveForm($(this), $('#saveOrder'), true, true, true)) {
                $Modal.modal('hide');
            }

        });


        $(document).on('change', '.type', function() {
            let row = $(this).closest('tr');
            let type = $(this).val();

            if (type !== '') {
                $.ajax({
                    url: `/requisitionItem/getItem/${type}`,
                    type: 'GET',
                    success: function(response) {

                        console.log(response)
                        let itemCodeSelect = row.find('.itemCode');
                        itemCodeSelect.empty().append(
                            '<option value="">Select Item</option>');

                        $.each(response.data, function(key, item) {
                            itemCodeSelect.append(
                                `<option value="${item.Id}">${item.ItemName}</option>`
                            );
                        });
                    },
                    error: function(response) {
                        alert('Failed to load items');
                        console.log(response);
                    }
                });
            } else {
                row.find('.itemCode').empty().append('<option value="">Select Item</option>');
            }
        });

        ////fetching suppliers


        // Handle item code change using event delegation
        $(document).on('change', '.itemCode', function() {
            let row = $(this).closest('tr');
            let itemId = $(this).val();

            if (itemId !== '') {
                $.ajax({
                    url: `/requisitionItem/getItemDetails/${itemId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(key, item) {
                                row.find('.itemDescription').val(item.Description ||
                                    '');
                                row.find('.unit-price').val(item.UnitPrice || '');
                            });
                        }
                    },
                    error: function(response) {
                        alert('Failed to load item details');
                        console.log(response);
                    }
                });
            } else {
                row.find('.itemDescription').val('');
                row.find('.unit-price').val('');
            }
        });

        // Handle quantity or price change and calculate line total
        $(document).on('change', '.quantity, .unit-price, .tax, .discount', function() {
            let row = $(this).closest('tr');
            let qty = parseFloat(row.find('.quantity').val()) || 0;
            let price = parseFloat(row.find('.unit-price').val()) || 0;
            let tax = parseFloat(row.find('.tax').val()) || 0;
            let discount = parseFloat(row.find('.discount').val()) || 0;

            let total = qty * price;

            if (tax > 0) {
                total += total * (tax / 100);
            }
            if (discount > 0) {
                total -= total * (discount / 100);
            }

            row.find('.line-total').val(total.toFixed(2));
        });
    });
</script>

<script>
    let rowCount = 1;

    document.getElementById('add-row').addEventListener('click', function() {
        rowCount++;
        const row = `
        <tr>
            <td class="line-no">${rowCount}.</td>
            <td class="text-start">
                <select class="form-select form-select-sm type" name="type[]" id="Type">
                    <option disabled selected>Select Type</option>
                    <option value="Stock">Stock</option>
                    <option value="Asset">Asset</option>
                    <option value="Non-Stock">Non-Stock</option>
                </select>
            </td>
            <td class="text-start">
                <select class="form-select form-select-sm itemCode" name="itemCode[]" id="Item">
                    <option disabled selected>Select Item Code</option>
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm itemDescription" name="item_description[]" id="Description" readonly></td>
            <td><input type="number" class="form-control form-control-sm qty quantity" name="quantity[]" id="Quantity" step="any" readonly ></td>
            <td><input type="number" class="form-control form-control-sm unit-price" name="unit_price[]" id="Price" step="any" readonly ></td>
            <td><input type="number" class="form-control form-control-sm tax" name="tax[]" id="Tax" step="any" readonly ></td>
            <td><input type="number" class="form-control form-control-sm discount" name="discount[]" id="Discount" step="any" readonly ></td>
            <td><input type="number" class="form-control form-control-sm line-total" name="line_total[]"  id="lineTotal" readonly></td>
        </tr>`;
        document.getElementById('po-items').insertAdjacentHTML('beforeend', row);
    });
</script>

@endsection