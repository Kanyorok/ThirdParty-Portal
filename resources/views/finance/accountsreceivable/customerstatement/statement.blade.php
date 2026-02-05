@extends('layouts.app')
@section('title', 'Customer Statement')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<style>
    .select2-container {
        width: 100% !important;
    }
    
    :root {
        --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
    }

    body, .card, .table {
        font-family: var(--font-sans);
    }

    .card {
        transition: transform 0.2s ease-in-out;
    }

    .table-hover tbody tr {
        transition: background-color 0.2s ease-in-out;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-weight: 500;
        padding: 0.35rem 0.65rem;
    }

    /* Print Styles */
    @media print {
        /* Hide non-essential elements */
        .btn, nav, header, footer, #customerSelectSection, #loadingIndicator,
        .d-flex.justify-content-between.align-items-center.mb-4,
        #backToSearchBtn, .breadcrumb, .page-header {
            display: none !important;
        }
        
        /* Reset page styling */
        body {
            background: white !important;
            margin: 0;
            padding: 20px;
            font-size: 11pt;
            color: #000 !important;
        }
        
        .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Card styling for print */
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            page-break-inside: avoid;
            margin-bottom: 10px !important;
            background: white !important;
        }
        
        .card-body {
            padding: 5px !important;
            background: white !important;
        }
        
        .card-header {
            background: #f8f9fa !important;
            border-bottom: 2px solid #333 !important;
            padding: 8px 5px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Table styling */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            page-break-inside: auto;
            font-size: 9pt;
            margin: 0 !important;
        }
        
        .table-responsive {
            padding: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
        }
        
        thead {
            display: table-header-group;
            background: #f0f0f0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        th, td {
            border: 1px solid #ddd !important;
            padding: 4px 6px !important;
            text-align: left;
            font-size: 9pt;
        }
        
        th {
            font-weight: bold !important;
            background: #f0f0f0 !important;
            font-size: 9pt;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Specific padding adjustments for better fit */
        .px-4 {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }
        
        .pe-4 {
            padding-right: 6px !important;
        }
        
        /* Preserve colors for balance indicators */
        .text-end {
            text-align: right !important;
        }
        
        /* Ensure text colors are visible */
        .text-muted {
            color: #666 !important;
        }
        
        /* Badge styling */
        .badge {
            border: 1px solid #333 !important;
            padding: 3px 8px !important;
            background: #f0f0f0 !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Customer info section */
        .rounded-circle {
            border: 2px solid #333 !important;
            background: #f0f0f0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Headers */
        h4, h5 {
            color: #000 !important;
            margin-top: 10px !important;
            margin-bottom: 10px !important;
        }
        
        /* Force page breaks before new sections */
        #supplierStatementSection {
            page-break-before: auto;
        }
        
        /* Ensure icons are visible or hidden appropriately */
        .fas, .fa {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Footer */
        .text-center.mt-4 {
            margin-top: 20px !important;
            border-top: 1px solid #ddd;
            padding-top: 10px !important;
            font-size: 9pt;
        }
        
        /* Ensure all content is visible */
        * {
            overflow: visible !important;
        }
        
        /* Remove shadows and gradients */
        .shadow-sm, .shadow {
            box-shadow: none !important;
        }
        
        /* Print page settings */
        @page {
            size: A4;
            margin: 1cm;
        }
    }


    #statementContent {
        display: none;
    }

    #statementContent.show {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="container-fluid my-4">
    {{-- Customer Selection Section --}}
    <div id="customerSelectSection">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.05), rgba(109, 63, 154, 0.05));">
                <h4 class="mb-3 fw-bold">
                    <i class="fas fa-file-invoice me-2" style="color: #5a74e7;"></i>Generate Customer Statement
                </h4>
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Select Customer</label>
                        <select id="customerSelect" class="form-select form-select-lg" style="width: 100%;">
                            <option disabled selected value="">-- Search and select a customer --</option>
                        </select>
                        <small class="text-muted">Type at least 2 characters of a name, ID number, email, or phone</small>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button id="loadStatementBtn" class="btn btn-lg w-100 shadow-sm" 
                                style="background: linear-gradient(to right, #5a74e7, #6d3f9a); color: white; border: none;" 
                                disabled>
                            <i class="fas fa-search me-2"></i>Load Statement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Indicator --}}
    <div id="loadingIndicator" style="display: none;">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading customer statement...</p>
        </div>
    </div>

    {{-- Statement Content --}}
    <div id="statementContent">
        {{-- Header with Back Button --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button id="backToSearchBtn" class="btn btn-sm shadow-sm mb-2" style="background: rgba(90, 116, 231, 0.1); color: #5a74e7; border: 1px solid rgba(90, 116, 231, 0.3);">
                    <i class="fas fa-arrow-left me-1"></i>Back to Search
                </button>
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-file-invoice me-2" style="color: #5a74e7;"></i>Customer Statement
                </h4>
            </div>
            <div>
                <button class="btn shadow-sm" onclick="window.print()" style="background: linear-gradient(to right, #5a74e7, #6d3f9a); color: white; border: none;">
                    <i class="fas fa-print me-2"></i>Print Statement
                </button>
            </div>
        </div>

        {{-- Customer Information Card --}}
        <div class="card border-0 shadow-sm mb-4" id="customerInfoCard">
            <div class="card-body" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.05), rgba(109, 63, 154, 0.05));">
                <div class="row">
                    <div class="col-md-8">
                        <div class="d-flex align-items-start mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 60px; height: 60px; background: linear-gradient(135deg, #5a74e7, #6d3f9a); color: white; font-size: 24px; font-weight: bold;" 
                                 id="customerInitial">
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold" id="customerName"></h4>
                                <div class="d-flex gap-2 mb-2" id="customerTypes"></div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-id-card me-2" style="color: #5a74e7;"></i>
                                    <div>
                                        <small class="text-muted d-block">ID Number</small>
                                        <strong id="customerIdNumber"></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope me-2" style="color: #6d3f9a;"></i>
                                    <div>
                                        <small class="text-muted d-block">Email</small>
                                        <strong id="customerEmail"></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-phone me-2" style="color: #28a745;"></i>
                                    <div>
                                        <small class="text-muted d-block">Phone</small>
                                        <strong id="customerPhone"></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt me-2" style="color: #ffc107;"></i>
                                    <div>
                                        <small class="text-muted d-block">Address</small>
                                        <strong id="customerAddress"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-end">
                            <small class="text-muted d-block mb-1">Statement Date</small>
                            <h5 class="mb-3" style="color: #5a74e7;" id="statementDate"></h5>
                            <img src="{{ asset('assets/images/logo-dark.png') }}" alt="Company Logo" class="img-fluid" style="max-height: 60px;" onerror="this.style.display='none'">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tenant Statement Section --}}
        <div id="tenantStatementSection" style="display: none;">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-3" style="background: linear-gradient(to right, rgba(90, 116, 231, 0.1), rgba(90, 116, 231, 0.05));">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-home me-2" style="color: #5a74e7;"></i>Tenant Account Statement
                            <span class="badge ms-2" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="fas fa-desktop me-1"></i>ERP System
                            </span>
                        </h5>
                        <h5 class="mb-0" id="tenantBalance"></h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: rgba(90, 116, 231, 0.05);">
                                <tr>
                                    <th class="px-4 py-3" style="width: 15%;">Reference</th>
                                    <th class="py-3" style="width: 12%;">Date</th>
                                    {{-- <th class="py-3">Description</th> --}}
                                    <th class="py-3" style="width: 20%;">Type</th>
                                    <th class="py-3 text-end" style="width: 15%;">Debit</th>
                                    <th class="py-3 text-end" style="width: 15%;">Credit</th>
                                    <th class="py-3 text-end pe-4" style="width: 18%;">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="tenantTransactionsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Supplier Statement Section --}}
        <div id="supplierStatementSection" style="display: none;">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-0 py-3" style="background: linear-gradient(to right, rgba(40, 167, 69, 0.1), rgba(40, 167, 69, 0.05));">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-truck me-2" style="color: #28a745;"></i>Supplier Account Statement
                            <span class="badge ms-2" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="fas fa-desktop me-1"></i>ERP System
                            </span>
                        </h5>
                        <h5 class="mb-0" id="supplierBalance"></h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: rgba(40, 167, 69, 0.05);">
                                <tr>
                                    <th class="px-4 py-3" style="width: 15%;">Reference</th>
                                    <th class="py-3" style="width: 12%;">Date</th>
                                    {{-- <th class="py-3">Description</th> --}}
                                    <th class="py-3" style="width: 20%;">Type</th>
                                    <th class="py-3 text-end" style="width: 15%;">Debit</th>
                                    <th class="py-3 text-end" style="width: 15%;">Credit</th>
                                    <th class="py-3 text-end pe-4" style="width: 18%;">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="supplierTransactionsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-4 text-muted">
            <small>
                <i class="fas fa-info-circle me-1"></i>
                <span id="generatedTimestamp"></span>
            </small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#customerSelect').select2({
        ajax: {
            url: "{{ route('thirdparties.select2') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                const term = (params.term || '').trim();
                console.debug('[CustomerSelect] request', { term, page: params.page || 1 });
                return {
                    q: term,
                    page: params.page || 1
                };
            },
            processResults: function(data) {
                console.debug('[CustomerSelect] response', data);
                return {
                    results: data.results || [],
                    pagination: data.pagination || { more: false }
                };
            },
            cache: true,
            error: function(xhr, status, err) {
                console.error('[CustomerSelect] ajax error', { status, err, response: xhr?.responseText });
            }
        },
        placeholder: '-- Search and select a customer --',
        minimumInputLength: 2,
        allowClear: true,
        width: '100%',
        language: {
            inputTooShort: function() {
                return 'Type at least 2 characters to search';
            }
        }
    });

    $('#customerSelect').on('select2:select', function(e) {
        console.debug('[CustomerSelect] selected', e.params?.data);
    });
    $('#customerSelect').on('select2:open', function() {
        console.debug('[CustomerSelect] opened');
    });
    $('#customerSelect').on('select2:close', function() {
        console.debug('[CustomerSelect] closed');
    });

    // Enable/disable load button based on selection
    $('#customerSelect').on('change', function() {
        const selected = $(this).val();
        $('#loadStatementBtn').prop('disabled', !selected);
    });

    // Load statement on button click
    $('#loadStatementBtn').on('click', function() {
        const customerId = $('#customerSelect').val();
        if (customerId) {
            loadStatement(customerId);
        }
    });

    // Back to search button
    $('#backToSearchBtn').on('click', function() {
        $('#statementContent').removeClass('show');
        $('#customerSelectSection').show();
        $('#customerSelect').val(null).trigger('change');
    });

    function loadStatement(customerId) {
        // Show loading indicator
        $('#customerSelectSection').hide();
        $('#statementContent').removeClass('show');
        $('#loadingIndicator').show();

        // Fetch statement data
        $.ajax({
            url: `/finance/customerstatement/${customerId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    displayStatement(response);
                    $('#loadingIndicator').hide();
                    $('#statementContent').addClass('show');
                } else {
                    if (typeof nError === 'function') {
                        nError('Failed to load statement');
                    } else {
                        alert('Failed to load statement');
                    }
                    $('#loadingIndicator').hide();
                    $('#customerSelectSection').show();
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.error || 'Failed to load statement';
                if (typeof nError === 'function') {
                    nError(errorMsg);
                } else {
                    alert(errorMsg);
                }
                $('#loadingIndicator').hide();
                $('#customerSelectSection').show();
            }
        });
    }

    function displayStatement(data) {
        const customer = data.customer;
        const tenantTransactions = data.tenantTransactions || [];
        const supplierTransactions = data.supplierTransactions || [];

        // Display customer information
        $('#customerInitial').text(customer.name.charAt(0).toUpperCase());
        $('#customerName').text(customer.name);
        $('#customerIdNumber').text(customer.id_number);
        $('#customerEmail').text(customer.email);
        $('#customerPhone').text(customer.phone);
        $('#customerAddress').text(customer.address);

        // Display customer types badges
        const typeColors = {
            'Tenant': { bg: 'rgba(90, 116, 231, 0.1)', text: '#5a74e7' },
            'Supplier': { bg: 'rgba(40, 167, 69, 0.1)', text: '#28a745' },
            'Client': { bg: 'rgba(109, 63, 154, 0.1)', text: '#6d3f9a' }
        };
        
        let typesHtml = '';
        customer.types.forEach(type => {
            const color = typeColors[type] || { bg: 'rgba(128, 128, 128, 0.1)', text: '#808080' };
            typesHtml += `<span class="badge" style="background-color: ${color.bg}; color: ${color.text};">${type}</span>`;
        });
        $('#customerTypes').html(typesHtml);

        // Display statement date
        const now = new Date();
        $('#statementDate').text(now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));
        $('#generatedTimestamp').text(`This statement was generated on ${now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })} at ${now.toLocaleTimeString('en-US')}`);

        // Display Tenant transactions
        if (tenantTransactions.length > 0) {
            $('#tenantStatementSection').show();
            const tenantBalance = tenantTransactions[tenantTransactions.length - 1].balance;
            const balanceColor = tenantBalance < 0 ? '#dc3545' : '#28a745';
            const balanceLabel = tenantBalance < 0 ? '(Dr)' : '(Cr)';
            $('#tenantBalance').html(`<span style="color: ${balanceColor};">Balance: ${tenantTransactions[0].currency_code} ${Math.abs(tenantBalance).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} <small class="text-muted">${balanceLabel}</small></span>`);
            
            let tenantHtml = '';
            tenantTransactions.forEach(tx => {
                const txBalanceColor = tx.balance < 0 ? '#dc3545' : '#28a745';
                const txBalanceLabel = tx.balance < 0 ? 'Dr' : 'Cr';
                const txDate = new Date(tx.date);
                tenantHtml += `
                    <tr>
                        <td class="px-4">${tx.reference_number}</td>
                        <td class="text-muted">${txDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                        <!--<td>${tx.description}</td>-->
                        <td><small class="text-muted">${tx.transaction_type}</small></td>
                        <td class="text-end" style="color: #dc3545;">${tx.debit > 0 ? tx.debit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '-'}</td>
                        <td class="text-end" style="color: #28a745;">${tx.credit > 0 ? tx.credit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '-'}</td>
                        <td class="text-end pe-4 fw-semibold" style="color: ${txBalanceColor};">${Math.abs(tx.balance).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} <small>${txBalanceLabel}</small></td>
                    </tr>
                `;
            });
            $('#tenantTransactionsBody').html(tenantHtml);
        } else {
            $('#tenantStatementSection').hide();
        }

        // Display Supplier transactions
        if (supplierTransactions.length > 0) {
            $('#supplierStatementSection').show();
            const supplierBalance = supplierTransactions[supplierTransactions.length - 1].balance;
            const balanceColor = supplierBalance < 0 ? '#dc3545' : '#28a745';
            const balanceLabel = supplierBalance < 0 ? '(Dr)' : '(Cr)';
            $('#supplierBalance').html(`<span style="color: ${balanceColor};">Balance: ${supplierTransactions[0].currency_code} ${Math.abs(supplierBalance).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} <small class="text-muted">${balanceLabel}</small></span>`);
            
            let supplierHtml = '';
            supplierTransactions.forEach(tx => {
                const txBalanceColor = tx.balance < 0 ? '#dc3545' : '#28a745';
                const txBalanceLabel = tx.balance < 0 ? 'Dr' : 'Cr';
                const txDate = new Date(tx.date);
                supplierHtml += `
                    <tr>
                        <td class="px-4">${tx.reference_number}</td>
                        <td class="text-muted">${txDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</td>
                        <!--<td>${tx.description}</td>-->
                        <td><small class="text-muted">${tx.transaction_type}</small></td>
                        <td class="text-end" style="color: #dc3545;">${tx.debit > 0 ? tx.debit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '-'}</td>
                        <td class="text-end" style="color: #28a745;">${tx.credit > 0 ? tx.credit.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '-'}</td>
                        <td class="text-end pe-4 fw-semibold" style="color: ${txBalanceColor};">${Math.abs(tx.balance).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} <small>${txBalanceLabel}</small></td>
                    </tr>
                `;
            });
            $('#supplierTransactionsBody').html(supplierHtml);
        } else {
            $('#supplierStatementSection').hide();
        }
    }
});
</script>
@endsection
