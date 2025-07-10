@extends('layouts.app')
@section('title', 'Edit Budget Entry')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card p-4">
        <h5>Edit Projections Overview</h5>
        <p class="text-muted">Update the details of your branch projections below.</p>

        <form action="{{ route('budgetprojections.update', $budget->Id ?? '') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="scenario" class="form-label">Budget</label>
                    <input class="form-control" value="{{ $budget->budget->Name ?? '' }}" readonly>
                </div>
                <div class="col-md-6">
                    <label for="currency" class="form-label">Currency</label>
                    <select name="CurrencyID" id="currency" class="form-select" required>
                        @foreach($currencies as $currency)
                            <option
                                value="{{ $currency->Id }}" {{ (isset($budget->CurrencyID) && $budget->CurrencyID == $currency->Id) ? 'selected' : '' }}>{{ $currency->Code }}</option>
                        @endforeach
                    </select>
            </div>
            </div>
            <div class="mt-4">
                <h5>Detailed Projections</h5>
                <p class="text-muted">Breakdown of projections by product. You can edit the values below.</p>
                <div class="mt-2 mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addProductModal">➕ Add Product Row
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center" id="productsTable">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Volume</th>
                            <th>Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($budget->projections as $i => $projection)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $projection->productType->Name }}<input type="hidden"
                                                                           name="Products[{{ $i }}][ProductID]"
                                                                           value="{{ (int)$projection->ProductID }}">
                            </td>
                            <td><input type="number" name="Products[{{ $i }}][Volume]" class="form-control"
                                       value="{{ $projection->Volume }}" min="0"></td>
                            <td><input type="number" step="0.01" name="Products[{{ $i }}][Value]" class="form-control"
                                       value="{{ $projection->Value }}" min="0"></td>
                            <td>
                                <button type="button" class="btn btn-outline-danger btn-sm delete-product">🗑</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            </div>
            <div class="card p-4 mt-4">
                <h5>📊 Monthly Budget Allocations</h5>
                <p class="text-muted">Edit the budget allocations for each month below.</p>
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Month</th>
                        <th>Allocation Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $allocMap = isset($monthlyAllocations) ? $monthlyAllocations->keyBy('Month') : collect();
                    @endphp
                    @foreach ($months as $i => $month)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>
                                <input type="number" step="0.01" min="0" name="MonthlyAllocations[{{ $i+1 }}]"
                                       class="form-control"
                                       value="{{ isset($allocMap[$i+1]) ? ($allocMap[$i+1]->Allocation ?? $allocMap[$i+1]->Amount) : 0 }}">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-4">
                <button type="submit" class="btn btn-success">
                    💾 Save Changes
                </button>
            </div>
        </form>

        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="newProduct" class="form-label">Product</label>
                            <select id="newProduct" class="form-select">
                                @foreach($productTypes as $productType)
                                    <option value="{{ $productType->Id }}">{{ $productType->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="newVolume" class="form-label">Volume</label>
                            <input type="number" id="newVolume" class="form-control" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="newValue" class="form-label">Value</label>
                            <input type="number" id="newValue" class="form-control" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="addProductBtn">Add Product</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- NOTE: In your controller, pass $productTypes = BudgetProductType::all() to the view and use that for the modal select above. --}}

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const addProductBtn = document.getElementById('addProductBtn');
                addProductBtn.addEventListener('click', function () {
                    const productSelect = document.getElementById('newProduct');
                    const volumeInput = document.getElementById('newVolume');
                    const valueInput = document.getElementById('newValue');
                    const tableBody = document.querySelector('#productsTable tbody');
                    const rowCount = tableBody.querySelectorAll('tr').length;
                    const productId = productSelect.value;
                    const productName = productSelect.options[productSelect.selectedIndex].text;
                    const volume = volumeInput.value;
                    const value = valueInput.value;
                    if (!productId || !volume || !value) {
                        alert('Please fill all fields.');
                        return;
                    }
                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                <td>${rowCount + 1}</td>
                <td>${productName}<input type="hidden" name="Products[${rowCount}][ProductID]" value="${productId}"></td>
                <td><input type="number" name="Products[${rowCount}][Volume]" class="form-control" value="${volume}" min="0"></td>
                <td><input type="number" step="0.01" name="Products[${rowCount}][Value]" class="form-control" value="${value}" min="0"></td>
                <td><button type="button" class="btn btn-outline-danger btn-sm delete-product">🗑</button></td>
            `;
                    tableBody.appendChild(newRow);
                    // Reset modal fields
                    productSelect.selectedIndex = 0;
                    volumeInput.value = '';
                    valueInput.value = '';
                    // Hide modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
                    modal.hide();
                });

                // Delete product row
                document.getElementById('productsTable').addEventListener('click', function (e) {
                    if (e.target && e.target.classList.contains('delete-product')) {
                        const row = e.target.closest('tr');
                        row.remove();
                        // Re-number rows
                        const rows = this.querySelectorAll('tbody tr');
                        rows.forEach((tr, idx) => {
                            tr.querySelector('td').textContent = idx + 1;
                        });
                    }
                });
            });
        </script>
    </div>
@endsection
