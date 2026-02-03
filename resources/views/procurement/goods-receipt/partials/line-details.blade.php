<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6 class="text-primary">Item Information</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <th style="width: 140px;">Item Code:</th>
                    <td>{{ $line->item->ItemCode ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Item Name:</th>
                    <td>{{ $line->item->ItemName ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td>{{ $line->item->ItemDescription ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Type:</th>
                    <td>
                        <span class="badge bg-{{ $line->ItemType == 'stock' ? 'success' : ($line->ItemType == 'asset' ? 'warning' : 'info') }}">
                            {{ $line->item_type_display }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>UOM:</th>
                    <td>{{ $line->item->uom->Name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-primary">Transaction Details</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <th style="width: 140px;">Received Date:</th>
                    <td>{{ $line->ReceivedDate ? $line->ReceivedDate->format('d M Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Received By:</th>
                    <td>{{ $line->receiver->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Store:</th>
                    <td>{{ $line->StoreID ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Delivery Note:</th>
                    <td>{{ $line->DeliveryNoteRef ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <h6 class="text-primary">Financials</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <th style="width: 140px;">Ordered Qty:</th>
                    <td>{{ number_format($line->POQTY, 2) }}</td>
                </tr>
                <tr>
                    <th>Received Qty:</th>
                    <td>{{ number_format($line->ReceivedQTY, 2) }}</td>
                </tr>
                <tr>
                    <th>Unit Price:</th>
                    <td>KES {{ number_format($line->UnitPrice, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Value:</th>
                    <td class="fw-bold">KES {{ number_format($totalValue, 2) }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-primary">Inventory & Tracking</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <th style="width: 140px;">Batch ID:</th>
                    <td>{{ $line->BatchNumber ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Expiry Date:</th>
                    <td>{{ $line->ExpiryDate ? $line->ExpiryDate->format('d M Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Manufacture Date:</th>
                    <td>{{ $line->ManufactureDate ? $line->ManufactureDate->format('d M Y') : 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($line->ProcessingErrors || $line->QualityRemarks)
    <div class="row">
        <div class="col-12">
            <h6 class="text-danger">Issues & Regards</h6>
            @if($line->ProcessingErrors)
                <div class="alert alert-danger py-2">
                    <strong>Processing Error:</strong> {{ $line->ProcessingErrors }}
                </div>
            @endif
            
            @if($line->QualityRemarks)
                <div class="alert alert-warning py-2">
                    <strong>Quality Remarks:</strong> {{ $line->QualityRemarks }}
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
