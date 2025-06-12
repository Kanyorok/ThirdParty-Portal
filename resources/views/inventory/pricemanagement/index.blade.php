@extends('layouts.app')
@section('title', 'Item Price Management')
@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="container mt-4">
        <h4 class="mb-3">Item Pricing</h4>

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" id="priceTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#addPrice" type="button"
                        role="tab">Add Price
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#viewPrices" type="button" role="tab">View
                    Price List
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#uploadPrice" type="button" role="tab">📁
                    Upload Price List
                </button>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content border p-3">

            <!-- Add Price Tab -->
            <div class="tab-pane fade show active" id="addPrice" role="tabpanel">
                <form method="POST" action="{{ route('pricemanagement.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <select class="form-select" name="ItemID" required>
                            <option selected disabled>Select Item</option>
                            @foreach($items ?? [] as $item)
                                <option value="{{ $item->Id }}">{{ $item->ItemName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">UOM</label>
                        <select class="form-select" name="UOM" required>
                            <option selected disabled>Select UOM</option>
                            @foreach($uoms ?? [] as $uom)
                                <option value="{{ $uom->Id }}">{{ $uom->Code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estimated Price</label>
                        <input type="number" step="0.01" class="form-control" name="EstimatedPrice" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Actual Price</label>
                        <input type="number" step="0.01" class="form-control" name="ActualPrice" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <input type="text" class="form-control" name="CurrencyCode" value="KES" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Effective From</label>
                            <input type="date" class="form-control" name="EffectiveFrom" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Effective To</label>
                            <input type="date" class="form-control" name="EffectiveTo">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="IsDefault" id="isDefault" value="1">
                        <label class="form-check-label" for="isDefault">Mark as Default Price</label>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Source</label>
                        <input type="text" class="form-control" name="Source" placeholder="Optional">
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save Price</button>
                </form>
            </div>

            <!-- View Prices Tab -->
            <div class="tab-pane fade" id="viewPrices" role="tabpanel">
                <table id="pricingTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Price ID</th>
                        <th>Item</th>
                        <th>UOM</th>
                        <th>Estimated Price</th>
                        <th>Actual Price</th>
                        <th>Currency</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                        <th>Default</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($prices ?? [] as $index => $price)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $price->PriceID }}</td>
                            <td>{{ $price->item->ItemName ?? $price->item->ItemCode ?? '-' }}</td>
                            <td>{{ $price->uom->Code ?? '-' }}</td>
                            <td>{{ number_format($price->EstimatedPrice, 2) }}</td>
                            <td>{{ number_format($price->ActualPrice, 2) }}</td>
                            <td>{{ $price->CurrencyCode }}</td>
                            <td>{{ $price->EffectiveFrom ? \Carbon\Carbon::parse($price->EffectiveFrom)->format('Y-m-d') : '—' }}</td>
                            <td>{{ $price->EffectiveTo ? \Carbon\Carbon::parse($price->EffectiveTo)->format('Y-m-d') : '—' }}</td>
                            <td>{!! $price->IsDefault ? '✔️' : '' !!}</td>
                            <td>
                                <a href="{{ route('pricemanagement.edit', $price->Id) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('pricemanagement.destroy', $price->Id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this price?')">Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">No prices found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Upload Price List Tab -->
            <div class="tab-pane fade" id="uploadPrice" role="tabpanel">
                <form action="{{ route('pricemanagement.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Upload Excel or CSV File</label>
                        <input class="form-control" type="file" name="priceFile" accept=".csv,.xlsx,.xls" required>
                    </div>
                    <div class="alert alert-info small">
                        Ensure your file has headers: <code>ItemCode, UOMCode, Price, EffectiveFrom, EffectiveTo,
                            Currency, IsDefault</code>
                    </div>
                    <button type="submit" class="btn btn-success">Upload</button>
                </form>
            </div>

        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#pricingTable').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
