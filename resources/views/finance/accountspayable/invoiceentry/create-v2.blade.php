@extends('layouts.app')
@section('title','New Invoice Entry')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-dropdown {
        z-index: 10000 !important;
    }

    /* Custom styling for PO select */
    #poSelect {
        padding: 8px 12px !important;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        font-size: 14px;
    }

    #poSelect:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Select2 styling to match */
    .select2-container--default .select2-selection--single {
        padding: 4px 12px !important;
        height: auto !important;
        border-radius: 6px !important;
        border: 1px solid #dee2e6 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;
        line-height: 1.5 !important;
    }
</style>
@endsection

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

    <!-- Currency Configuration (hidden) -->
    <script>
        window.currencyConfig = {
            symbol: '{{ $defaultCurrency->Symbol ?? "KSh" }}',
            code: '{{ $defaultCurrency->Code ?? "KES" }}',
            id: {{ $defaultCurrency->Id ?? 56 }}
        };
    </script>

    <div class="container my-3">
        <div id="loadingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.95); z-index: 9999; backdrop-filter: blur(2px);">
            <div class="text-center">
                <div class="spinner-border text-info" role="status" style="width: 4rem; height: 4rem;"></div>
                <div class="mt-2 text-muted fw-bold" id="loadingMessage">Searching for supplier...</div>
            </div>
        </div>

        <form action="{{ route('invoiceentry.store') }}" method="post" enctype="multipart/form-data" id="invoiceForm">
            @csrf

            <!-- Alerts / Toasts -->
            <div id="flashArea"></div>
            <div id="toastArea" class="position-fixed top-0 end-0 p-3" style="z-index:1080;"></div>

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-file-invoice text-info me-2"></i> Create New Invoice Entry
                    </h6>
                    <a href="{{ route('invoiceentry.index') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-arrow-left me-1"></i> Back to Invoices
                    </a>
                </div>

                <div class="card-body p-3">

                    <!-- STEP 1: Search Supplier -->
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-7">
                                <label class="form-label small text-muted">Search Supplier (Reg No./Email/Phone/Name)</label>
                                <select id="supplierSelect" class="form-control" style="width: 100%;">
                                    <option value="">-- Search and select a supplier --</option>
                                </select>
                                <div class="form-text" id="supplierSelectHint">Type at least 2 characters to search</div>
                            </div>
                            <div class="col-md-5 text-md-end">
                                <span class="small text-muted">Use the dropdown to select a supplier</span>
                            </div>
                        </div>

                        <!-- Supplier summary (hidden until found) -->
                        <div id="supplierCard" class="row g-3 mt-3 d-none">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="text-uppercase text-muted small">Supplier</div>
                                                <div class="h6 mb-0" id="suppName">—</div>
                                                <div class="small text-muted" id="suppId">—</div>
                                            </div>
                                            <span class="badge bg-success" id="suppStatus">Active</span>
                                        </div>
                                        <div class="row small mt-2">
                                            <div class="col-md-4">Email: <span class="text-dark" id="suppEmail">—</span></div>
                                            <div class="col-md-4">Phone: <span class="text-dark" id="suppPhone">—</span></div>
                                            <div class="col-md-4">Reg No: <span class="text-dark" id="suppRegNo">—</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="alert alert-info mb-0">
                                    <div class="fw-semibold">Next</div>
                                    <div class="small">Select a Purchase Order and corresponding GRN to create the invoice.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Purchase Orders -->
                    <div id="ordersBlock" class="border rounded-3 p-3 mb-3 d-none">
                        <h6 class="mb-3 text-muted">
                            <i class="fas fa-shopping-cart text-info me-2"></i> Select Purchase Order
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Search and Select PO <span class="text-danger">*</span></label>
                                <select id="poSelect" class="form-control" style="width: 100%; padding: 8px 12px;">
                                    <option value="">-- Search and select a Purchase Order --</option>
                                </select>
                                <div class="form-text" id="poSelectHint">Start typing to search by PO number, description, or amount</div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 bg-light h-100">
                                    <div class="text-muted small text-uppercase mb-1">Quick Stats</div>
                                    <div class="small" id="poStats">
                                        <div>Total POs: <span id="totalPOs">0</span></div>
                                        <div>Date Range: <span id="dateRange">-</span></div>
                                        <div>Total Value: <span id="totalValue">{{ $defaultCurrency->Symbol ?? 'KSh' }} 0.00</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected PO Summary -->
                        <div id="selectedPOSummary" class="mt-3 d-none">
                            <div class="alert alert-success">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">Selected PO: <span id="selectedPONumber"></span></h6>
                                        <small class="text-muted">
                                            <span id="selectedPODate"></span> •
                                            Amount: <strong id="selectedPOAmount"></strong> •
                                            <span id="selectedPODescription"></span>
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-2" id="viewSelectedPO">
                                            <i class="fas fa-eye me-1"></i> View PO
                                        </button>
{{--                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="changePO">--}}
{{--                                            <i class="fas fa-edit me-1"></i> Change--}}
{{--                                        </button>--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Goods Receipts -->
                    <div id="grnsBlock" class="border rounded-3 p-3 mb-3 d-none">
                        <h6 class="mb-3 text-muted">
                            <i class="fas fa-truck text-info me-2"></i> Goods Receipts for Selected PO
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle" id="grnsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:40px">Select</th>
                                        <th>GRN ID</th>
                                        <th>Received Date</th>
                                        <th class="text-end">Ordered Qty</th>
                                        <th class="text-end">Received Qty</th>
                                        <th class="text-center" style="width:80px">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="grnRows"></tbody>
                            </table>
                        </div>
                        <div class="small text-muted" id="grnCountHint">0 goods receipts</div>
                    </div>

                    <!-- 3-Way Matching Status -->
                    <div id="matchingStatusBlock" class="border rounded-3 p-3 mb-3 d-none">
                        <h6 class="mb-3 text-muted">
                            <i class="fas fa-check-double text-info me-2"></i> 3-Way Matching Status
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body p-3 text-center">
                                        <i class="fas fa-shopping-cart text-success mb-2" style="font-size: 1.5rem;"></i>
                                        <div class="small text-success fw-bold">Purchase Order</div>
                                        <div class="text-success">✓ Selected</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card" id="grnMatchingCard">
                                    <div class="card-body p-3 text-center">
                                        <i class="fas fa-truck mb-2" style="font-size: 1.5rem;" id="grnMatchingIcon"></i>
                                        <div class="small fw-bold" id="grnMatchingTitle">Goods Receipt</div>
                                        <div id="grnMatchingStatus">Pending</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card" id="invoiceMatchingCard">
                                    <div class="card-body p-3 text-center">
                                        <i class="fas fa-file-invoice mb-2" style="font-size: 1.5rem;" id="invoiceMatchingIcon"></i>
                                        <div class="small fw-bold" id="invoiceMatchingTitle">Invoice Amount</div>
                                        <div id="invoiceMatchingStatus">Pending</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: Invoice Details -->
                    <div id="invoiceDetailsBlock" class="border rounded-3 p-3 mb-3 d-none">
                        <h6 class="mb-3 text-muted">
                            <i class="fas fa-file-alt text-info me-2"></i> Invoice Details
                        </h6>

                        <!-- Hidden fields for selected references -->
                        <input type="hidden" id="ThirdPartyID" name="ThirdPartyID" value="">
                        <input type="hidden" id="SupplierID" name="SupplierID" value="">
                        <input type="hidden" id="POReference" name="POReference" value="">
                        <input type="hidden" id="GRNReference" name="GRNReference" value="">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="InvoiceNumber" name="InvoiceNumber" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="InvoiceDate" name="InvoiceDate" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="DueDate" name="DueDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" id="currencySymbol">{{ $defaultCurrency->Symbol ?? 'KSh' }}</span>
                                    <input type="number" step="0.01" class="form-control" id="Amount" name="Amount" required>
                                </div>
                                <div class="form-text">
                                    Expected amount: <strong id="expectedAmount">{{ $defaultCurrency->Symbol ?? 'KSh' }} 0.00</strong>
                                </div>
                                <div id="amountValidation" class="invalid-feedback d-none"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Attachment</label>
                                <input type="file" class="form-control" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="form-text">PDF, DOC, DOCX, JPG, JPEG, PNG (max 10MB)</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="Description" rows="3" placeholder="Optional invoice description..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Section -->
                    <div id="submitSection" class="text-center d-none">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="btnSubmit"
                                onclick="if(this.form.checkValidity() && validate3WayMatching()){
                                    this.disabled = true;
                                    this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-2&quot;></i> Creating Invoice...';
                                    this.form.submit();
                                } else {
                                    return false;
                                }">
                            <i class="fas fa-save me-2"></i> Create Invoice
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- PO Details Modal -->
    <div class="modal fade" id="viewPOModal" tabindex="-1" aria-labelledby="viewPOTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPOTitle">Purchase Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="poDetailsContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- GRN Details Modal -->
    <div class="modal fade" id="viewGRNModal" tabindex="-1" aria-labelledby="viewGRNTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewGRNTitle">Goods Receipt Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="grnDetailsContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Wait for both DOM and jQuery to be ready
        function initializeInvoiceEntry() {
            // API endpoints (hoisted so all handlers can access)
            const quickSearchUrl = '{{ route('finance.invoiceentry-v2.api.suppliers.quick-search') }}';
            const findSupplierUrl = '{{ route('finance.invoiceentry-v2.api.suppliers.search') }}';

            // Elements
            const supplierSelect = (typeof window.$ !== 'undefined') ? window.$('#supplierSelect') : null;
            const supplierCard = document.getElementById('supplierCard');
            const ordersBlock = document.getElementById('ordersBlock');
            const grnsBlock = document.getElementById('grnsBlock');
            const invoiceDetailsBlock = document.getElementById('invoiceDetailsBlock');
            const submitSection = document.getElementById('submitSection');
            const invoiceForm = document.getElementById('invoiceForm');


            // Modal elements
            const viewPOModal = document.getElementById('viewPOModal');
            const viewGRNModal = document.getElementById('viewGRNModal');

            let currentSupplier = null;
            let currentOrders = [];
            let currentGRNs = [];
            let selectedPO = null;
            let selectedGRN = null;
            let currentCurrency = window.currencyConfig;
            let isSearching = false; // Prevent duplicate requests

            // Currency helper functions
            function formatCurrency(amount, currency = null) {
                const curr = currency || currentCurrency;
                const symbol = curr?.symbol || 'KSh';
                
                // Handle already formatted strings by removing commas first
                let numericAmount;
                if (typeof amount === 'string') {
                    numericAmount = parseFloat(amount.replace(/,/g, ''));
                } else {
                    numericAmount = parseFloat(amount);
                }
                
                return `${symbol} ${numericAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }

            function updateCurrencyDisplay(currency = null) {
                const curr = currency || currentCurrency;
                const symbol = curr?.symbol || 'KSh';

                // Update currency symbol in amount input
                const currencySymbolEl = document.getElementById('currencySymbol');
                if (currencySymbolEl) {
                    currencySymbolEl.textContent = symbol;
                }

                // Update current currency reference
                if (currency) {
                    currentCurrency = currency;
                }
            }

            // Helper function to show loading overlay
            function showLoadingOverlay(message = 'Loading...') {
                const loadingOverlay = document.getElementById('loadingOverlay');
                const loadingMessage = document.getElementById('loadingMessage');

                loadingMessage.textContent = message;
                loadingOverlay.classList.remove('d-none');

                // Ensure it's on top and covers everything
                loadingOverlay.style.zIndex = '99999';
                loadingOverlay.style.position = 'fixed';
                loadingOverlay.style.top = '0';
                loadingOverlay.style.left = '0';
                loadingOverlay.style.width = '100vw';
                loadingOverlay.style.height = '100vh';

                // Disable scrolling
                document.body.style.overflow = 'hidden';
            }

            // Helper function to hide loading overlay
            function hideLoadingOverlay() {
                const loadingOverlay = document.getElementById('loadingOverlay');
                loadingOverlay.classList.add('d-none');

                // Re-enable scrolling
                document.body.style.overflow = '';
            }

            // Helper: show loading spinner content
            function setLoading(targetId, title) {
                const el = document.getElementById(targetId);
                el.innerHTML = `
                    <div class="d-flex align-items-center justify-content-center py-4">
                        <div class="spinner-border text-primary me-2" role="status" aria-hidden="true"></div>
                        <span>Loading ${title}...</span>
                    </div>`;
            }

            // Initialize Select2 for Supplier search
            function initializeSupplierSelect() {
                if (!supplierSelect || typeof supplierSelect.select2 !== 'function') {
                    console.warn('Select2 not available; supplier search will be disabled.');
                    return;
                }

                // Destroy if previously initialized
                if (supplierSelect.hasClass('select2-hidden-accessible')) {
                    supplierSelect.select2('destroy');
                }

                supplierSelect.select2({
                    placeholder: 'Search and select a supplier...',
                    allowClear: true,
                    width: '100%',
                    minimumInputLength: 2,
                    ajax: {
                        transport: function (params, success, failure) {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]');
                            fetch(quickSearchUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                                },
                                body: JSON.stringify({ q: params.data.term })
                            })
                            .then(r => r.json())
                            .then(success)
                            .catch(failure);
                        },
                        delay: 250,
                        processResults: function (data) {
                            const results = Array.isArray(data?.results) ? data.results : [];
                            return { results, pagination: { more: false } };
                        }
                    },
                    templateResult: function (item) {
                        if (!item.id) return item.text;
                        return window.$('<div><strong>' + (item.supplier?.Name || item.text) + '</strong><br/><small class="text-muted">' + item.text + '</small></div>');
                    },
                    templateSelection: function (item) {
                        if (!item.id) return item.text;
                        return item.supplier?.Name || item.text;
                    }
                });

                supplierSelect.on('select2:select', function (e) {
                    const data = e.params.data;
                    const supplierId = data?.supplier?.SupplierID || data?.id;
                    if (!supplierId) return;

                    // Load full supplier details and related POs/GRNs
                    loadSupplierById(supplierId);
                });

                supplierSelect.on('select2:clear', function () {
                    resetSupplierView();
                });
            }

            function loadSupplierById(supplierId) {
                if (isSearching) return;
                isSearching = true;
                showLoadingOverlay('Loading supplier data...');
                resetSupplierView();

                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                fetch(findSupplierUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                    },
                    body: JSON.stringify({ supplier_id: supplierId })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        showNotification(data.error, 'error');
                        resetSupplierView();
                    } else {
                        currentSupplier = data.supplier;
                        currentOrders = data.orders;
                        currentGRNs = data.grns;
                        if (data.defaultCurrency) updateCurrencyDisplay(data.defaultCurrency);
                        showNotification(`Loaded supplier: ${data.supplier.Name}`, 'success');
                        displaySupplier();
                        displayOrders();
                    }
                })
                .catch(error => {
                    showNotification(`Failed to load supplier: ${error.message}`, 'error');
                    resetSupplierView();
                })
                .finally(() => {
                    isSearching = false;
                    hideLoadingOverlay();
                });
            }

            // Display supplier information
            function displaySupplier() {
                if (!currentSupplier) return;

                document.getElementById('suppName').textContent = currentSupplier.Name;
                document.getElementById('suppId').textContent = `ID: ${currentSupplier.SupplierID}`;
                document.getElementById('suppEmail').textContent = currentSupplier.Email || '—';
                document.getElementById('suppPhone').textContent = currentSupplier.Phone || '—';
                document.getElementById('suppRegNo').textContent = currentSupplier.RegistrationNumber || '—';
                document.getElementById('suppStatus').textContent = currentSupplier.IsActive ? 'Active' : 'Inactive';
                document.getElementById('suppStatus').className = `badge ${currentSupplier.IsActive ? 'bg-success' : 'bg-danger'}`;

                // Set hidden form fields
                document.getElementById('ThirdPartyID').value = currentSupplier.ThirdPartyID;
                document.getElementById('SupplierID').value = currentSupplier.SupplierID;

                supplierCard.classList.remove('d-none');
            }

            // Initialize Select2 for PO selection
            function initializePOSelect() {
                // Check if jQuery and Select2 are available
                if (typeof window.$ === 'undefined' || typeof window.$.fn.select2 === 'undefined') {
                    console.warn('Select2 or jQuery is not loaded. Using regular select.');
                    return;
                }

                const poSelect = window.$('#poSelect');

                // Destroy existing Select2 if it exists
                if (poSelect.hasClass('select2-hidden-accessible')) {
                    poSelect.select2('destroy');
                }

                // Clear and populate options
                poSelect.empty();
                poSelect.append('<option value="">-- Search and select a Purchase Order --</option>');

                currentOrders.forEach(order => {
                    const currency = order.Currency || currentCurrency;
                    const option = new Option(
                        `${order.OrderNo} - ${order.Description} (${currency.Symbol} ${order.TotalAmount}) - ${order.OrderDate}`,
                        order.Id,
                        false,
                        false
                    );
                    option.setAttribute('data-order', JSON.stringify(order));
                    poSelect.append(option);
                });

                try {
                    // Initialize Select2 with simpler configuration
                    poSelect.select2({
                        placeholder: 'Search and select a Purchase Order...',
                        allowClear: true,
                        width: '100%'
                    });
                } catch(error) {
                    console.error('Error initializing Select2:', error);
                    return;
                }

                // Handle selection change
                poSelect.on('select2:select', function(e) {
                    const selectedOption = e.params.data.element;
                    const orderData = JSON.parse(selectedOption.getAttribute('data-order'));

                    selectedPO = {
                        Id: e.params.data.id,
                        OrderNo: orderData.OrderNo
                    };

                    // Reset GRN selection and hide dependent cards
                    selectedGRN = null;
                    document.getElementById('GRNReference').value = '';
                    document.getElementById('matchingStatusBlock').classList.add('d-none');
                    invoiceDetailsBlock.classList.add('d-none');
                    submitSection.classList.add('d-none');

                    document.getElementById('POReference').value = e.params.data.id;
                    showSelectedPOSummary(orderData);
                    displayGRNsForPO(orderData.OrderNo);
                });

                // Handle clear/deselection
                poSelect.on('select2:clear', function(e) {
                    selectedPO = null;
                    document.getElementById('POReference').value = '';
                    hideSelectedPOSummary();
                    grnsBlock.classList.add('d-none');
                });

                // Fallback: regular change event for non-Select2 functionality
                poSelect.on('change', function(e) {
                    if (poSelect.hasClass('select2-hidden-accessible')) {
                        return; // Let Select2 handle it
                    }

                    const selectedValue = this.value;
                    if (selectedValue) {
                        const selectedOption = this.options[this.selectedIndex];
                        const orderData = JSON.parse(selectedOption.getAttribute('data-order'));

                        selectedPO = {
                            Id: selectedValue,
                            OrderNo: orderData.OrderNo
                        };

                        // Reset GRN selection and hide dependent cards
                        selectedGRN = null;
                        document.getElementById('GRNReference').value = '';
                        document.getElementById('matchingStatusBlock').classList.add('d-none');
                        invoiceDetailsBlock.classList.add('d-none');
                        submitSection.classList.add('d-none');

                        document.getElementById('POReference').value = selectedValue;
                        showSelectedPOSummary(orderData);
                        displayGRNsForPO(orderData.OrderNo);
                    } else {
                        selectedPO = null;
                        document.getElementById('POReference').value = '';
                        hideSelectedPOSummary();
                        grnsBlock.classList.add('d-none');
                    }
                });
            }


            // Display orders using Select2
            function displayOrders() {
                if (currentOrders.length === 0) {
                    document.getElementById('poSelectHint').innerHTML = '<i class="fas fa-inbox me-2"></i>No purchase orders found for this supplier';
                    document.getElementById('poSelectHint').className = 'form-text text-muted';
                } else {
                    document.getElementById('poSelectHint').textContent = `${currentOrders.length} purchase orders available. Start typing to search...`;

                    // Update quick stats
                    updatePOStats();

                    // Initialize Select2 after a short delay to ensure DOM is ready
                    setTimeout(() => {
                        initializePOSelect();
                    }, 100);
                }

                ordersBlock.classList.remove('d-none');
            }

            // Update PO statistics
            function updatePOStats() {
                const totalPOs = currentOrders.length;
                const totalValue = currentOrders.reduce((sum, order) => sum + parseFloat(order.TotalAmount.replace(/,/g, '')), 0);

                // Get date range
                const dates = currentOrders.map(order => new Date(order.OrderDate)).filter(date => !isNaN(date));
                let dateRange = '-';
                if (dates.length > 0) {
                    const minDate = new Date(Math.min(...dates));
                    const maxDate = new Date(Math.max(...dates));
                    dateRange = dates.length === 1 ?
                        minDate.toLocaleDateString() :
                        `${minDate.toLocaleDateString()} - ${maxDate.toLocaleDateString()}`;
                }

                document.getElementById('totalPOs').textContent = totalPOs;
                document.getElementById('dateRange').textContent = dateRange;
                document.getElementById('totalValue').textContent = formatCurrency(totalValue);
            }

            // Show selected PO summary
            function showSelectedPOSummary(order) {
                document.getElementById('selectedPONumber').textContent = order.OrderNo;
                document.getElementById('selectedPODate').textContent = order.OrderDate;
                const orderCurrency = order.Currency || currentCurrency;
                document.getElementById('selectedPOAmount').textContent = `${orderCurrency.Symbol} ${order.TotalAmount}`;
                document.getElementById('selectedPODescription').textContent = order.Description;

                document.getElementById('selectedPOSummary').classList.remove('d-none');
                document.getElementById('poSelect').style.display = 'none';
            }

            // Hide selected PO summary
            function hideSelectedPOSummary() {
                document.getElementById('selectedPOSummary').classList.add('d-none');
                document.getElementById('poSelect').style.display = 'block';
            }

            // Display GRNs for selected PO
            function displayGRNsForPO(orderNo) {
                const relevantGRNs = currentGRNs.filter(grn => grn.OrderNo === orderNo);
                const grnRows = document.getElementById('grnRows');
                const grnCountHint = document.getElementById('grnCountHint');

                grnRows.innerHTML = '';

                if (relevantGRNs.length === 0) {
                    grnRows.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-truck me-2"></i>No goods receipts found for this PO</td></tr>';
                    grnCountHint.textContent = '0 goods receipts';
                } else {
                    // Filter GRNs to show only those with matching quantities (3-way matching)
                    const validGRNs = relevantGRNs.filter(grn => {
                        return grn.TotalOrderedQty === grn.TotalReceivedQty;
                    });

                    const invalidGRNs = relevantGRNs.filter(grn => {
                        return grn.TotalOrderedQty !== grn.TotalReceivedQty;
                    });

                    // Display valid GRNs first
                    validGRNs.forEach(grn => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="text-center">
                                <input type="radio" name="selectedGRN" value="${grn.GRNID}" class="form-check-input">
                            </td>
                            <td><strong>${grn.GRNID}</strong></td>
                            <td>${grn.ReceivedDate}</td>
                            <td class="text-end">${grn.TotalOrderedQty}</td>
                            <td class="text-end text-success"><strong>${grn.TotalReceivedQty}</strong></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary view-grn-btn" data-grn='${JSON.stringify(grn)}'>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>`;
                        grnRows.appendChild(row);
                    });

                    // Display invalid GRNs with warning
                    invalidGRNs.forEach(grn => {
                        const row = document.createElement('tr');
                        row.className = 'table-warning';
                        row.innerHTML = `
                            <td class="text-center">
                                <input type="radio" disabled class="form-check-input">
                                <small class="text-muted d-block">Disabled</small>
                            </td>
                            <td>
                                <strong>${grn.GRNID}</strong>
                                <br><small class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Qty Mismatch</small>
                            </td>
                            <td>${grn.ReceivedDate}</td>
                            <td class="text-end">${grn.TotalOrderedQty}</td>
                            <td class="text-end text-danger"><strong>${grn.TotalReceivedQty}</strong></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary view-grn-btn" data-grn='${JSON.stringify(grn)}'>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>`;
                        grnRows.appendChild(row);
                    });

                    // Update count hint
                    if (validGRNs.length === 0) {
                        grnCountHint.innerHTML = `<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>${relevantGRNs.length} goods receipt(s) found, but none match PO quantities (3-way matching required)</span>`;
                    } else {
                        grnCountHint.textContent = `${validGRNs.length} valid goods receipt(s) available for invoicing`;
                        if (invalidGRNs.length > 0) {
                            grnCountHint.innerHTML += `<br><small class="text-muted">${invalidGRNs.length} additional GRN(s) disabled due to quantity mismatches</small>`;
                        }
                    }

                    // Auto-select GRN if only one valid exists
                    if (validGRNs.length === 1) {
                        const radio = document.querySelector('input[name="selectedGRN"]:not([disabled])');
                        if (radio) {
                            radio.checked = true;
                            selectedGRN = validGRNs[0];
                            document.getElementById('GRNReference').value = validGRNs[0].GRNID;
                            showInvoiceDetails();
                        }
                    }
                }

                grnsBlock.classList.remove('d-none');

                // Add event listeners for GRN selection
                document.querySelectorAll('input[name="selectedGRN"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.checked) {
                            selectedGRN = relevantGRNs.find(grn => grn.GRNID === this.value);
                            document.getElementById('GRNReference').value = this.value;
                            showInvoiceDetails();
                        }
                    });
                });

                // Add event listeners for View GRN buttons
                document.querySelectorAll('.view-grn-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const grn = JSON.parse(this.getAttribute('data-grn'));
                        showGRNModal(grn);
                    });
                });
            }

            // Show invoice details section
            function showInvoiceDetails() {
                if (selectedPO && selectedGRN) {
                    // Set expected amount from PO
                    const selectedOrder = currentOrders.find(order => order.Id == selectedPO.Id);
                    if (selectedOrder) {
                        const expectedAmountValue = parseFloat(selectedOrder.TotalAmount.replace(/,/g, ''));
                        const orderCurrency = selectedOrder.Currency || currentCurrency;
                        document.getElementById('expectedAmount').textContent = `${orderCurrency.Symbol} ${selectedOrder.TotalAmount}`;

                        // Update currency display if order has different currency
                        if (selectedOrder.Currency) {
                            updateCurrencyDisplay(selectedOrder.Currency);
                        }

                        // Store expected amount for validation
                        window.expectedInvoiceAmount = expectedAmountValue;

                        // Set up amount validation
                        setupAmountValidation();
                    }

                    // Show 3-way matching status
                    document.getElementById('matchingStatusBlock').classList.remove('d-none');
                    updateMatchingStatus();

                    invoiceDetailsBlock.classList.remove('d-none');
                    submitSection.classList.remove('d-none');
                }
            }

            // Update 3-way matching status indicators
            function updateMatchingStatus() {
                // Update GRN status
                updateGRNMatchingStatus();

                // Update invoice amount status (will be updated as user types)
                updateInvoiceAmountMatchingStatus();
            }

            // Update GRN matching status
            function updateGRNMatchingStatus() {
                const grnCard = document.getElementById('grnMatchingCard');
                const grnIcon = document.getElementById('grnMatchingIcon');
                const grnStatus = document.getElementById('grnMatchingStatus');

                if (selectedGRN && selectedGRN.TotalOrderedQty === selectedGRN.TotalReceivedQty) {
                    grnCard.className = 'card border-success';
                    grnIcon.className = 'fas fa-truck text-success mb-2';
                    grnStatus.innerHTML = '✓ Quantities Match';
                    grnStatus.className = 'text-success';
                } else if (selectedGRN) {
                    grnCard.className = 'card border-warning';
                    grnIcon.className = 'fas fa-truck text-warning mb-2';
                    grnStatus.innerHTML = '⚠ Quantity Mismatch';
                    grnStatus.className = 'text-warning';
                } else {
                    grnCard.className = 'card border-secondary';
                    grnIcon.className = 'fas fa-truck text-muted mb-2';
                    grnStatus.innerHTML = 'Pending Selection';
                    grnStatus.className = 'text-muted';
                }
            }

            // Update invoice amount matching status
            function updateInvoiceAmountMatchingStatus() {
                const invoiceCard = document.getElementById('invoiceMatchingCard');
                const invoiceIcon = document.getElementById('invoiceMatchingIcon');
                const invoiceStatus = document.getElementById('invoiceMatchingStatus');

                const amountInput = document.getElementById('Amount');
                const enteredAmount = parseFloat(amountInput.value) || 0;
                const expectedAmount = window.expectedInvoiceAmount || 0;
                const tolerance = 0.01;

                if (enteredAmount === 0) {
                    invoiceCard.className = 'card border-secondary';
                    invoiceIcon.className = 'fas fa-file-invoice text-muted mb-2';
                    invoiceStatus.innerHTML = 'Enter Amount';
                    invoiceStatus.className = 'text-muted';
                } else if (Math.abs(enteredAmount - expectedAmount) <= tolerance) {
                    invoiceCard.className = 'card border-success';
                    invoiceIcon.className = 'fas fa-file-invoice text-success mb-2';
                    invoiceStatus.innerHTML = '✓ Amount Matches';
                    invoiceStatus.className = 'text-success';
                } else {
                    invoiceCard.className = 'card border-danger';
                    invoiceIcon.className = 'fas fa-file-invoice text-danger mb-2';
                    invoiceStatus.innerHTML = '✗ Amount Mismatch';
                    invoiceStatus.className = 'text-danger';
                }
            }

            // Setup amount validation for 3-way matching
            function setupAmountValidation() {
                const amountInput = document.getElementById('Amount');
                const amountValidation = document.getElementById('amountValidation');
                const submitBtn = document.getElementById('btnSubmit');

                if (!amountInput || !amountValidation || !submitBtn) return;

                // Real-time validation
                amountInput.addEventListener('input', function() {
                    validateInvoiceAmount();
                });

                amountInput.addEventListener('blur', function() {
                    validateInvoiceAmount();
                });
            }

            // Validate invoice amount against PO amount (3-way matching)
            function validateInvoiceAmount() {
                const amountInput = document.getElementById('Amount');
                const amountValidation = document.getElementById('amountValidation');
                const submitBtn = document.getElementById('btnSubmit');

                if (!amountInput || !amountValidation || !submitBtn || !window.expectedInvoiceAmount) return;

                const enteredAmount = parseFloat(amountInput.value) || 0;
                const expectedAmount = window.expectedInvoiceAmount;
                const tolerance = 0.01; // Allow 1 cent tolerance for rounding

                // Update matching status indicator
                updateInvoiceAmountMatchingStatus();

                if (Math.abs(enteredAmount - expectedAmount) > tolerance) {
                    // Amount doesn't match
                    amountInput.classList.add('is-invalid');
                    amountValidation.classList.remove('d-none');

                    const symbol = currentCurrency?.symbol || 'KSh';
                    if (enteredAmount > expectedAmount) {
                        amountValidation.textContent = `Invoice amount (${symbol} ${enteredAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}) exceeds PO amount (${symbol} ${expectedAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}). 3-way matching required.`;
                    } else if (enteredAmount < expectedAmount && enteredAmount > 0) {
                        amountValidation.textContent = `Invoice amount (${symbol} ${enteredAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}) is less than PO amount (${symbol} ${expectedAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}). 3-way matching required.`;
                    } else {
                        amountValidation.textContent = 'Please enter a valid invoice amount that matches the PO amount.';
                    }

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-times me-2"></i> Amount Must Match PO';
                    submitBtn.className = 'btn btn-danger btn-lg px-5';
                } else {
                    // Amount matches
                    amountInput.classList.remove('is-invalid');
                    amountValidation.classList.add('d-none');

                    // Check if all 3-way matching criteria are met
                    const isFullyMatched = selectedGRN &&
                                         selectedGRN.TotalOrderedQty === selectedGRN.TotalReceivedQty &&
                                         enteredAmount > 0;

                    if (isFullyMatched) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check-double me-2"></i> Create Invoice (3-Way Matched)';
                        submitBtn.className = 'btn btn-success btn-lg px-5';
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Create Invoice';
                        submitBtn.className = 'btn btn-primary btn-lg px-5';
                    }
                }
            }

            // Show PO modal
            function showPOModal(order) {
                setLoading('poDetailsContent', 'PO');

                let poContent = `
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Order No:</strong> ${order.OrderNo}</div>
                        <div class="col-md-6"><strong>Order Date:</strong> ${order.OrderDate}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12"><strong>Description:</strong> ${order.Description}</div>
                    </div>`;

                if (order.OrderLines && order.OrderLines.length > 0) {
                    poContent += `
                        <h6>Order Lines</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    order.OrderLines.forEach(line => {
                        poContent += `
                            <tr>
                                <td>${line.ItemName}</td>
                                <td class="text-end">${line.Quantity}</td>
                                <td class="text-end">${formatCurrency(line.UnitPrice, order.Currency)}</td>
                                <td class="text-end">${formatCurrency(line.LineTotal, order.Currency)}</td>
                            </tr>`;
                    });

                    poContent += `
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <th colspan="3">Total Amount</th>
                                        <th class="text-end">${formatCurrency(order.TotalAmount, order.Currency)}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>`;
                }

                document.getElementById('poDetailsContent').innerHTML = poContent;
                new bootstrap.Modal(viewPOModal).show();
            }

            // Show GRN modal
            function showGRNModal(grn) {
                setLoading('grnDetailsContent', 'GRN');

                let grnContent = `
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>GRN ID:</strong> ${grn.GRNID}</div>
                        <div class="col-md-6"><strong>Received Date:</strong> ${grn.ReceivedDate}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>PO Reference:</strong> ${grn.OrderNo}</div>
                    </div>`;

                if (grn.Items && grn.Items.length > 0) {
                    grnContent += `
                        <h6>Received Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-end">Ordered Qty</th>
                                        <th class="text-end">Received Qty</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    grn.Items.forEach(item => {
                        grnContent += `
                            <tr>
                                <td>${item.ItemName}</td>
                                <td class="text-end">${item.POQTY}</td>
                                <td class="text-end">${item.ReceivedQTY}</td>
                            </tr>`;
                    });

                    grnContent += `
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <th>Total</th>
                                        <th class="text-end">${grn.TotalOrderedQty}</th>
                                        <th class="text-end">${grn.TotalReceivedQty}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>`;
                }

                document.getElementById('grnDetailsContent').innerHTML = grnContent;
                new bootstrap.Modal(viewGRNModal).show();
            }

            // Reset supplier view
            function resetSupplierView() {
                supplierCard.classList.add('d-none');
                ordersBlock.classList.add('d-none');
                grnsBlock.classList.add('d-none');
                invoiceDetailsBlock.classList.add('d-none');
                submitSection.classList.add('d-none');
                document.getElementById('matchingStatusBlock').classList.add('d-none');

                // Reset Select2 if it exists
                if (typeof window.$ !== 'undefined') {
                    const poSelect = window.$('#poSelect');
                    if (poSelect.hasClass('select2-hidden-accessible')) {
                        poSelect.select2('destroy');
                    }
                    poSelect.empty().append('<option value="">-- Search and select a Purchase Order --</option>');
                } else {
                    // Fallback to vanilla JS
                    const poSelect = document.getElementById('poSelect');
                    if (poSelect) {
                        poSelect.innerHTML = '<option value="">-- Search and select a Purchase Order --</option>';
                    }
                }

                // Hide PO summary
                hideSelectedPOSummary();

                // Reset variables
                currentSupplier = null;
                currentOrders = [];
                currentGRNs = [];
                selectedPO = null;
                selectedGRN = null;
            }

            // Initialize supplier Select2
            initializeSupplierSelect();

            // PO related event listeners
            const viewSelectedPOBtn = document.getElementById('viewSelectedPO');
            if (viewSelectedPOBtn) {
                viewSelectedPOBtn.addEventListener('click', function() {
                    if (selectedPO) {
                        const order = currentOrders.find(o => o.Id == selectedPO.Id);
                        if (order) {
                            showPOModal(order);
                        }
                    }
                });
            }

            document.getElementById('changePO').addEventListener('click', function() {
                hideSelectedPOSummary();
                if (typeof window.$ !== 'undefined') {
                    window.$('#poSelect').val(null).trigger('change');
                } else {
                    // Fallback to vanilla JS
                    const poSelect = document.getElementById('poSelect');
                    if (poSelect) {
                        poSelect.value = '';
                        poSelect.dispatchEvent(new Event('change'));
                    }
                }
                // Reset all selections and hide all dependent cards
                selectedPO = null;
                selectedGRN = null;
                document.getElementById('GRNReference').value = '';
                grnsBlock.classList.add('d-none');
                document.getElementById('matchingStatusBlock').classList.add('d-none');
                invoiceDetailsBlock.classList.add('d-none');
                submitSection.classList.add('d-none');
            });


            // Form submission will be handled by HTML onclick validation

            // Comprehensive 3-way matching validation
            function validate3WayMatching() {

                // Check if PO is selected
                if (!selectedPO) {
                    showNotification('Please select a Purchase Order', 'error');
                    return false;
                }

                // Check if GRN is selected
                if (!selectedGRN) {
                    showNotification('Please select a Goods Receipt', 'error');
                    return false;
                }

                // Check GRN quantity matching
                if (selectedGRN.TotalOrderedQty !== selectedGRN.TotalReceivedQty) {
                    showNotification('Selected GRN has quantity mismatch. Ordered and received quantities must match for 3-way matching.', 'error');
                    return false;
                }

                // Check invoice amount matching
                const amountInput = document.getElementById('Amount');
                const enteredAmount = parseFloat(amountInput.value) || 0;
                const expectedAmount = window.expectedInvoiceAmount || 0;
                const tolerance = 0.01;

                if (Math.abs(enteredAmount - expectedAmount) > tolerance) {
                    const currencySymbol = window.currencyConfig?.symbol || 'KSh';
                    showNotification(`Invoice amount (${currencySymbol} ${enteredAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}) must match PO amount (${currencySymbol} ${expectedAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}) for 3-way matching.`, 'error');
                    return false;
                }

                return true;
            }

            // Notification function using Bootstrap toast or fallback
            function showNotification(message, type = 'info') {
                // Create Bootstrap toast
                const toastHtml = `
                    <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} border-0 position-fixed"
                         style="top: 20px; right: 20px; z-index: 10000;" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;

                // Add to body
                document.body.insertAdjacentHTML('beforeend', toastHtml);

                // Initialize and show toast
                const toastElement = document.body.lastElementChild;
                if (typeof window.bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
                    toast.show();

                    // Remove element after hiding
                    toastElement.addEventListener('hidden.bs.toast', () => {
                        toastElement.remove();
                    });
                } else {
                    // Fallback: remove after 5 seconds
                    setTimeout(() => {
                        if (toastElement.parentNode) {
                            toastElement.remove();
                        }
                    }, 5000);
                }
            }

            // Escape key to cancel loading
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const loadingOverlay = document.getElementById('loadingOverlay');
                    if (!loadingOverlay.classList.contains('d-none')) {
                        hideLoadingOverlay();
                    }
                }
            });
        }

        // Initialize when DOM and scripts are ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeInvoiceEntry);
        } else {
            // DOM is already ready
            initializeInvoiceEntry();
        }

        // Also try to initialize when window loads (backup)
        window.addEventListener('load', function() {
            if (typeof initializeInvoiceEntry === 'function') {
                initializeInvoiceEntry();
            }
        });
    </script>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<script>
    // Debug: Check if jQuery is available after scripts load
    console.log('Scripts section loaded. jQuery available:', typeof window.$ !== 'undefined');
    console.log('Select2 available:', typeof window.$ !== 'undefined' && typeof window.$.fn.select2 !== 'undefined');
</script>
@endsection
