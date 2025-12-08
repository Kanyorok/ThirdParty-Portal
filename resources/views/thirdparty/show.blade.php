@extends('layouts.app')

@section('title', $party->Name . ' - Third Party Details')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('thirdparty.parties.index') }}">Third Parties</a></li>
    <li class="breadcrumb-item active">{{ $party->Name }}</li>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .tab-pane {
            padding: 20px 0;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 180px;
        }
        .info-value {
            color: #212529;
        }
        .badge-type {
            font-size: 0.85em;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .tab-content {
            min-height: 400px;
        }
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .logo-container {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }
        .logo-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        @if($party->logo && $party->logo->FilePath)
                            <div class="logo-container me-3">
                                <img src="{{ asset('storage/' . $party->logo->FilePath) }}" alt="{{ $party->Name }} Logo">
                            </div>
                        @endif
                        <div>
                            <h1 class="mb-1">{{ $party->Name }}</h1>
                            <div class="text-muted">
                                @foreach($party->types as $type)
                                    <span class="badge badge-type bg-primary me-1">{{ $type->Description }}</span>
                                @endforeach
                                @if($party->TradingName)
                                    <div class="mt-1">
                                        <small>Trading as: {{ $party->TradingName }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('thirdparty.parties.edit', $party->Id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-2"></i>Delete
                        </button>
                        <a href="{{ route('thirdparty.parties.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="row mb-3">
            <div class="col-12">
                <ul class="nav nav-tabs" id="thirdPartyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" 
                                data-bs-target="#profile" type="button" role="tab" aria-controls="profile" 
                                aria-selected="true">
                            <i class="fas fa-user-circle me-2"></i>Profile
                        </button>
                    </li>
                    
                    @if(in_array('supplier', $availableTabs))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="supplier-tab" data-bs-toggle="tab" 
                                data-bs-target="#supplier" type="button" role="tab" aria-controls="supplier" 
                                aria-selected="false" data-tab="supplier">
                            <i class="fas fa-truck me-2"></i>Supplier
                        </button>
                    </li>
                    @endif
                    
                    @if(in_array('customer', $availableTabs))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="customer-tab" data-bs-toggle="tab" 
                                data-bs-target="#customer" type="button" role="tab" aria-controls="customer" 
                                aria-selected="false" data-tab="customer">
                            <i class="fas fa-shopping-cart me-2"></i>Customer
                        </button>
                    </li>
                    @endif
                    
                    @if(in_array('tenant', $availableTabs))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tenant-tab" data-bs-toggle="tab" 
                                data-bs-target="#tenant" type="button" role="tab" aria-controls="tenant" 
                                aria-selected="false" data-tab="tenant">
                            <i class="fas fa-home me-2"></i>Tenant
                        </button>
                    </li>
                    @endif
                    
                    @if(in_array('attribution', $availableTabs))
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attribution-tab" data-bs-toggle="tab" 
                                data-bs-target="#attribution" type="button" role="tab" aria-controls="attribution" 
                                aria-selected="false" data-tab="attribution">
                            <i class="fas fa-file-alt me-2"></i>Attribution
                        </button>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="row">
            <div class="col-12">
                <div class="tab-content" id="thirdPartyTabsContent">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Basic Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Name:</div>
                                            <div class="col-8 info-value">{{ $party->Name }}</div>
                                        </div>
                                        @if($party->TradingName)
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Trading Name:</div>
                                            <div class="col-8 info-value">{{ $party->TradingName }}</div>
                                        </div>
                                        @endif
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Business Type:</div>
                                            <div class="col-8 info-value">{{ $tabData['profile']['basicInfo']['businessType']['description'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Registration No:</div>
                                            <div class="col-8 info-value">{{ $party->RegistrationNumber }}</div>
                                        </div>
                                        @if($party->TaxPIN)
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Tax PIN:</div>
                                            <div class="col-8 info-value">{{ $party->TaxPIN }}</div>
                                        </div>
                                        @endif
                                        @if($party->VATNumber)
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">VAT Number:</div>
                                            <div class="col-8 info-value">{{ $party->VATNumber }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-address-book me-2"></i>Contact Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Email:</div>
                                            <div class="col-8 info-value">
                                                <a href="mailto:{{ $party->Email }}">{{ $party->Email }}</a>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Phone:</div>
                                            <div class="col-8 info-value">
                                                <a href="tel:{{ $party->Phone }}">{{ $party->Phone }}</a>
                                            </div>
                                        </div>
                                        @if($party->Website)
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Website:</div>
                                            <div class="col-8 info-value">
                                                <a href="{{ $party->Website }}" target="_blank">{{ $party->Website }}</a>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Country:</div>
                                            <div class="col-8 info-value">
                                                @if($party->country)
                                                    @if($party->country->Flag)
                                                        <span class="me-2">{{ $party->country->Flag }}</span>
                                                    @endif
                                                    {{ $party->country->Name }}
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Location:</div>
                                            <div class="col-8 info-value">{{ $tabData['profile']['locationInfo']['location'] ?? 'N/A' }}</div>
                                        </div>
                                        @if($party->PhysicalAddress)
                                        <div class="row mb-2">
                                            <div class="col-4 info-label">Address:</div>
                                            <div class="col-8 info-value">{{ $party->PhysicalAddress }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Users Section -->
                            @if($tabData['profile']['users'] && count($tabData['profile']['users']) > 0)
                            <div class="col-12 mt-3">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-users me-2"></i>Associated Users
                                        </h5>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                            <i class="fas fa-plus me-1"></i>Add User
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Gender</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($tabData['profile']['users'] as $user)
                                                    <tr>
                                                        <td>{{ $user['firstName'] }} {{ $user['lastName'] }}</td>
                                                        <td><a href="mailto:{{ $user['email'] }}">{{ $user['email'] }}</a></td>
                                                        <td><a href="tel:{{ $user['phone'] }}">{{ $user['phone'] }}</a></td>
                                                        <td>{{ $user['gender'] }}</td>
                                                        <td>
                                                            <span class="badge bg-success">{{ $user['status'] }}</span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Status & Audit -->
                            <div class="col-12 mt-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-history me-2"></i>Status & Audit Information
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="row mb-2">
                                                    <div class="col-6 info-label">Status:</div>
                                                    <div class="col-6 info-value">
                                                        <span class="badge bg-success">{{ $tabData['profile']['statusInfo']['status'] ?? 'Active' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="row mb-2">
                                                    <div class="col-6 info-label">Created:</div>
                                                    <div class="col-6 info-value">
                                                        {{ $tabData['profile']['statusInfo']['createdAt'] ? \Carbon\Carbon::parse($tabData['profile']['statusInfo']['createdAt'])->format('M d, Y H:i') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="row mb-2">
                                                    <div class="col-6 info-label">Last Updated:</div>
                                                    <div class="col-6 info-value">
                                                        {{ $tabData['profile']['statusInfo']['updatedAt'] ? \Carbon\Carbon::parse($tabData['profile']['statusInfo']['updatedAt'])->format('M d, Y H:i') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="row mb-2">
                                                    <div class="col-6 info-label">Updated By:</div>
                                                    <div class="col-6 info-value">
                                                        {{ $tabData['profile']['statusInfo']['updatedBy'] ?? 'System' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier Tab -->
                    @if(in_array('supplier', $availableTabs))
                    <div class="tab-pane fade" id="supplier" role="tabpanel" aria-labelledby="supplier-tab">
                        @if($tabData['supplier']['exists'])
                            <div class="row">
                                <!-- Supplier Information -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-info-circle me-2"></i>Supplier Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Supplier Code:</div>
                                                <div class="col-8 info-value">{{ $tabData['supplier']['supplierInfo']['supplierCode'] }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Payment Terms:</div>
                                                <div class="col-8 info-value">{{ $tabData['supplier']['supplierInfo']['paymentTerms'] }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Credit Limit:</div>
                                                <div class="col-8 info-value">{{ number_format($tabData['supplier']['supplierInfo']['creditLimit'], 2) }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Lead Time:</div>
                                                <div class="col-8 info-value">{{ $tabData['supplier']['supplierInfo']['leadTime'] }} days</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Rating:</div>
                                                <div class="col-8 info-value">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star {{ $i <= $tabData['supplier']['supplierInfo']['rating'] ? 'text-warning' : 'text-muted' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Performance Metrics -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-chart-line me-2"></i>Performance Metrics
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">On-Time Delivery:</div>
                                                <div class="col-6 info-value">{{ $tabData['supplier']['performance']['onTimeDelivery'] }}%</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Quality Rating:</div>
                                                <div class="col-6 info-value">{{ $tabData['supplier']['performance']['qualityRating'] }}/10</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Total Orders:</div>
                                                <div class="col-6 info-value">{{ $tabData['supplier']['performance']['totalOrders'] }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Total Spend:</div>
                                                <div class="col-6 info-value">{{ number_format($tabData['supplier']['performance']['totalSpend'], 2) }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Last Order:</div>
                                                <div class="col-6 info-value">
                                                    @if($tabData['supplier']['performance']['lastOrderDate'])
                                                        {{ \Carbon\Carbon::parse($tabData['supplier']['performance']['lastOrderDate'])->format('M d, Y') }}
                                                    @else
                                                        No orders yet
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Products Supplied -->
                                @if($tabData['supplier']['products'] && count($tabData['supplier']['products']) > 0)
                                <div class="col-12 mt-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-boxes me-2"></i>Products Supplied
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Code</th>
                                                            <th>Product Name</th>
                                                            <th>Category</th>
                                                            <th>Unit Price</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($tabData['supplier']['products'] as $product)
                                                        <tr>
                                                            <td>{{ $product['code'] }}</td>
                                                            <td>{{ $product['name'] }}</td>
                                                            <td>{{ $product['category'] }}</td>
                                                            <td>{{ number_format($product['unitPrice'], 2) }}</td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-truck text-muted"></i>
                                <h4>No Supplier Information</h4>
                                <p>This third party is not set up as a supplier.</p>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Customer Tab -->
                    @if(in_array('customer', $availableTabs))
                    <div class="tab-pane fade" id="customer" role="tabpanel" aria-labelledby="customer-tab">
                        @if($tabData['customer']['exists'])
                            <div class="row">
                                <!-- Demographics -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-user-friends me-2"></i>Demographics
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Date of Birth:</div>
                                                <div class="col-8 info-value">
                                                    @if($tabData['customer']['demographics']['dateOfBirth'])
                                                        {{ \Carbon\Carbon::parse($tabData['customer']['demographics']['dateOfBirth'])->format('M d, Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Gender:</div>
                                                <div class="col-8 info-value">{{ $tabData['customer']['demographics']['gender']['description'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Marital Status:</div>
                                                <div class="col-8 info-value">{{ $tabData['customer']['demographics']['maritalStatus']['description'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Occupation:</div>
                                                <div class="col-8 info-value">{{ $tabData['customer']['demographics']['occupation']['description'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Customer Metrics -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-chart-bar me-2"></i>Customer Metrics
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Customer Since:</div>
                                                <div class="col-6 info-value">
                                                    {{ \Carbon\Carbon::parse($tabData['customer']['customerMetrics']['customerSince'])->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Last Purchase:</div>
                                                <div class="col-6 info-value">
                                                    @if($tabData['customer']['customerMetrics']['lastPurchase'])
                                                        {{ \Carbon\Carbon::parse($tabData['customer']['customerMetrics']['lastPurchase'])->format('M d, Y') }}
                                                    @else
                                                        No purchases yet
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Total Purchases:</div>
                                                <div class="col-6 info-value">{{ $tabData['customer']['customerMetrics']['totalPurchases'] }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Loyalty Points:</div>
                                                <div class="col-6 info-value">{{ number_format($tabData['customer']['customerMetrics']['loyaltyPoints'], 0) }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6 info-label">Credit Limit:</div>
                                                <div class="col-6 info-value">{{ number_format($tabData['customer']['customerMetrics']['creditLimit'], 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart text-muted"></i>
                                <h4>No Customer Information</h4>
                                <p>This third party is not set up as a customer.</p>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Tenant Tab -->
                    @if(in_array('tenant', $availableTabs))
                    <div class="tab-pane fade" id="tenant" role="tabpanel" aria-labelledby="tenant-tab">
                        @if($tabData['tenant']['exists'])
                            <div class="row">
                                <!-- Tenant Information -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-home me-2"></i>Tenant Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Tenant Code:</div>
                                                <div class="col-8 info-value">{{ $tabData['tenant']['tenantInfo']['tenantCode'] }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Remarks:</div>
                                                <div class="col-8 info-value">{{ $tabData['tenant']['tenantInfo']['remarks'] }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Lease Period:</div>
                                                <div class="col-8 info-value">
                                                    {{ \Carbon\Carbon::parse($tabData['tenant']['tenantInfo']['leaseStart'])->format('M d, Y') }} 
                                                    to 
                                                    {{ \Carbon\Carbon::parse($tabData['tenant']['tenantInfo']['leaseEnd'])->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Rent Amount:</div>
                                                <div class="col-8 info-value">{{ number_format($tabData['tenant']['tenantInfo']['rentAmount'], 2) }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Security Deposit:</div>
                                                <div class="col-8 info-value">{{ number_format($tabData['tenant']['tenantInfo']['securityDeposit'], 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Property Information -->
                                @if($tabData['tenant']['propertyInfo']['property'])
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-building me-2"></i>Property Information
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Property:</div>
                                                <div class="col-8 info-value">{{ $tabData['tenant']['propertyInfo']['property']->Name ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Unit Number:</div>
                                                <div class="col-8 info-value">{{ $tabData['tenant']['propertyInfo']['unitNumber'] }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4 info-label">Floor:</div>
                                                <div class="col-8 info-value">{{ $tabData['tenant']['propertyInfo']['floor'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-home text-muted"></i>
                                <h4>No Tenant Information</h4>
                                <p>This third party is not set up as a tenant.</p>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Attribution Tab -->
                    @if(in_array('attribution', $availableTabs))
                    <div class="tab-pane fade" id="attribution" role="tabpanel" aria-labelledby="attribution-tab">
                        <div class="row">
                            <!-- Notes Section -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-sticky-note me-2"></i>Notes
                                        </h5>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                            <i class="fas fa-plus me-1"></i>Add Note
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        @if($tabData['attribution']['notes'] && count($tabData['attribution']['notes']) > 0)
                                            @foreach($tabData['attribution']['notes'] as $note)
                                            <div class="activity-item">
                                                <p class="mb-1">{{ $note['content'] }}</p>
                                                <small class="text-muted">
                                                    Added by {{ $note['createdBy'] }} • 
                                                    {{ \Carbon\Carbon::parse($note['createdAt'])->format('M d, Y H:i') }}
                                                </small>
                                            </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fas fa-sticky-note fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">No notes added yet</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Documents Section -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file me-2"></i>Documents
                                        </h5>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        @if($tabData['attribution']['documents'] && count($tabData['attribution']['documents']) > 0)
                                            <div class="list-group">
                                                @foreach($tabData['attribution']['documents'] as $document)
                                                <div class="list-group-item list-group-item-action">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                                            <span>{{ $document['name'] }}</span>
                                                            <small class="text-muted d-block">{{ $document['type'] }} • {{ $document['size'] }}</small>
                                                        </div>
                                                        <div>
                                                            <a href="{{ asset('storage/' . $document['url']) }}" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fas fa-file fa-2x text-muted mb-3"></i>
                                                <p class="text-muted">No documents uploaded yet</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete "{{ $party->Name }}"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('thirdparty.parties.destroy', $party->Id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
    <script>
        $(function () {
            // Handle tab switching with AJAX loading
            $('button[data-tab]').on('click', function() {
                const tab = $(this).data('tab');
                const tabContent = $(`#${tab}`);
                
                // If tab content is empty, load via AJAX
                if (tabContent.is(':empty')) {
                    $.ajax({
                        url: '{{ route("thirdparty.parties.tab-content", $party->Id) }}',
                        method: 'GET',
                        data: { tab: tab },
                        success: function(response) {
                            if (response.success) {
                                tabContent.html(response.html);
                            }
                        },
                        error: function() {
                            tabContent.html('<div class="alert alert-danger">Failed to load tab content</div>');
                        }
                    });
                }
            });

            // Set active tab from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab');
            if (activeTab && $(`#${activeTab}-tab`).length) {
                $(`#${activeTab}-tab`).tab('show');
            }

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection