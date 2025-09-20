@extends('layouts.app')
@section('title', 'Create Purchase Order - Unified System')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
        .origination-card {
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .origination-card.selected {
            border-color: #007bff;
            background-color: #f8f9ff;
        }
        .origination-card:hover {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .source-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .item-row {
            border: 1px solid #e9ecef;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }
        .readonly-input {
            background-color: #f8f9fa !important;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-3">
        <h2 class="h3 mb-0">
            <i class="fas fa-file-plus text-primary"></i> 
            Create Purchase Order - Unified System
        </h2>
            <a href="{{ route('purchaseOrder.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Orders
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>There were some problems with your input:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    <form action="{{ route('purchaseOrder.store') }}" method="post" id="unifiedPurchaseOrderForm" novalidate>
            @csrf
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- ========================================= -->
        <!-- STEP 1: ORIGINATION TYPE SELECTION -->
        <!-- ========================================= -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-route"></i> Step 1: Select LPO Origination Type
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- RFQ-Based -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card origination-card h-100" data-type="rfq">
                            <div class="card-body text-center">
                                <i class="fas fa-file-contract fa-3x text-info mb-3"></i>
                                <h6 class="card-title">RFQ-Based</h6>
                                <p class="card-text small">Create PO from existing RFQ responses</p>
                                <span class="badge badge-info">{{ $rfqs->count() }} Available</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Award-Based -->  
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card origination-card h-100" data-type="award">
                            <div class="card-body text-center">
                                <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                                <h6 class="card-title">Award-Based</h6>
                                <p class="card-text small">Create PO from tender/RFQ awards</p>
                                <span class="badge badge-warning">{{ $awards->count() }} Available</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contract-Based -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card origination-card h-100" data-type="contract">
                            <div class="card-body text-center">
                                <i class="fas fa-file-signature fa-3x text-success mb-3"></i>
                                <h6 class="card-title">Contract-Based</h6>
                                <p class="card-text small">Create PO from active contracts</p>
                                <span class="badge badge-success">{{ $contracts->count() }} Available</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Direct Procurement -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card origination-card h-100" data-type="direct">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-cart fa-3x text-secondary mb-3"></i>
                                <h6 class="card-title">Direct Procurement</h6>
                                <p class="card-text small">Create PO from procurement plans</p>
                                <span class="badge badge-secondary">{{ $procurementPlans->count() }} Available</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden input to store selected type -->
                <input type="hidden" id="originationType" name="origination_type" required>
            </div>
        </div>

        <!-- ========================================= -->
        <!-- STEP 2: SOURCE SELECTION SECTIONS -->
        <!-- ========================================= -->
        
        <!-- RFQ Selection Section -->
        <div class="card mb-4 form-section" id="rfq-section">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-file-contract"></i> Step 2: Select RFQ</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label>RFQ Reference <span class="text-danger">*</span></label>
                        <select class="form-control" name="rfq_id" id="rfq_id">
                            <option value="">Select RFQ</option>
                        @foreach($rfqs as $rfq)
                                <option value="{{ $rfq->RFQNumber }}">{{ $rfq->RFQNumber }}</option>
                        @endforeach
                    </select>
                </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label><br>
                        <button type="button" class="btn btn-info" id="loadRFQData">
                            <i class="fas fa-download"></i> Load RFQ Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Award Selection Section -->
        <div class="card mb-4 form-section" id="award-section">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Step 2: Select Award</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label>Award Reference <span class="text-danger">*</span></label>
                        <select class="form-control" name="award_id" id="award_id">
                            <option value="">Select Award</option>
                            @foreach($awards as $award)
                                <option value="{{ $award['id'] }}" data-type="{{ $award['type'] }}">
                                    {{ $award['tender_no'] }} - {{ $award['title'] }} ({{ $award['supplier_name'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label><br>
                        <button type="button" class="btn btn-warning" id="loadAwardData">
                            <i class="fas fa-download"></i> Load Award Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contract Selection Section -->
        <div class="card mb-4 form-section" id="contract-section">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-file-signature"></i> Step 2: Select Contract</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label>Contract Reference <span class="text-danger">*</span></label>
                        <select class="form-control" name="contract_id" id="contract_id">
                            <option value="">Select Contract</option>
                            @foreach($contracts as $contract)
                                <option value="{{ $contract['id'] }}">
                                    {{ $contract['contract_ref'] }} - {{ $contract['title'] }} ({{ $contract['supplier_name'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label><br>
                        <button type="button" class="btn btn-success" id="loadContractData">
                            <i class="fas fa-download"></i> Load Contract Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Direct Procurement Selection Section -->
        <div class="card mb-4 form-section" id="direct-section">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Step 2: Direct Procurement Selection</h5>
            </div>
            <div class="card-body">
                <!-- Step 2a: Select Plan -->
                <div id="plan-selection-step">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label>Select Procurement Plan <span class="text-danger">*</span></label>
                            <select class="form-control" name="plan_id" id="plan_id">
                                <option value="">Choose Plan</option>
                                @foreach($procurementPlans as $plan)
                                    <option value="{{ $plan['plan_id'] }}">
                                        {{ $plan['plan_ref'] }} - {{ $plan['plan_title'] }} ({{ $plan['items_count'] ?? 'Unknown' }} items)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-secondary" id="loadPlanCategories">
                                <i class="fas fa-tags"></i> Load Item Categories
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2b: Select Item Category -->
                <div id="category-selection-step" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Plan Selected:</strong> 
                        <span id="selected-plan-info"></span>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label>Filter by Item Category <span class="text-danger">*</span></label>
                            <select class="form-control" id="item_category_id">
                                <option value="">Choose Item Category</option>
                                <!-- Categories will be populated via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-info" id="loadPrequalifiedSuppliers">
                                <i class="fas fa-users"></i> Load Prequalified Suppliers
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2c: Prequalified Suppliers Display -->
                <div id="suppliers-display-step" style="display: none;">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <strong>Category Selected:</strong> 
                        <span id="selected-category-info"></span>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-building"></i> Prequalified Suppliers for This Category</h6>
                        </div>
                        <div class="card-body">
                            <div id="prequalified-suppliers-list">
                                <!-- Suppliers will be populated here -->
                            </div>
                            <button type="button" class="btn btn-success mt-2" id="loadCategoryItems">
                                <i class="fas fa-box"></i> Proceed to Item Selection
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2d: Items Selection Grid -->
                <div id="items-selection-step" style="display: none;">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-list"></i> Select Items from Plan & Category</h6>
                        </div>
                        <div class="card-body">
                            <!-- Search Bar -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <input type="text" class="form-control" id="planItemSearch" 
                                           placeholder="🔍 Search items by name or description...">
                                </div>
                            </div>
                            
                            <!-- Items Grid -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="planItemsTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th style="width: 25%;">Item Name</th>
                                            <th style="width: 25%;">Description</th>
                                            <th style="width: 10%;">Planned Qty</th>
                                            <th style="width: 12%;">Unit Cost</th>
                                            <th style="width: 12%;">PO Quantity</th>
                                            <th style="width: 10%;">Delivery Date</th>
                                            <th style="width: 6%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="planItemsTableBody">
                                        <!-- Dynamic rows will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Selected Items Summary -->
                            <div id="selectedPlanItemsSummary" style="display: none;">
                                <h6 class="mt-3"><i class="fas fa-list"></i> Selected Items for PO</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item</th>
                                                <th>PO Quantity</th>
                                                <th>Unit Cost</th>
                                                <th>Line Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="selectedPlanItemsBody">
                                            <!-- Selected items will appear here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Finalize Selection Button -->
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-success" id="finalizePlanSelection" style="display: none;">
                                    <i class="fas fa-check"></i> Finalize Item Selection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================= -->
        <!-- STEP 3: SOURCE INFO DISPLAY -->
        <!-- ========================================= -->
        <div class="card mb-4" id="source-info-card" style="display: none;">
            <div class="card-header source-info-card">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Step 3: Source Information</h5>
            </div>
            <div class="card-body" id="source-info-content">
                <!-- Dynamic content populated by JavaScript -->
            </div>
        </div>

        <!-- ========================================= -->
        <!-- STEP 4: PO DETAILS -->
        <!-- ========================================= -->
        <div class="card mb-4" id="po-details-card" style="display: none;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-file-alt"></i> Step 4: Purchase Order Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                <div class="col-md-4">
                    <label>LPO Number <span class="text-danger">*</span></label>
                        <input type="text" name="LPONo" class="form-control" value="{{ old('LPONo', 'LPO-' . uniqid()) }}" readonly required/>
                </div>
                <div class="col-md-4">
                    <label>Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="pODate" value="{{ old('pODate', date('Y-m-d')) }}" required/>
                    </div>
                    <div class="col-md-4">
                        <label>Priority <span class="text-danger">*</span></label>
                        <select class="form-control" name="priority" required>
                            <option value="High">High</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Low">Low</option>
                        </select>
                </div>
            </div>

                <!-- Supplier Section -->
                <div class="row mb-3">
                    <div class="col-md-8">
                    <label>Supplier <span class="text-danger">*</span></label>
                        <select class="form-control" id="supplier" name="supplier" required>
                            <option value="">Select supplier</option>
                            <!-- For direct procurement, show all suppliers -->
                            @if(request()->get('type') === 'direct')
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->Id }}" data-address="{{ $supplier->Address ?? '' }}">
                                        {{ $supplier->TradingName ?? $supplier->ThirdPartyName }}
                                    </option>
                                @endforeach
                            @endif
                    </select>
                </div>
                    <div class="col-md-4">
                    <label>Address</label>
                    <input type="text" class="form-control" name="address" placeholder="Supplier address" readonly/>
                </div>
            </div>

                <!-- Payment Terms -->
            <div class="row mb-4">
                    <div class="col-md-6">
                    <label>Payment Terms <span class="text-danger">*</span></label>
                        <select class="form-control" name="terms" required>
                            <option value="">Select Payment Term</option>
                        @foreach ($paymentTerms as $term)
                                <option value="{{ $term->ID }}">{{ $term->Description }}</option>
                        @endforeach
                    </select>
                    </div>
                    <div class="col-md-6">
                        <label>Delivery Terms</label>
                        <input type="text" class="form-control" name="delivery_terms" placeholder="Enter delivery terms"/>
                    </div>
                </div>
                </div>
            </div>

        <!-- ========================================= -->
        <!-- STEP 5: ITEMS SECTION -->
        <!-- ========================================= -->
        <div class="card mb-4" id="items-section-card" style="display: none;">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Step 5: Items</h5>
                    <button type="button" class="btn btn-light btn-sm" id="add-item-btn">
                        <i class="fas fa-plus"></i> Add Item
                </button>
            </div>
            </div>
            <div class="card-body">
                <div id="items-container">
                    <!-- Dynamic items will be populated here -->
            </div>

                <!-- Items Table for Manual Entry (Direct Procurement/RFQ) -->
                <div id="manual-items-section" style="display: none;">
                    <div class="table-responsive">
                <table class="table table-bordered" id="line-items-table">
                    <thead class="table-light">
                    <tr>
                                    <th style="width: 3%;">#</th>
                                    <th style="width: 12%;">Item Type</th>
                                    <th style="width: 15%;">Item Name</th>
                                    <th style="width: 20%;">Description</th>
                                    <th style="width: 8%;">Quantity</th>
                                    <th style="width: 10%;">Unit Price</th>
                                    <th style="width: 8%;">Tax %</th>
                                    <th style="width: 8%;">Discount %</th>
                                    <th style="width: 12%;">Line Total</th>
                                    <th style="width: 4%;">Action</th>
                    </tr>
                    </thead>
                    <tbody id="item-rows">
                                <!-- Dynamic rows will be added here -->
                    </tbody>
                </table>
            </div>
                </div>
            </div>
            </div>

        <!-- ========================================= -->
        <!-- STEP 6: TOTALS & NOTES -->
        <!-- ========================================= -->
        <div class="card mb-4" id="totals-section-card" style="display: none;">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-calculator"></i> Step 6: Summary & Notes</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label>Additional Notes</label>
                        <textarea class="form-control" rows="4" name="notes" placeholder="Optional notes for this purchase order"></textarea>
                    </div>
                    <div class="col-md-4">
                    <div class="mb-2">
                        <label>Exclusive Total</label>
                        <input type="text" class="form-control exclusiveTotal" name="exclusiveTotal" readonly/>
                    </div>
                    <div class="mb-2">
                        <label>Tax Amount</label>
                        <input type="text" class="form-control taxAmount" name="taxAmount" readonly/>
                    </div>
                        <div class="mb-2">
                            <label><strong>Inclusive Total</strong></label>
                            <input type="text" class="form-control inclusiveTotal" name="inclusiveTotal" readonly style="font-weight: bold; font-size: 1.1em;"/>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

        <!-- ========================================= -->
        <!-- ACTION BUTTONS -->
        <!-- ========================================= -->
        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary" id="saveOrder">
                <i class="fas fa-save"></i> Create Purchase Order
            </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
    // =====================================================
    // GLOBAL VARIABLES AND INITIALIZATION
    // =====================================================
    
    let currentOriginationType = '';
    let loadedSourceData = null;
    let itemRowCounter = 0;

    // Prepare data for JavaScript
        const rfqResponses = @json($rfqResponses);
    const suppliers = @json($suppliers);
    const itemTypes = @json($itemTypes);

    // Item type options HTML for dynamic rows
        const itemTypeOptions = `{!! $itemTypes->map(function($type) {
        return "<option value='{$type->Id}'>{$type->TypeName}</option>";
    })->implode('') !!}`;
    
    $(document).ready(function() {
        initializeOriginationSelection();
        initializeEventHandlers();
        initializeFormSubmission();
    });
    
    function initializeFormSubmission() {
        $('#unifiedPurchaseOrderForm').on('submit', function(e) {
            e.preventDefault();
            
            // Debug logging
            console.log('Form submit triggered');
            console.log('Current origination type:', currentOriginationType);
            console.log('validateForm function available:', typeof validateForm);
            console.log('validateFormBasedOnType function available:', typeof validateFormBasedOnType);
            
            // Basic validation
            try {
                if (!validateForm()) {
                    console.log('Basic validation failed');
                    return false;
                }
                
                if (!validateFormBasedOnType()) {
                    console.log('Type-based validation failed');
                    return false;
                }
            } catch (error) {
                console.error('Validation error:', error);
                alert('Validation error: ' + error.message);
                return false;
            }
            
            const submitBtn = $('#saveOrder');
            const originalText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
            
            // Submit form via AJAX
            const formData = $(this).serialize();
            const actionUrl = $(this).attr('action');
            
            // Check supplier field specifically
            const supplierValue = $('#supplier').val();
            const supplierText = $('#supplier option:selected').text();
            const hiddenSupplierValue = $('input[name="supplier"]:hidden').val();
            
            console.log('=== FORM SUBMISSION DEBUG ===');
            console.log('Submitting to URL:', actionUrl);
            console.log('Supplier field value:', supplierValue);
            console.log('Supplier field text:', supplierText);
            console.log('Supplier field disabled?:', $('#supplier').prop('disabled'));
            console.log('Supplier field visible?:', $('#supplier').is(':visible'));
            console.log('Supplier dropdown options:', $('#supplier option').length);
            console.log('Hidden supplier input value:', hiddenSupplierValue);
            console.log('Hidden supplier inputs count:', $('input[name="supplier"]:hidden').length);
            console.log('Origination type:', currentOriginationType);
            console.log('Full form data:', formData);
            console.log('============================');
            
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success || response.message) {
                        // Show success message
                        alert(response.message || 'Purchase order created successfully!');
                        
                        // Redirect if route is provided
                        if (response.route) {
                            window.location.href = response.route;
                        } else {
                            // Fallback redirect
                            window.location.href = "{{ route('purchaseOrder.index') }}";
                        }
                    } else {
                        alert('Unexpected response format');
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error Details:');
                    console.error('Status:', xhr.status);
                    console.error('Status Text:', xhr.statusText);
                    console.error('Response Text:', xhr.responseText);
                    console.error('Error:', error);
                    console.error('XHR Object:', xhr);
                    
                    let errorMessage = 'An error occurred while creating the purchase order.';
                    
                    if (xhr.status === 404) {
                        errorMessage = `404 Error: The route "${actionUrl}" was not found. Please check your routes configuration.`;
                    } else if (xhr.status === 422) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = 'Validation errors: ' + Object.values(xhr.responseJSON.errors).flat().join(', ');
                        } else {
                            errorMessage = 'Validation failed: ' + (xhr.responseJSON?.message || 'Unknown validation error');
                        }
                    } else if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            // Handle validation errors
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join('\\n');
                        }
                    }
                    
                    alert(errorMessage);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
            
            return false;
        });
    }

    // =====================================================
    // ORIGINATION TYPE SELECTION
    // =====================================================
    
    function initializeOriginationSelection() {
        $('.origination-card').on('click', function() {
            const selectedType = $(this).data('type');
            
            // Update visual selection
            $('.origination-card').removeClass('selected');
            $(this).addClass('selected');
            
            // Set hidden input
            $('#originationType').val(selectedType);
            currentOriginationType = selectedType;
            
            // Initialize supplier dropdown for direct procurement immediately
            if (selectedType === 'direct') {
                console.log('Direct procurement selected, initializing supplier dropdown...');
                showSupplierSelectionForDirect();
                // PO details section will be shown after plan items are selected
            }
            
            // Show appropriate section
            showSourceSelectionSection(selectedType);
            
            // Hide subsequent sections
            hideSubsequentSections();
        });
    }
    
    function showSourceSelectionSection(type) {
        // Hide all form sections
        $('.form-section').removeClass('active').hide();
        
        // Show the selected section
        $(`#${type}-section`).addClass('active').show();
        
        console.log(`Showing section: ${type}-section`);
    }
    
    function hideSubsequentSections() {
        $('#source-info-card, #po-details-card, #items-section-card, #totals-section-card').hide();
    }

    // =====================================================
    // EVENT HANDLERS FOR EACH ORIGINATION TYPE
    // =====================================================
    
    function initializeEventHandlers() {
        // RFQ Data Loading - Button Click (Manual)
        $('#loadRFQData').on('click', function() {
            const rfqId = $('#rfq_id').val();
            if (!rfqId) {
                alert('Please select an RFQ first.');
                return;
            }
            loadRFQData(rfqId);
        });
        
        // RFQ Data Loading - Automatic on Dropdown Change
        $('#rfq_id').on('change', function() {
            const rfqId = $(this).val();
            console.log('RFQ dropdown changed, RFQ ID:', rfqId);
            
            if (rfqId) {
                console.log('Auto-loading RFQ data for ID:', rfqId);
                loadRFQData(rfqId);
            } else {
                // Clear data when no RFQ selected
                console.log('No RFQ selected, clearing data');
                $('#items-container').empty();
                $('#supplier').empty().append('<option value="">Select supplier</option>');
                hideSubsequentSections();
            }
        });
        
        // Award Data Loading - Button Click (Manual)
        $('#loadAwardData').on('click', function() {
            const awardId = $('#award_id').val();
            if (!awardId) {
                alert('Please select an award first.');
                return;
            }
            loadAwardData(awardId);
        });
        
        // Award Data Loading - Automatic on Dropdown Change
        $('#award_id').on('change', function() {
            const awardId = $(this).val();
            console.log('Award dropdown changed, award ID:', awardId);
            
            if (awardId) {
                console.log('Auto-loading award data for ID:', awardId);
                loadAwardData(awardId);
            } else {
                // Clear supplier and items when no award selected
                console.log('No award selected, clearing supplier and items');
                const supplierSelect = $('#supplier');
                supplierSelect.empty().append('<option value="">Select supplier</option>');
                supplierSelect.prop('disabled', false).removeClass('readonly-input');
                $('#award-supplier-input').remove();
                $('#items-container').empty();
                hideSubsequentSections();
            }
        });
        
        // Contract Data Loading - Button Click (Manual)
        $('#loadContractData').on('click', function() {
            const contractId = $('#contract_id').val();
            if (!contractId) {
                alert('Please select a contract first.');
                return;
            }
            loadContractData(contractId);
        });
        
        // Contract Data Loading - Automatic on Dropdown Change
        $('#contract_id').on('change', function() {
            const contractId = $(this).val();
            console.log('Contract dropdown changed, contract ID:', contractId);
            
            if (contractId) {
                console.log('Auto-loading contract data for ID:', contractId);
                loadContractData(contractId);
            } else {
                // Clear supplier and items when no contract selected
                console.log('No contract selected, clearing supplier and items');
                const supplierSelect = $('#supplier');
                supplierSelect.empty().append('<option value="">Select supplier</option>');
                supplierSelect.prop('disabled', false).removeClass('readonly-input');
                $('#contract-supplier-input').remove();
                $('#items-container').empty();
                hideSubsequentSections();
            }
        });
        
        // Enhanced Direct Procurement - Step-by-step workflow
        
        // Step 1: Load Plan Categories
        $('#loadPlanCategories').on('click', function() {
            const planId = $('#plan_id').val();
            if (!planId) {
                alert('Please select a procurement plan first.');
                return;
            }
            loadPlanCategories(planId);
        });
        
        // Step 2: Load Prequalified Suppliers
        $('#loadPrequalifiedSuppliers').on('click', function() {
            const categoryId = $('#item_category_id').val();
            if (!categoryId) {
                alert('Please select an item category first.');
                return;
            }
            loadPrequalifiedSuppliers(categoryId);
        });
        
        // Step 3: Load Category Items
        $('#loadCategoryItems').on('click', function() {
            const planId = $('#plan_id').val();
            const categoryId = $('#item_category_id').val();
            loadCategoryItems(planId, categoryId);
        });
        
        // Plan Item Search
        $('#planItemSearch').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('#planItemsTableBody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.includes(searchTerm));
            });
        });
        
        // Plan Item Selection Handler
        $(document).on('click', '.selectPlanItem', function() {
            const row = $(this).closest('tr');
            const itemData = {
                id: row.data('item-id'),
                plan_ref: row.data('plan-ref'),
                item_name: row.find('.item-name').text(),
                item_description: row.find('.item-description').text(),
                unit_cost: parseFloat(row.find('.unit-cost').text().replace(/[^\d.-]/g, '')),
                unit_of_measure: row.data('unit-measure')
            };
            const quantity = row.find('.po-quantity').val();
            
            if (!quantity || quantity <= 0) {
                alert('Please enter a valid PO quantity');
                return;
            }
            
            addPlanItemToSelection(itemData, quantity);
            row.find('.po-quantity').val('');
            row.find('.po-quantity').prop('disabled', true);
            $(this).prop('disabled', true).text('Added');
        });
        
        // Finalize Plan Selection
        $('#finalizePlanSelection').on('click', function() {
            finalizePlanItemSelection();
        });
        
        // Add item button
        $('#add-item-btn').on('click', addNewItemRow);
        
        // Supplier address population
        $(document).on('change', '#supplier', function() {
            const address = $(this).find('option:selected').data('address') || '';
            $('input[name="address"]').val(address);
        });
        
        // Dynamic calculations
        $(document).on('input', '.quantity, .unit-price, .tax, .discount', calculateLineTotal);
        $(document).on('input', '.quantity, .unit-price, .tax, .discount', calculateSummaryTotals);
    }

    // =====================================================
    // DATA LOADING FUNCTIONS
    // =====================================================
    
    function loadRFQData(rfqId) {
        showLoading('Loading RFQ data...');
        
                    $.ajax({
            url: `/procurement/purchaseOrder/fetchRFQDetails/${rfqId}`,
                        type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayRFQInfo(response);
                    populateRFQSuppliers(rfqId);
                    showPODetailsSection();
                    showManualItemsSection(); // RFQ uses manual item entry
                } else {
                    alert('Failed to load RFQ data: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('RFQ loading error:', xhr);
                alert('Error loading RFQ data. Please try again.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function loadAwardData(awardId) {
        showLoading('Loading award data...');
        
        $.ajax({
            url: `{{ url('/procurement/purchaseOrder/award-details') }}/${awardId}`,
            type: 'GET',
            success: function(response) {
                console.log('Award data response received:', response);
                
                if (response.success) {
                    console.log('Award supplier data:', response.supplier);
                    console.log('Award items data:', response.items);
                    
                    displayAwardInfo(response);
                    populateSupplierFromAward(response.supplier);
                    populateItemsFromAward(response.items);
                    showPODetailsSection();
                    showPreloadedItemsSection();
                } else {
                    console.error('Award data loading failed:', response.message);
                    alert('Failed to load award data: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Award loading error:', xhr);
                alert('Error loading award data. Please try again.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function loadContractData(contractId) {
        showLoading('Loading contract data...');
        
        $.ajax({
            url: `{{ url('/procurement/purchaseOrder/contract-details') }}/${contractId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayContractInfo(response);
                    populateSupplierFromContract(response.supplier);
                    populateItemsFromContract(response.items);
                    populateContractTerms(response.contract);
                    showPODetailsSection();
                    showPreloadedItemsSection();
                } else {
                    alert('Failed to load contract data: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Contract loading error:', xhr);
                alert('Error loading contract data. Please try again.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function loadPlanItemData(planItemId) {
        showLoading('Loading plan item data...');
        
        $.ajax({
            url: `{{ url('/procurement/purchaseOrder/plan-item-details') }}/${planItemId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayPlanItemInfo(response);
                    populateItemFromPlan(response.item);
                    showPODetailsSection();
                    showSupplierSelectionForDirect(); // Show supplier dropdown for manual selection
                    showPreloadedItemsSection();
                } else {
                    alert('Failed to load plan item data: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Plan item loading error:', xhr);
                alert('Error loading plan item data. Please try again.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    // =====================================================
    // ENHANCED DIRECT PROCUREMENT - STEP BY STEP WORKFLOW
    // =====================================================
    
    let selectedPlanItems = [];
    let currentPlanId = null;
    let currentCategoryId = null;
    let prequalifiedSuppliers = [];
    
    function loadPlanCategories(planId) {
        showLoading('Loading item categories for selected plan...');
        currentPlanId = planId;
        
        const planText = $('#plan_id option:selected').text();
        $('#selected-plan-info').text(planText);
        
                    $.ajax({
            url: `{{ url('/procurement/purchaseOrder/plan') }}/${planId}/categories`,
                        type: 'GET',
            success: function(response) {
                if (response.success) {
                    populatePlanCategories(response.categories);
                    $('#category-selection-step').show();
                } else {
                    alert('Failed to load item categories: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Plan categories loading error:', xhr);
                alert('Error loading item categories. Please try again.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function populatePlanCategories(categories) {
        const select = $('#item_category_id');
        select.empty().append('<option value="">Choose Item Category</option>');
        
        if (categories.length === 0) {
            select.append('<option value="" disabled>No item categories found in this plan</option>');
            return;
        }
        
        categories.forEach(function(category) {
            select.append(`<option value="${category.id}">${category.name}${category.description ? ' - ' + category.description : ''}</option>`);
        });
    }
    
    function loadPrequalifiedSuppliers(categoryId) {
        showLoading('Loading prequalified suppliers for selected category...');
        currentCategoryId = categoryId;
        
        const categoryText = $('#item_category_id option:selected').text();
        $('#selected-category-info').text(categoryText);
        
        $.ajax({
            url: `{{ url('/procurement/purchaseOrder/category') }}/${categoryId}/suppliers`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    prequalifiedSuppliers = response.suppliers;
                    displayPrequalifiedSuppliers(response.suppliers);
                    $('#suppliers-display-step').show();
                } else {
                    alert('Failed to load prequalified suppliers: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Prequalified suppliers loading error:', xhr);
                alert('Error loading prequalified suppliers. Please try again.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function displayPrequalifiedSuppliers(suppliers) {
        const container = $('#prequalified-suppliers-list');
        container.empty();
        
        if (suppliers.length === 0) {
            container.html(`
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No prequalified suppliers found for this item category. You may need to manually select suppliers.
                </div>
            `);
            return;
        }
        
        const suppliersHtml = suppliers.map(supplier => `
            <div class="border rounded p-2 mb-2 bg-light">
                <strong>${supplier.name}</strong>
                <br><small class="text-muted">Category: ${supplier.supplier_category}</small>
                ${supplier.address ? `<br><small>${supplier.address}</small>` : ''}
            </div>
        `).join('');
        
        container.html(`
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <strong>${suppliers.length} prequalified supplier(s)</strong> found for this item category.
            </div>
            <div class="row">
                ${suppliers.map((supplier, index) => `
                    <div class="col-md-6 mb-2">
                        <div class="card card-body">
                            <h6 class="card-title mb-1">${supplier.name}</h6>
                            <small class="text-muted">Category: ${supplier.supplier_category}</small>
                            ${supplier.address ? `<small class="d-block">${supplier.address}</small>` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        `);
    }
    
    function loadCategoryItems(planId, categoryId) {
        showLoading('Loading items for selected plan and category...');
        
        $.ajax({
            url: `{{ url('/procurement/purchaseOrder/plan') }}/${planId}/category/${categoryId}/items`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    populateFilteredPlanItems(response.items);
                    populatePrequalifiedSuppliersDropdown();
                    $('#items-selection-step').show();
                    $('#planItemSearch').focus();
                } else {
                    alert('Failed to load items: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Category items loading error:', xhr);
                alert('Error loading items. Please try again.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    function populateFilteredPlanItems(items) {
        const tbody = $('#planItemsTableBody');
        tbody.empty();
        
        if (items.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> No items found for this plan and category combination
                    </td>
                </tr>
            `);
            return;
        }
        
        items.forEach(function(item) {
            const row = `
                <tr data-item-id="${item.id}" data-plan-ref="${item.plan_ref}" data-unit-measure="${item.unit_of_measure}">
                    <td class="item-name">${item.item_name}</td>
                    <td class="item-description" title="${item.item_description}">
                        ${item.item_description && item.item_description.length > 50 ? item.item_description.substring(0, 50) + '...' : (item.item_description || '')}
                    </td>
                    <td class="text-center">${item.planned_quantity} ${item.unit_of_measure}</td>
                    <td class="text-end unit-cost">KES ${parseFloat(item.unit_cost || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm po-quantity" 
                               min="1" max="${item.planned_quantity}" 
                               placeholder="Qty" style="width: 100px;">
                    </td>
                    <td class="small">${item.delivery_date}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary selectPlanItem">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function populatePrequalifiedSuppliersDropdown() {
        const supplierSelect = $('#supplier');
        supplierSelect.empty().append('<option value="">Select Prequalified Supplier</option>');
        
        prequalifiedSuppliers.forEach(function(supplier) {
            supplierSelect.append(`<option value="${supplier.id}" data-address="${supplier.address}">${supplier.name}</option>`);
        });
        
        // Enable the supplier dropdown
        supplierSelect.prop('disabled', false).removeClass('readonly-input');
    }
    
    function addPlanItemToSelection(itemData, quantity) {
        const lineTotal = itemData.unit_cost * quantity;
        
        // Add to selected items array
        selectedPlanItems.push({
            ...itemData,
            po_quantity: quantity,
            line_total: lineTotal
        });
        
        // Add to selected items summary table
        const summaryRow = `
            <tr data-selected-id="${itemData.id}">
                <td>${itemData.item_name}</td>
                <td>${quantity} ${itemData.unit_of_measure}</td>
                <td>KES ${itemData.unit_cost.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="fw-bold">KES ${lineTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger removePlanItem" data-item-id="${itemData.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#selectedPlanItemsBody').append(summaryRow);
        $('#selectedPlanItemsSummary').show();
        $('#finalizePlanSelection').show();
        
        updatePlanSelectionSummary();
    }
    
    function updatePlanSelectionSummary() {
        const totalAmount = selectedPlanItems.reduce((sum, item) => sum + item.line_total, 0);
        const itemCount = selectedPlanItems.length;
        
        $('#selectedPlanItemsSummary h6').html(`
            <i class="fas fa-list"></i> Selected Items for PO 
            <span class="badge bg-primary">${itemCount} items</span>
            <span class="badge bg-success">Total: KES ${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</span>
        `);
    }
    
    // Handle removal of selected plan items
    $(document).on('click', '.removePlanItem', function() {
        const itemId = $(this).data('item-id');
        
        // Remove from selected items array
        selectedPlanItems = selectedPlanItems.filter(item => item.id !== itemId);
        
        // Remove from summary table
        $(this).closest('tr').remove();
        
        // Re-enable the item in the grid
        const gridRow = $(`#planItemsTableBody tr[data-item-id="${itemId}"]`);
        gridRow.find('.po-quantity').prop('disabled', false).val('');
        gridRow.find('.selectPlanItem').prop('disabled', false).html('<i class="fas fa-plus"></i>');
        
        // Update summary
        updatePlanSelectionSummary();
        
        // Hide summary if no items selected
        if (selectedPlanItems.length === 0) {
            $('#selectedPlanItemsSummary').hide();
            $('#finalizePlanSelection').hide();
        }
    });
    
    function finalizePlanItemSelection() {
        if (selectedPlanItems.length === 0) {
            alert('Please select at least one item before finalizing.');
            return;
        }
        
        // Show source info with selected items summary
        const html = `
            <div class="row">
                <div class="col-md-12">
                    <h6><i class="fas fa-shopping-cart"></i> Direct Procurement Summary</h6>
                    <p><strong>Items Selected:</strong> ${selectedPlanItems.length} items from procurement plans</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Plan Ref</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${selectedPlanItems.map(item => `
                                    <tr>
                                        <td>${item.item_name}</td>
                                        <td>${item.plan_ref}</td>
                                        <td>${item.po_quantity} ${item.unit_of_measure}</td>
                                        <td>KES ${item.unit_cost.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                        <td>KES ${item.line_total.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        showSourceInfo(html);
        populateItemsFromPlanItems(selectedPlanItems);
        createHiddenFormInputsForDirectProcurement(); // Add hidden inputs for validation
        showPODetailsSection();
        showSupplierSelectionForDirect();
        showPreloadedItemsSection();
    }
    
    function createHiddenFormInputsForDirectProcurement() {
        // Remove any existing hidden inputs
        $('#direct-procurement-inputs').remove();
        
        // Create container for hidden inputs
        const hiddenInputsContainer = $('<div id="direct-procurement-inputs" style="display: none;"></div>');
        
        // Add plan_item_id for first selected item (for validation)
        hiddenInputsContainer.append(`<input type="hidden" name="plan_item_id" value="${selectedPlanItems[0]?.id || ''}">`);
        
        // Create arrays for validation
        selectedPlanItems.forEach((item, index) => {
            hiddenInputsContainer.append(`<input type="hidden" name="itemCode[${index}]" value="${item.item_id}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="quantity[${index}]" value="${item.po_quantity}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="unitPrice[${index}]" value="${item.unit_cost}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="tax[${index}]" value="0">`);
            hiddenInputsContainer.append(`<input type="hidden" name="discount[${index}]" value="0">`);
            hiddenInputsContainer.append(`<input type="hidden" name="lineTotal[${index}]" value="${item.line_total}">`);
        });
        
        // Append to form
        $('#unifiedPurchaseOrderForm').append(hiddenInputsContainer);
    }

    // =====================================================
    // DISPLAY FUNCTIONS FOR EACH TYPE
    // =====================================================
    
    function displayRFQInfo(data) {
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-file-contract"></i> RFQ Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>RFQ Number:</strong></td><td>${data.rfq?.RFQNumber || 'N/A'}</td></tr>
                        <tr><td><strong>Title:</strong></td><td>${data.rfq?.RFQTitle || 'N/A'}</td></tr>
                        <tr><td><strong>Status:</strong></td><td><span class="badge badge-success">${data.rfq?.Status || 'N/A'}</span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-info-circle"></i> Responses Available</h6>
                    <p class="mb-0">Total suppliers responded: <span class="badge badge-info">${data.suppliers_count || 0}</span></p>
                </div>
            </div>
        `;
        showSourceInfo(html);
    }
    
    function displayAwardInfo(data) {
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-trophy"></i> Award Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Tender No:</strong></td><td>${data.award?.tender_no || 'N/A'}</td></tr>
                        <tr><td><strong>Title:</strong></td><td>${data.award?.title || 'N/A'}</td></tr>
                        <tr><td><strong>Awarded Amount:</strong></td><td>KES ${parseFloat(data.award?.awarded_amount || 0).toLocaleString()}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-building"></i> Winning Supplier</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Supplier:</strong></td><td>${data.supplier?.name || 'N/A'}</td></tr>
                        <tr><td><strong>Address:</strong></td><td>${data.supplier?.address || 'N/A'}</td></tr>
                    </table>
                </div>
            </div>
        `;
        showSourceInfo(html);
    }
    
    function displayContractInfo(data) {
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-file-signature"></i> Contract Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Contract Ref:</strong></td><td>${data.contract?.contract_ref || 'N/A'}</td></tr>
                        <tr><td><strong>Tender No:</strong></td><td>${data.contract?.tender_no || 'N/A'}</td></tr>
                        <tr><td><strong>Title:</strong></td><td>${data.contract?.title || 'N/A'}</td></tr>
                        <tr><td><strong>Contract Value:</strong></td><td>KES ${parseFloat(data.contract?.contract_value || 0).toLocaleString()}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-building"></i> Contracted Supplier</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Supplier:</strong></td><td>${data.supplier?.name || 'N/A'}</td></tr>
                        <tr><td><strong>Address:</strong></td><td>${data.supplier?.address || 'N/A'}</td></tr>
                    </table>
                </div>
            </div>
        `;
        showSourceInfo(html);
    }
    
    function displayPlanItemInfo(data) {
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-shopping-cart"></i> Plan Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Plan Ref:</strong></td><td>${data.plan?.plan_ref || 'N/A'}</td></tr>
                        <tr><td><strong>Title:</strong></td><td>${data.plan?.title || 'N/A'}</td></tr>
                        <tr><td><strong>Branch:</strong></td><td>${data.plan?.branch || 'N/A'}</td></tr>
                        <tr><td><strong>Department:</strong></td><td>${data.plan?.department || 'N/A'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-box"></i> Item Details</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Item:</strong></td><td>${data.item?.item_name || 'N/A'}</td></tr>
                        <tr><td><strong>Quantity:</strong></td><td>${data.item?.quantity || 'N/A'} ${data.item?.unit_of_measure || ''}</td></tr>
                        <tr><td><strong>Est. Cost:</strong></td><td>KES ${parseFloat(data.item?.estimated_unit_cost || 0).toLocaleString()}</td></tr>
                    </table>
                </div>
            </div>
        `;
        showSourceInfo(html);
    }

    function showSourceInfo(html) {
        $('#source-info-content').html(html);
        $('#source-info-card').show();
    }

    // =====================================================
    // SECTION DISPLAY FUNCTIONS
    // =====================================================
    
    function showPODetailsSection() {
        $('#po-details-card').show();
    }
    
    function showManualItemsSection() {
        $('#items-section-card').show();
        $('#manual-items-section').show();
        $('#totals-section-card').show();
        addInitialItemRow(); // Add first empty row
    }
    
    function showPreloadedItemsSection() {
        $('#items-section-card').show();
        $('#totals-section-card').show();
    }
    
    function showSupplierSelectionForDirect() {
        console.log('showSupplierSelectionForDirect() called');
        
        // Enable supplier dropdown and populate with all suppliers
        const supplierSelect = $('#supplier');
        console.log('Supplier select element found:', supplierSelect.length > 0);
        
        supplierSelect.prop('disabled', false).removeClass('readonly-input');
        
        // Clear and repopulate
        supplierSelect.empty().append('<option value="">Select Supplier</option>');
        
        let suppliersAdded = 0;
        @foreach($suppliers as $supplier)
            supplierSelect.append(`<option value="{{ $supplier->Id }}" data-address="{{ $supplier->Address ?? '' }}">{{ $supplier->TradingName ?? $supplier->ThirdPartyName }}</option>`);
            suppliersAdded++;
        @endforeach
        
        console.log('Suppliers added to dropdown:', suppliersAdded);
        console.log('Supplier dropdown options count:', supplierSelect.find('option').length);
        
        // Ensure the dropdown is visible
        supplierSelect.closest('.col-md-8').show();
        
        // Test if at least one supplier was added
        if (suppliersAdded === 0) {
            console.warn('WARNING: No suppliers were added to the dropdown! Check if $suppliers variable is populated in the controller.');
        }
    }
    
    // DEBUG FUNCTION - call testSupplierDropdown() in console to debug
    window.testSupplierDropdown = function() {
        console.log('=== SUPPLIER DROPDOWN DEBUG TEST ===');
        console.log('Current origination type:', currentOriginationType);
        console.log('Supplier field exists:', $('#supplier').length > 0);
        console.log('Supplier field value:', $('#supplier').val());
        console.log('Supplier field disabled:', $('#supplier').prop('disabled'));
        console.log('Supplier options count:', $('#supplier option').length);
        console.log('Hidden supplier inputs count:', $('input[name="supplier"]:hidden').length);
        console.log('Hidden supplier values:');
        $('input[name="supplier"]:hidden').each(function(index) {
            console.log(`  ${index}: value="${$(this).val()}", id="${$(this).attr('id')}"`);
        });
        console.log('Supplier options:');
        $('#supplier option').each(function(index) {
            console.log(`  ${index}: value="${$(this).val()}", text="${$(this).text()}"`);
        });
        console.log('Awards/Contracts loaded?');
        console.log('  Award ID selected:', $('#award_id').val());
        console.log('  Contract ID selected:', $('#contract_id').val());
        console.log('Calling showSupplierSelectionForDirect()...');
        showSupplierSelectionForDirect();
        console.log('After calling function - options count:', $('#supplier option').length);
        console.log('====================================');
    }

    // =====================================================
    // SUPPLIER AND ITEMS POPULATION
    // =====================================================
    
    function populateRFQSuppliers(rfqId) {
        // Filter RFQ responses for this RFQ
        const suppliers = rfqResponses.filter(r => r.RFQNumber === rfqId);
        const uniqueSuppliers = [];
        const seen = new Set();
        
        suppliers.forEach(s => {
            const key = s.SupplierName + (s.SupplierId || s.SupplierID || '');
            if (!seen.has(key)) {
                uniqueSuppliers.push(s);
                seen.add(key);
            }
        });
        
        // Populate supplier dropdown
        const supplierSelect = $('#supplier');
        supplierSelect.empty().append('<option value="">Select Supplier</option>');
        
        uniqueSuppliers.forEach(s => {
            supplierSelect.append(`<option value="${s.SupplierId || s.Id || ''}" data-address="${s.Address || ''}">${s.SupplierName || s.Name || ''}</option>`);
        });
    }
    
    function populateSupplierFromAward(supplier) {
        console.log('populateSupplierFromAward called with:', supplier);
        
        const supplierSelect = $('#supplier');
        supplierSelect.empty();
        supplierSelect.append(`<option value="${supplier.id}" selected>${supplier.name}</option>`);
        supplierSelect.prop('disabled', true).addClass('readonly-input');
        
        // Create hidden input since disabled fields don't get submitted
        $('#award-supplier-input').remove(); // Remove existing if any
        const hiddenSupplierInput = `<input type="hidden" id="award-supplier-input" name="supplier" value="${supplier.id}">`;
        $('#unifiedPurchaseOrderForm').append(hiddenSupplierInput);
        
        $('input[name="address"]').val(supplier.address || '');
        
        console.log('Supplier populated from award - ID:', supplier.id, 'Name:', supplier.name);
        console.log('Hidden supplier input created with value:', supplier.id);
    }
    
    function populateSupplierFromContract(supplier) {
        console.log('populateSupplierFromContract called with:', supplier);
        
        const supplierSelect = $('#supplier');
        supplierSelect.empty();
        supplierSelect.append(`<option value="${supplier.id}" selected>${supplier.name}</option>`);
        supplierSelect.prop('disabled', true).addClass('readonly-input');
        
        // Create hidden input since disabled fields don't get submitted
        $('#contract-supplier-input').remove(); // Remove existing if any
        const hiddenSupplierInput = `<input type="hidden" id="contract-supplier-input" name="supplier" value="${supplier.id}">`;
        $('#unifiedPurchaseOrderForm').append(hiddenSupplierInput);
        
        $('input[name="address"]').val(supplier.address || '');
        
        console.log('Supplier populated from contract - ID:', supplier.id, 'Name:', supplier.name);
        console.log('Hidden supplier input created with value:', supplier.id);
    }
    
    function populateItemsFromAward(items) {
        const container = $('#items-container');
        container.empty();
        
        items.forEach((item, index) => {
            const itemHtml = createPreloadedItemCard(item, index, 'award');
            container.append(itemHtml);
        });
        
        createHiddenFormInputsForAwards(items); // Add hidden inputs for validation
                calculateSummaryTotals();
    }
    
    function createHiddenFormInputsForAwards(items) {
        // Remove any existing hidden inputs
        $('#award-inputs').remove();
        
        // Create container for hidden inputs
        const hiddenInputsContainer = $('<div id="award-inputs" style="display: none;"></div>');
        
        // Create arrays for validation
        items.forEach((item, index) => {
            hiddenInputsContainer.append(`<input type="hidden" name="itemCode[${index}]" value="${item.item_id}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="quantity[${index}]" value="${item.quantity || 1}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="unitPrice[${index}]" value="${item.unit_price || 0}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="tax[${index}]" value="0">`);
            hiddenInputsContainer.append(`<input type="hidden" name="discount[${index}]" value="0">`);
            hiddenInputsContainer.append(`<input type="hidden" name="lineTotal[${index}]" value="${(item.quantity || 1) * (item.unit_price || 0)}">`);
        });
        
        // Append to form
        $('#unifiedPurchaseOrderForm').append(hiddenInputsContainer);
    }
    
    function populateItemsFromContract(items) {
        const container = $('#items-container');
        container.empty();
        
        items.forEach((item, index) => {
            const itemHtml = createPreloadedItemCard(item, index, 'contract');
            container.append(itemHtml);
        });
        
        createHiddenFormInputsForContracts(items); // Add hidden inputs for validation
        calculateSummaryTotals();
    }
    
    function createHiddenFormInputsForContracts(items) {
        // Remove any existing hidden inputs
        $('#contract-inputs').remove();
        
        // Create container for hidden inputs
        const hiddenInputsContainer = $('<div id="contract-inputs" style="display: none;"></div>');
        
        // Create arrays for validation
        items.forEach((item, index) => {
            hiddenInputsContainer.append(`<input type="hidden" name="itemCode[${index}]" value="${item.item_id}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="quantity[${index}]" value="${item.quantity || 1}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="unitPrice[${index}]" value="${item.unit_price || 0}">`);
            hiddenInputsContainer.append(`<input type="hidden" name="tax[${index}]" value="0">`);
            hiddenInputsContainer.append(`<input type="hidden" name="discount[${index}]" value="0">`);
            hiddenInputsContainer.append(`<input type="hidden" name="lineTotal[${index}]" value="${(item.quantity || 1) * (item.unit_price || 0)}">`);
        });
        
        // Append to form
        $('#unifiedPurchaseOrderForm').append(hiddenInputsContainer);
    }
    
    function populateItemsFromPlanItems(planItems) {
        const container = $('#items-container');
        container.empty();
        
        planItems.forEach((planItem, index) => {
            const item = {
                item_id: planItem.item_id,
                item_name: planItem.item_name,
                description: planItem.item_description,
                quantity: planItem.po_quantity,
                unit_price: planItem.unit_cost,
                item_type_id: null,
                unit_of_measure: planItem.unit_of_measure
            };
            const itemHtml = createPreloadedItemCard(item, index, 'plan');
            container.append(itemHtml);
        });
        
        calculateSummaryTotals();
    }
    
    function populateItemFromPlan(item) {
        const container = $('#items-container');
        container.empty();
        
        const itemHtml = createPreloadedItemCard(item, 0, 'plan');
        container.append(itemHtml);
        
        calculateSummaryTotals();
    }
    
    function populateContractTerms(contract) {
        if (contract.payment_terms) {
            // Try to match payment terms or add as note
            $('textarea[name="notes"]').val(`Contract Payment Terms: ${contract.payment_terms}`);
        }
        if (contract.delivery_terms) {
            $('input[name="delivery_terms"]').val(contract.delivery_terms);
        }
    }

    // =====================================================
    // ITEM MANAGEMENT FUNCTIONS
    // =====================================================
    
    function createPreloadedItemCard(item, index, source) {
        return `
            <div class="item-row" data-index="${index}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label>Item Name</label>
                        <input type="text" class="form-control readonly-input" value="${item.item_name || item.description || ''}" readonly>
                        <input type="hidden" name="itemCode[]" value="${item.item_id || ''}">
                    </div>
                    <div class="col-md-3">
                        <label>Description</label>
                        <textarea class="form-control readonly-input" name="itemDescription[]" rows="2" readonly>${item.description || ''}</textarea>
                    </div>
                    <div class="col-md-2">
                        <label>Quantity</label>
                        <input type="number" class="form-control quantity" name="quantity[]" value="${item.quantity || ''}" step="any" required>
                    </div>
                    <div class="col-md-2">
                        <label>Unit Price</label>
                        <input type="number" class="form-control unit-price" name="unitPrice[]" value="${item.unit_price || item.estimated_unit_cost || ''}" step="any" required>
                    </div>
                    <div class="col-md-1">
                        <label>Tax %</label>
                        <input type="number" class="form-control tax" name="tax[]" value="0" step="any">
                    </div>
                    <div class="col-md-1">
                        <label>Line Total</label>
                        <input type="number" class="form-control line-total" name="lineTotal[]" readonly>
                    </div>
                </div>
            </div>
        `;
    }
    
    function addInitialItemRow() {
        const tbody = $('#item-rows');
        tbody.append(createManualItemRow(1));
        itemRowCounter = 1;
    }
    
    function addNewItemRow() {
        if (currentOriginationType === 'rfq' || currentOriginationType === 'direct') {
            itemRowCounter++;
            const tbody = $('#item-rows');
            tbody.append(createManualItemRow(itemRowCounter));
        } else {
            // For award/contract, items are preloaded
            alert('Items are automatically loaded from the selected source.');
        }
    }
    
    function createManualItemRow(rowNumber) {
        return `
            <tr>
                <td class="line-no">${rowNumber}.</td>
                <td>
                    <select class="form-select form-select-sm type" name="type[]" required>
                        <option disabled selected>Select Type</option>
                        ${itemTypeOptions}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm itemCode" name="itemCode[]" required>
                        <option disabled selected>Select Item Code</option>
                    </select>
                </td>
                <td>
                    <textarea class="form-control form-control-sm itemDescription" name="itemDescription[]" cols="30" rows="3" readonly></textarea>
                </td>
                <td><input type="number" class="form-control form-control-sm quantity" name="quantity[]" step="any" required></td>
                <td><input type="number" class="form-control form-control-sm unit-price" name="unitPrice[]" step="any" required></td>
                <td><input type="number" class="form-control form-control-sm tax" name="tax[]" step="any" value="0"></td>
                <td><input type="number" class="form-control form-control-sm discount" name="discount[]" step="any" value="0"></td>
                <td><input type="number" class="form-control form-control-sm line-total" name="lineTotal[]" step="any" readonly></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Item">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    // =====================================================
    // CALCULATION FUNCTIONS
    // =====================================================
    
    function calculateLineTotal() {
        const row = $(this).closest('tr, .item-row');
        const quantity = parseFloat(row.find('.quantity').val()) || 0;
        const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
        const tax = parseFloat(row.find('.tax').val()) || 0;
        const discount = parseFloat(row.find('.discount').val()) || 0;
        
        let lineTotal = quantity * unitPrice;
        
        // Apply discount
        if (discount > 0) {
            lineTotal -= lineTotal * (discount / 100);
        }
        
        // Apply tax
        if (tax > 0) {
            lineTotal += lineTotal * (tax / 100);
        }
        
        row.find('.line-total').val(lineTotal.toFixed(2));
    }

            function calculateSummaryTotals() {
                let exclusiveTotal = 0;
                let totalTax = 0;

        // Calculate for table rows (manual entry)
        $('#item-rows tr').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
            const tax = parseFloat($(this).find('.tax').val()) || 0;
            const discount = parseFloat($(this).find('.discount').val()) || 0;
            
            if (quantity > 0 && unitPrice > 0) {
                let lineTotalBeforeTax = quantity * unitPrice;

                    // Apply discount
                    if (discount > 0) {
                        lineTotalBeforeTax -= lineTotalBeforeTax * (discount / 100);
                    }

                    // Calculate tax for this line
                    let lineTax = lineTotalBeforeTax * (tax / 100);

                    // Add to totals
                    exclusiveTotal += lineTotalBeforeTax;
                    totalTax += lineTax;
            }
        });
        
        // Calculate for preloaded item cards
        $('.item-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
            const tax = parseFloat($(this).find('.tax').val()) || 0;
            
            if (quantity > 0 && unitPrice > 0) {
                let lineTotalBeforeTax = quantity * unitPrice;
                let lineTax = lineTotalBeforeTax * (tax / 100);
                
                exclusiveTotal += lineTotalBeforeTax;
                totalTax += lineTax;
                
                // Update line total display
                $(this).find('.line-total').val((lineTotalBeforeTax + lineTax).toFixed(2));
            }
                });

                // Calculate inclusive total
        const inclusiveTotal = exclusiveTotal + totalTax;

                // Update the summary fields
        $('.exclusiveTotal').val(exclusiveTotal.toFixed(2));
        $('.taxAmount').val(totalTax.toFixed(2));
        $('.inclusiveTotal').val(inclusiveTotal.toFixed(2));
    }

    // =====================================================
    // UTILITY FUNCTIONS
    // =====================================================
    
    function showLoading(message) {
        // You can implement a loading spinner here
        console.log(message);
    }
    
    function hideLoading() {
        // Hide loading spinner
        console.log('Loading complete');
    }

    // =====================================================
    // EXISTING DYNAMIC ITEM HANDLERS (for RFQ/Direct)
    // =====================================================
    
    // Handle item type change
    $(document).on('change', '.type', function() {
        let row = $(this).closest('tr');
        let type = $(this).val();
        
        if (type !== '') {
            $.ajax({
                url: `/procurement/requisitionItem/getItem/${type}`,
                type: 'GET',
                success: function(response) {
                    let itemCodeSelect = row.find('.itemCode');
                    itemCodeSelect.empty().append('<option value="">Select Item</option>');
                    
                    $.each(response.data, function(key, item) {
                        itemCodeSelect.append(`<option value="${item.Id}">${item.ItemName}</option>`);
                    });
                },
                error: function(xhr) {
                    console.error('Error fetching items:', xhr);
                }
                });
            }
        });

    // Handle item selection
    $(document).on('change', '.itemCode', function() {
        let itemId = $(this).val();
        let row = $(this).closest('tr');
        
        if (itemId !== '') {
            $.ajax({
                url: `/procurement/requisitionItem/getItemDetails/${itemId}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        row.find('.itemDescription').val(response.data.ItemDescription || '');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching item details:', xhr);
                }
            });
        }
        });

        // Remove row handler
    $(document).on('click', '.remove-row', function() {
        if ($('#item-rows tr').length > 1) {
                $(this).closest('tr').remove();
                updateLineNumbers();
                calculateSummaryTotals();
            } else {
            // Clear the row instead of removing if only one left
                let row = $(this).closest('tr');
                row.find('select, input, textarea').val('');
                calculateSummaryTotals();
            }
        });
    
    function updateLineNumbers() {
        $('#item-rows tr').each(function(index) {
            $(this).find('.line-no').text((index + 1) + '.');
        });
    }
    
    // Additional form submission handler for button clicks
    $('#saveOrder').on('click', function(e) {
        e.preventDefault();
        
        console.log('Save Order button clicked');
        console.log('Current origination type:', currentOriginationType);
        
        if (!currentOriginationType) {
            alert('Please select an origination type first.');
            return;
        }
        
        // For direct procurement, ensure plan items are selected
        if (currentOriginationType === 'direct') {
            console.log('Direct procurement - checking plan items:', selectedPlanItems.length);
            if (selectedPlanItems.length === 0) {
                alert('Please select at least one plan item for direct procurement.');
                return;
            }
        }
        
        // Trigger the main form submission which will use AJAX
        console.log('Triggering form submit...');
        $('#unifiedPurchaseOrderForm').trigger('submit');
    });
    
    function validateForm() {
        // Basic form validation
        
        // Check if origination type is selected
        if (!currentOriginationType) {
            alert('Please select an origination type.');
            return false;
        }
        
        // Validate delivery terms if required
        const deliveryTerms = $('#delivery_terms').val();
        if (!deliveryTerms || deliveryTerms.trim() === '') {
            // Optional but warn user
            if (!confirm('Delivery terms are not specified. Continue anyway?')) {
                return false;
            }
        }
        
        // Check address if required
        const address = $('#address').val();
        if (!address || address.trim() === '') {
            // Optional but warn user
            if (!confirm('Delivery address is not specified. Continue anyway?')) {
                return false;
            }
        }
        
        return true;
    }

    function validateFormBasedOnType() {
        console.log('Validating form based on type:', currentOriginationType);
        
        switch(currentOriginationType) {
            case 'rfq':
                if (!$('#rfq_id').val()) {
                    alert('Please select an RFQ.');
                    return false;
                }
                break;
            case 'award':
                if (!$('#award_id').val()) {
                    alert('Please select an award.');
                    return false;
                }
                break;
            case 'contract':
                if (!$('#contract_id').val()) {
                    alert('Please select a contract.');
                    return false;
                }
                break;
            case 'direct':
                console.log('Validating direct procurement...');
                console.log('Selected plan items count:', selectedPlanItems.length);
                console.log('Selected plan items:', selectedPlanItems);
                console.log('Supplier value:', $('#supplier').val());
                console.log('Supplier options count:', $('#supplier option').length);
                
                if (selectedPlanItems.length === 0) {
                    alert('Please select at least one plan item for direct procurement.');
                    return false;
                }
                
                // Ensure supplier dropdown is properly initialized
                if ($('#supplier option').length <= 1) { // Only has "Select Supplier" option
                    console.log('Supplier dropdown not initialized, calling showSupplierSelectionForDirect()...');
                    showSupplierSelectionForDirect();
                }
                
                // Ensure hidden inputs are created
                if ($('#direct-procurement-inputs').length === 0) {
                    console.log('Creating hidden inputs for direct procurement...');
                    createHiddenFormInputsForDirectProcurement();
                }
                
                if (!$('#supplier').val()) {
                    alert('Please select a supplier for direct procurement.');
                    return false;
                }
                break;
        }
        
        // Validate supplier and items
        if (!$('#supplier').val()) {
            alert('Please select a supplier.');
            return false;
        }
        
        // Validate at least one item
        const hasItems = $('#item-rows tr').length > 0 || $('.item-row').length > 0;
        if (!hasItems) {
            alert('Please add at least one item.');
            return false;
        }
        
        return true;
    }
</script>

@endsection
