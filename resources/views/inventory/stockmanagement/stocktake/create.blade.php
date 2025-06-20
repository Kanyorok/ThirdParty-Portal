@extends('layouts.app')
@section('title', 'Physical Stock Take')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📝 Physical Stock Take</h4>

    <form action="{{ route('stocktake.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Header Info -->
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">📍 Branch</label>
                <select name="BranchId" id="branch-select" class="form-select" required>
                    <option value="">-- Select Branch --</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">🏢 Store</label>
                <select name="StoreId" id="store-select" class="form-select" required>
                    <option value="">-- Select Store --</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">🧑‍💼 Counted By</label>
                <select name="CountedBy" class="form-select select2" required>
                    <option value="">-- Select User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->Id }}">{{ $user->Name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">📅 Count Date</label>
                <input type="date" name="CountDate" class="form-control" value="{{ now()->toDateString() }}">
            </div>
        </div>

        <!-- Items Grid -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>System Qty</th>
                    <th>Counted Qty</th>
                    <th>Variance</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody id="stockTakeBody">
                <!-- Filled dynamically -->
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <button class="btn btn-success mt-3">✅ Submit Stock Count</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const branchSelect = document.getElementById('branch-select');
        const storeSelect = document.getElementById('store-select');
        const stockTableBody = document.getElementById('stockTakeBody');

        branchSelect.addEventListener('change', function () {
            loadStores(this.value);
        });

        storeSelect.addEventListener('change', function () {
            const branchId = branchSelect.value;
            const storeId = this.value;

            if (branchId && storeId) {
                fetch(`/inventory/stock-items/${branchId}/${storeId}`)
                    .then(res => res.json())
                    .then(stocks => {
                        let html = '';
                        stocks.forEach((stock, index) => {
                            html += `
                          <tr>
                            <td>${index + 1}</td>
                            <td>${stock.item?.ItemCode ?? 'N/A'}</td>
                            <td>${stock.item?.ItemName ?? 'N/A'}</td>
                            <td class="system-qty">${stock.CurrentQty}</td>

                            <input type="hidden" name="lines[${index}][ItemId]" value="${stock.Id}">
                            <input type="hidden" name="lines[${index}][ActualQuantity]" value="${stock.CurrentQty}">

                            <td>
                              <input type="number" name="lines[${index}][CountedQuantity]"
                                     class="form-control counted-qty" value="${stock.CurrentQty}" required>
                            </td>
                            <td><span class="variance fw-bold text-danger">0</span></td>
                            <td>
                              <input type="text" name="lines[${index}][Remarks]" class="form-control" placeholder="Optional">
                            </td>
                          </tr>`;
                        });
                        stockTableBody.innerHTML = html;
                        attachVarianceListeners();
                    })
                    .catch(err => console.error('Error loading stock items:', err));
            }
        });

        function loadStores(branchId) {
            storeSelect.innerHTML = '<option value="">-- Loading stores --</option>';
            if (branchId) {
                fetch(`/inventory/stocktake/branches/${branchId}`)
                    .then(response => response.json())
                    .then(stores => {
                        storeSelect.innerHTML = '<option value="">-- Select Store --</option>';
                        stores.forEach(store => {
                            const option = document.createElement('option');
                            option.value = store.Id;
                            option.textContent = store.StoreName;
                            storeSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading stores:', error));
            }
        }

        function attachVarianceListeners() {
            document.querySelectorAll('.counted-qty').forEach((input, idx) => {
                input.addEventListener('input', function () {
                    const row = input.closest('tr');
                    const systemQty = parseFloat(row.querySelector('.system-qty').textContent) || 0;
                    const countedQty = parseFloat(input.value) || 0;
                    const variance = countedQty - systemQty;
                    row.querySelector('.variance').textContent = variance;
                });
            });
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.select2').select2({
            placeholder: 'Select user',
            allowClear: true
    });
  });
</script>

@endsection
