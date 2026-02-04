@extends('layouts.app')
@section('title', 'Physical Stock Take')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
@section('content')
    @if ($errors->any() && !$errors->has('remarks_error'))
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if ($errors->has('remarks_error'))
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->get('remarks_error') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if (session('no_items'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('no_items') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="container mt-4">
        <form action="{{ route('stocktake.store') }}" method="POST" enctype="multipart/form-data" id="stockTakeForm">
            @csrf

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">📍 Branch<span class="text-danger">*</span></label>
                    <select name="BranchId" id="branch-select" class="form-select @error('BranchId') is-invalid @enderror" required>
                        <option value="">-- Select Branch --</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->Id }}" 
                                {{ old('BranchId', request('BranchId')) == $branch->Id ? 'selected' : '' }}>
                                {{ $branch->Name ?? '-'}}
                            </option>
                        @endforeach
                    </select>
                    @error('BranchId')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">🏢 Store<span class="text-danger">*</span></label>
                    <select name="StoreId" id="store-select" class="form-select @error('StoreId') is-invalid @enderror" required>
                        <option value="">-- Select Store --</option>
                        @foreach ($stores ?? [] as $store)
                            <option value="{{ $store->Id }}" 
                                {{ old('StoreId', request('StoreId')) == $store->Id ? 'selected' : '' }}>
                                {{ $store->StoreName ?? '-'}}
                            </option>
                        @endforeach
                    </select>
                    @error('StoreId')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">🧑‍💼 Counted By<span class="text-danger">*</span></label>
                    <select name="CountedBy" class="form-select select2 @error('CountedBy') is-invalid @enderror" required>
                        <option value="">-- Select User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->Id }}" 
                                {{ old('CountedBy') == $user->Id ? 'selected' : '' }}>
                                {{ $user->Name ?? '-'}}
                            </option>
                        @endforeach
                    </select>
                    @error('CountedBy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">📅 Count Date<span class="text-danger">*</span></label>
                    <input type="date" name="CountDate" class="form-control @error('CountDate') is-invalid @enderror" 
                           value="{{ old('CountDate', now()->toDateString()) }}" required>
                    @error('CountDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item Code<span class="text-danger">*</span></th>
                        <th>Item Name<span class="text-danger">*</span></th>
                        <th>System Qty</th>
                        <th>Counted Qty<span class="text-danger">*</span></th>
                        <th>Variance</th>
                        <th>Remarks <span class="text-danger">*</span></th>
                    </tr>
                    </thead>
                    <tbody id="stockTakeBody">
                    @php
                        $oldLines = old('lines', []);
                    @endphp
                    
                    @if(!empty($oldLines) && count($oldLines) > 0)
                        @foreach($oldLines as $index => $line)
                            @php
                                $item = \App\Models\Inventory\StockItem::with('item')->find($line['ItemId'] ?? null);
                                $variance = ($line['CountedQuantity'] ?? 0) - ($line['ActualQuantity'] ?? 0);
                            @endphp
                            <tr data-item-id="{{ $line['ItemId'] ?? '' }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->item->ItemCode ?? 'N/A' }}</td>
                                <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                                <td class="system-qty">{{ $line['ActualQuantity'] ?? '0' }}</td>

                                <input type="hidden" name="lines[{{ $index }}][ItemId]" value="{{ $line['ItemId'] ?? '' }}">
                                <input type="hidden" name="lines[{{ $index }}][ActualQuantity]" value="{{ $line['ActualQuantity'] ?? '0' }}">

                                <td>
                                    <input type="number" name="lines[{{ $index }}][CountedQuantity]"
                                           class="form-control counted-qty @error('lines.' . $index . '.CountedQuantity') is-invalid @enderror" 
                                           value="{{ old('lines.' . $index . '.CountedQuantity', $line['CountedQuantity'] ?? '0') }}" 
                                           required min="0" step="0.01">
                                    @error('lines.' . $index . '.CountedQuantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>
                                    <span class="variance fw-bold 
                                        {{ $variance > 0 ? 'text-success' : ($variance < 0 ? 'text-danger' : 'text-secondary') }}">
                                        {{ $variance > 0 ? '+' . $variance : $variance }}
                                    </span>
                                </td>
                                <td>
                                    <input type="text" name="lines[{{ $index }}][Remarks]" 
                                           class="form-control remarks-input @error('lines.' . $index . '.Remarks') is-invalid @enderror" 
                                           value="{{ old('lines.' . $index . '.Remarks', $line['Remarks'] ?? '') }}" 
                                           placeholder="Enter remarks (required)" 
                                           required
                                           data-item-name="{{ $item->item->ItemName ?? 'Item' }}">
                                    @error('lines.' . $index . '.Remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>

            <div id="noItemsMessage" class="alert alert-warning text-center d-none">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <span id="noItemsText"></span>
            </div>

            <div id="formErrors" class="alert alert-danger d-none">
                <ul id="errorList" class="mb-0"></ul>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-success mt-3" id="submitBtn">Submit Stock Count</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const branchSelect = document.getElementById('branch-select');
        const storeSelect = document.getElementById('store-select');
        const stockTableBody = document.getElementById('stockTakeBody');
        const stockTakeForm = document.getElementById('stockTakeForm');
        const formErrors = document.getElementById('formErrors');
        const errorList = document.getElementById('errorList');
        const submitBtn = document.getElementById('submitBtn');
        const noItemsMessage = document.getElementById('noItemsMessage');
        const noItemsText = document.getElementById('noItemsText');

        const oldBranchId = "{{ old('BranchId') }}";
        const oldStoreId = "{{ old('StoreId') }}";
        const oldCountedBy = "{{ old('CountedBy') }}";

        $('.select2').select2({
            placeholder: 'Select user',
            allowClear: true
        });

        if (oldBranchId) {
            branchSelect.value = oldBranchId;
            loadStores(oldBranchId).then(() => {
                if (oldStoreId) {
                    storeSelect.value = oldStoreId;
                    const oldLines = @json(old('lines', []));
                    if (oldLines.length === 0) {
                        loadStockItems(oldBranchId, oldStoreId);
                    } else {
                        hideNoItemsMessage();
                    }
                }
            });
        }

        if (oldCountedBy) {
            $('.select2').val(oldCountedBy).trigger('change');
        }

        document.querySelectorAll('.counted-qty').forEach(input => {
            calculateVariance(input);
        });

        branchSelect.addEventListener('change', function () {
            stockTableBody.innerHTML = '';
            hideNoItemsMessage();
            loadStores(this.value);
        });

        storeSelect.addEventListener('change', function () {
            const branchId = branchSelect.value;
            const storeId = this.value;

            stockTableBody.innerHTML = '';
            hideNoItemsMessage();

            if (branchId && storeId) {
                loadStockItems(branchId, storeId);
            }
        });

        function loadStores(branchId) {
            return new Promise((resolve, reject) => {
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
                                if (store.Id == oldStoreId) {
                                    option.selected = true;
                                }
                                storeSelect.appendChild(option);
                            });
                            resolve();
                        })
                        .catch(error => {
                            console.error('Error loading stores:', error);
                            showError('Failed to load stores. Please try again.');
                            reject(error);
                        });
                } else {
                    resolve();
                }
            });
        }

        function loadStockItems(branchId, storeId) {
            showLoading();
            
            fetch(`/inventory/stock-items/${branchId}/${storeId}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(stocks => {
                    hideLoading();
                    
                    if (stocks.length === 0) {
                        showNoItemsMessage('No stock is maintained for the selected Branch and Store. Please select a Branch–Store combination that has stock take records.');
                        return;
                    }
                    
                    let html = '';
                    const oldLines = @json(old('lines', []));
                    
                    stocks.forEach((stock, index) => {
                        const oldLine = oldLines.find(line => line.ItemId == stock.Id);
                        const countedQty = oldLine ? oldLine.CountedQuantity : stock.CurrentQty;
                        const remarks = oldLine ? oldLine.Remarks : '';
                        const systemQty = stock.CurrentQty || 0;
                        const variance = countedQty - systemQty;
                        
                        html += `
                        <tr data-item-id="${stock.Id}">
                            <td>${index + 1}</td>
                            <td>${stock.item?.ItemCode ?? 'N/A'}</td>
                            <td>${stock.item?.ItemName ?? 'N/A'}</td>
                            <td class="system-qty">${systemQty}</td>

                            <input type="hidden" name="lines[${index}][ItemId]" value="${stock.Id}">
                            <input type="hidden" name="lines[${index}][ActualQuantity]" value="${systemQty}">

                            <td>
                                <input type="number" name="lines[${index}][CountedQuantity]"
                                       class="form-control counted-qty" 
                                       value="${countedQty}" 
                                       required 
                                       min="0" step="0.01">
                            </td>
                            <td>
                                <span class="variance fw-bold 
                                    ${variance > 0 ? 'text-success' : (variance < 0 ? 'text-danger' : 'text-secondary')}">
                                    ${variance > 0 ? '+' + variance : variance}
                                </span>
                            </td>
                            <td>
                                <input type="text" name="lines[${index}][Remarks]" 
                                       class="form-control remarks-input" 
                                       value="${remarks}" 
                                       placeholder="Enter remarks (required)" 
                                       required
                                       data-item-name="${stock.item?.ItemName ?? 'Item'}">
                            </td>
                        </tr>`;
                    });
                    stockTableBody.innerHTML = html;
                    attachVarianceListeners();
                    attachRemarksValidation();
                    
                    hideNoItemsMessage();
                })
                .catch(err => {
                    hideLoading();
                    console.error('Error loading stock items:', err);
                    showError('Failed to load stock items. Please try again.');
                });
        }

        function showLoading() {
            const loadingRow = `
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        Loading stock items...
                    </td>
                </tr>
            `;
            stockTableBody.innerHTML = loadingRow;
        }

        function hideLoading() {
        }

        function showNoItemsMessage(message) {
            stockTableBody.innerHTML = '';
            
            noItemsText.textContent = message;
            noItemsMessage.classList.remove('d-none');
            
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
        }

        function hideNoItemsMessage() {
            noItemsMessage.classList.add('d-none');
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
        }

        function attachVarianceListeners() {
            document.querySelectorAll('.counted-qty').forEach(input => {
                input.addEventListener('input', function () {
                    calculateVariance(this);
                });
            });
        }

        function calculateVariance(input) {
            const row = input.closest('tr');
            const systemQty = parseFloat(row.querySelector('.system-qty').textContent) || 0;
            const countedQty = parseFloat(input.value) || 0;
            const variance = countedQty - systemQty;
            const varianceSpan = row.querySelector('.variance');
            
            varianceSpan.textContent = variance > 0 ? '+' + variance : variance;
            
            if (variance > 0) {
                varianceSpan.className = 'variance fw-bold text-success';
            } else if (variance < 0) {
                varianceSpan.className = 'variance fw-bold text-danger';
            } else {
                varianceSpan.className = 'variance fw-bold text-secondary';
            }
        }

        function attachRemarksValidation() {
            document.querySelectorAll('.remarks-input').forEach(input => {
                input.addEventListener('blur', function() {
                    validateRemarksField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                });
            });
        }

        function validateRemarksField(input) {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                return false;
            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                return true;
            }
        }

        stockTakeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            clearErrors();
            
            let isValid = true;
            const errorMessages = [];
            
            if (!branchSelect.value) {
                errorMessages.push('Branch is required');
                branchSelect.classList.add('is-invalid');
                isValid = false;
            } else {
                branchSelect.classList.remove('is-invalid');
            }
            
            if (!storeSelect.value) {
                errorMessages.push('Store is required');
                storeSelect.classList.add('is-invalid');
                isValid = false;
            } else {
                storeSelect.classList.remove('is-invalid');
            }
            
            const remarksInputs = document.querySelectorAll('.remarks-input');
            if (remarksInputs.length === 0) {
                if (noItemsMessage.classList.contains('d-none')) {
                    errorMessages.push('Please select a store to load items');
                    isValid = false;
                } else {
                    errorMessages.push('No items available for the selected Branch-Store combination');
                    isValid = false;
                }
            } else {
                remarksInputs.forEach((input, index) => {
                    if (!validateRemarksField(input)) {
                        const itemName = input.getAttribute('data-item-name') || `Item ${index + 1}`;
                        errorMessages.push(`Remarks are required for ${itemName}`);
                        isValid = false;
                    }
                });
                
                document.querySelectorAll('.counted-qty').forEach(input => {
                    if (input.value === '' || isNaN(input.value)) {
                        input.classList.add('is-invalid');
                        errorMessages.push('Counted quantity must be a valid number');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
            }
            
            if (!isValid) {
                showErrors(errorMessages);
                formErrors.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                this.submit();
            }
        });

        function showErrors(messages) {
            formErrors.classList.remove('d-none');
            errorList.innerHTML = '';
            messages.forEach(message => {
                const li = document.createElement('li');
                li.textContent = message;
                errorList.appendChild(li);
            });
        }

        function clearErrors() {
            formErrors.classList.add('d-none');
            errorList.innerHTML = '';
        }

        function showError(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.container').prepend(alertDiv);
        }
    });
</script>

<style>
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
    
    .is-valid {
        border-color: #198754 !important;
    }
    
    .form-control:focus.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
    }
    
    .invalid-feedback {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
    
    .disabled {
        opacity: 0.65;
        pointer-events: none;
    }
</style>
@endsection