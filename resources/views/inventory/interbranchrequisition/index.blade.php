@extends('layouts.app')

@section('title', 'Inter-Branch Requisition')

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

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="sessionErrorAlert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="sessionSuccessAlert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div id="customErrorContainer" style="display:none;">
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="customErrorMessage">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                onclick="hideCustomError()"></button>
    </div>
</div>

<div class="container mt-4">

    <div class="mb-3 d-flex justify-content-between align-items-end flex-wrap">
        <a href="{{ route('interbranchrequisition.create') }}" class="btn btn-sm btn-success mb-2">
            <i class="bi bi-plus-circle"></i> Add Requisition
        </a>
        <form id="filterForm" method="GET" class="d-flex align-items-center mb-2">
            <label for="filterStatus" class="me-2 fw-bold">Filter by status:</label>
            <select id="filterStatus" name="status" class="form-select form-select-sm me-2" style="width: 170px;">
                <option value="">Show All</option>
                <option value="Ap" {{ request('status') == 'Ap' ? 'selected' : '' }}>Approved</option>
                <option value="P" {{ request('status') == 'P' ? 'selected' : '' }}>Pending Approval</option>
                <option value="Re" {{ request('status') == 'Re' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if(request()->has('status') && request('status') !== null && request('status') !== "")
                <a href="{{ route('interbranchrequisition.index') }}" class="btn btn-link btn-sm ms-2">Reset</a>
            @endif
        </form>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" id="requisitionTabs" role="tablist">
        @if($isHeadOffice)
            <!-- For Head Office - All Requisitions Tab -->
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" 
                        type="button" role="tab" aria-controls="all" aria-selected="true">
                    All Requisitions
                    <span class="badge bg-secondary ms-1">{{ $allRequisitions->count() }}</span>
                </button>
            </li>
            <!-- For Head Office - Outgoing Tab (what HQ sends to others) -->
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" 
                        type="button" role="tab" aria-controls="outgoing" aria-selected="false">
                    Outgoing (HQ to Branches)
                    <span class="badge bg-info ms-1">{{ $outgoingRequisitions->count() }}</span>
                </button>
            </li>
        @else
            <!-- For Non-HQ Branches - Incoming Tab (what comes to my branch) -->
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming" 
                        type="button" role="tab" aria-controls="incoming" aria-selected="true">
                    Incoming Requisitions
                    <span class="badge bg-primary ms-1">{{ $incomingRequisitions->count() }}</span>
                </button>
            </li>
            <!-- For Non-HQ Branches - Outgoing Tab (what my branch sends to others) -->
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" 
                        type="button" role="tab" aria-controls="outgoing" aria-selected="false">
                    Outgoing Requisitions
                    <span class="badge bg-success ms-1">{{ $outgoingRequisitions->count() }}</span>
                </button>
            </li>
        @endif
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="requisitionTabsContent">
        @if($isHeadOffice)
            <!-- Head Office - All Requisitions Tab -->
            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle requisition-table" id="allTable">
                                <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Requisition No</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($allRequisitions as $requisition)
                                    @php
                                        // Determine if current user's branch raised this requisition
                                        $isRaisedByCurrentBranch = $requisition->FromBranch == $currentBranch->Id;
                                        
                                        // Check if user can modify this requisition based on branch logic
                                        $canModifyByBranch = $isRaisedByCurrentBranch;
                                        
                                        // Get status enum
                                        $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($requisition->Status);
                                        
                                        // Check if status allows editing (only Pending status)
                                        $statusAllowsEdit = $requisition->Status === 'P';
                                        $statusAllowsDelete = $requisition->Status === 'P';
                                        
                                        // Final decision combining branch logic AND status logic
                                        $canEdit = $canModifyByBranch && $statusAllowsEdit;
                                        $canDelete = $canModifyByBranch && $statusAllowsDelete;
                                        
                                        // Tooltip messages
                                        $editTooltip = '';
                                        $deleteTooltip = '';
                                        
                                        if (!$canModifyByBranch) {
                                            $editTooltip = 'You cannot edit requisitions raised by other branches.';
                                            $deleteTooltip = 'You cannot delete requisitions raised by other branches.';
                                        } else if (!$statusAllowsEdit) {
                                            $editTooltip = 'Cannot edit - requisition status is ' . ($statusEnum ? $statusEnum->label() : $requisition->Status);
                                            $deleteTooltip = 'Cannot delete - requisition status is ' . ($statusEnum ? $statusEnum->label() : $requisition->Status);
                                        } else {
                                            $editTooltip = 'Edit Requisition';
                                            $deleteTooltip = 'Delete Requisition';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $requisition->ReqNo ?? '-' }}</td>
                                        <td>{{ $requisition->fromBranch->Name ?? '-' }}</td>
                                        <td>{{ $requisition->toBranch->Name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($requisition->CreatedOn)->format('d M Y') }}</td>
                                        <td>
                                            @if($statusEnum)
                                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $requisition->Status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $requisition->items->count() }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- View button - always available -->
                                                <a href="{{ route('interbranchrequisition.show', $requisition->Id) }}"
                                                   class="btn btn-view btn-sm"
                                                   data-bs-toggle="tooltip"
                                                   title="View Requisition">
                                                    <i class="bi bi-eye text-white"></i>
                                                </a>
                                                
                                                <!-- Edit button - conditional -->
                                                @if($canEdit)
                                                    <a href="{{ route('interbranchrequisition.edit', $requisition->Id) }}"
                                                       class="btn btn-edit btn-sm"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ $editTooltip }}">
                                                        <i class="bi bi-pencil text-white"></i>
                                                    </a>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-edit btn-sm disabled"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $editTooltip }}"
                                                            onclick="return showCustomError('{{ $editTooltip }}');">
                                                        <i class="bi bi-pencil text-white"></i>
                                                    </button>
                                                @endif
                                                
                                                <!-- Delete button - conditional -->
                                                @if($canDelete)
                                                    <button type="button"
                                                            class="btn btn-delete btn-sm"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $deleteTooltip }}"
                                                            onclick="confirmDelete('{{ $requisition->Id }}', '{{ $requisition->ReqNo }}')">
                                                        <i class="bi bi-trash text-white"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-delete btn-sm disabled"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $deleteTooltip }}"
                                                            onclick="return showCustomError('{{ $deleteTooltip }}');">
                                                        <i class="bi bi-trash text-white"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            <form id="delete-form-{{ $requisition->Id }}"
                                                  action="{{ route('interbranchrequisition.destroy', $requisition->Id) }}"
                                                  method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Head Office - Outgoing Tab -->
            <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle requisition-table" id="hqOutgoingTable">
                                <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Requisition No</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($outgoingRequisitions as $requisition)
                                    @php
                                        // For HQ outgoing, all are raised by HQ
                                        $isRaisedByCurrentBranch = true;
                                        $canModifyByBranch = $isRaisedByCurrentBranch;
                                        
                                        // Get status enum
                                        $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($requisition->Status);
                                        
                                        // Check if status allows editing (only Pending status)
                                        $statusAllowsEdit = $requisition->Status === 'P';
                                        $statusAllowsDelete = $requisition->Status === 'P';
                                        
                                        // Final decision combining branch logic AND status logic
                                        $canEdit = $canModifyByBranch && $statusAllowsEdit;
                                        $canDelete = $canModifyByBranch && $statusAllowsDelete;
                                        
                                        // Tooltip messages
                                        $editTooltip = '';
                                        $deleteTooltip = '';
                                        
                                        if (!$statusAllowsEdit) {
                                            $editTooltip = 'Cannot edit - requisition status is ' . ($statusEnum ? $statusEnum->label() : $requisition->Status);
                                            $deleteTooltip = 'Cannot delete - requisition status is ' . ($statusEnum ? $statusEnum->label() : $requisition->Status);
                                        } else {
                                            $editTooltip = 'Edit Requisition';
                                            $deleteTooltip = 'Delete Requisition';
                                        }
                                    @endphp
                                    <tr class="outgoing-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $requisition->ReqNo ?? '-' }}</td>
                                        <td>{{ $requisition->fromBranch->Name ?? '-' }}</td>
                                        <td>{{ $requisition->toBranch->Name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($requisition->CreatedOn)->format('d M Y') }}</td>
                                        <td>
                                            @if($statusEnum)
                                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $requisition->Status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $requisition->items->count() }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- View button - always available -->
                                                <a href="{{ route('interbranchrequisition.show', $requisition->Id) }}"
                                                   class="btn btn-view btn-sm"
                                                   data-bs-toggle="tooltip"
                                                   title="View Requisition">
                                                    <i class="bi bi-eye text-white"></i>
                                                </a>
                                                
                                                <!-- Edit button - conditional -->
                                                @if($canEdit)
                                                    <a href="{{ route('interbranchrequisition.edit', $requisition->Id) }}"
                                                       class="btn btn-edit btn-sm"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ $editTooltip }}">
                                                        <i class="bi bi-pencil text-white"></i>
                                                    </a>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-edit btn-sm disabled"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $editTooltip }}"
                                                            onclick="return showCustomError('{{ $editTooltip }}');">
                                                        <i class="bi bi-pencil text-white"></i>
                                                    </button>
                                                @endif
                                                
                                                <!-- Delete button - conditional -->
                                                @if($canDelete)
                                                    <button type="button"
                                                            class="btn btn-delete btn-sm"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $deleteTooltip }}"
                                                            onclick="confirmDelete('{{ $requisition->Id }}', '{{ $requisition->ReqNo }}')">
                                                        <i class="bi bi-trash text-white"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-delete btn-sm disabled"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $deleteTooltip }}"
                                                            onclick="return showCustomError('{{ $deleteTooltip }}');">
                                                        <i class="bi bi-trash text-white"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            <form id="delete-form-{{ $requisition->Id }}"
                                                  action="{{ route('interbranchrequisition.destroy', $requisition->Id) }}"
                                                  method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Non-HQ - Incoming Tab -->
            <div class="tab-pane fade show active" id="incoming" role="tabpanel" aria-labelledby="incoming-tab">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle requisition-table" id="incomingTable">
                                <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Requisition No</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($incomingRequisitions as $requisition)
                                    @php
                                        // For incoming requisitions, current branch is the receiving branch (ToBranch)
                                        $isRaisedByCurrentBranch = $requisition->FromBranch == $currentBranch->Id;
                                        
                                        // Incoming requisitions are NOT raised by current branch (they come FROM others TO us)
                                        $canModifyByBranch = $isRaisedByCurrentBranch;
                                        
                                        // Get status enum
                                        $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($requisition->Status);
                                        
                                        // Check if status allows editing (only Pending status)
                                        $statusAllowsEdit = $requisition->Status === 'P';
                                        $statusAllowsDelete = $requisition->Status === 'P';
                                        
                                        // Final decision combining branch logic AND status logic
                                        $canEdit = $canModifyByBranch && $statusAllowsEdit;
                                        $canDelete = $canModifyByBranch && $statusAllowsDelete;
                                        
                                        // Tooltip messages
                                        $editTooltip = '';
                                        $deleteTooltip = '';
                                        
                                        if (!$canModifyByBranch) {
                                            $editTooltip = 'You cannot edit incoming requisitions from other branches.';
                                            $deleteTooltip = 'You cannot delete incoming requisitions from other branches.';
                                        } else if (!$statusAllowsEdit) {
                                            $editTooltip = 'Cannot edit - requisition status is ' . ($statusEnum ? $statusEnum->label() : $requisition->Status);
                                            $deleteTooltip = 'Cannot delete - requisition status is ' . ($statusEnum ? $statusEnum->label() : $requisition->Status);
                                        } else {
                                            $editTooltip = 'Edit Requisition';
                                            $deleteTooltip = 'Delete Requisition';
                                        }
                                    @endphp
                                    <tr class="incoming-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $requisition->ReqNo ?? '-' }}</td>
                                        <td>{{ $requisition->fromBranch->Name ?? '-' }}</td>
                                        <td>{{ $requisition->toBranch->Name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($requisition->CreatedOn)->format('d M Y') }}</td>
                                        <td>
                                            @if($statusEnum)
                                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $requisition->Status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $requisition->items->count() }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- View button - always available -->
                                                <a href="{{ route('interbranchrequisition.show', $requisition->Id) }}"
                                                   class="btn btn-view btn-sm"
                                                   data-bs-toggle="tooltip"
                                                   title="View Requisition">
                                                    <i class="bi bi-eye text-white"></i>
                                                </a>
                                                
                                                <!-- Edit button - conditional -->
                                                @if($canEdit)
                                                    <a href="{{ route('interbranchrequisition.edit', $requisition->Id) }}"
                                                       class="btn btn-edit btn-sm"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ $editTooltip }}">
                                                        <i class="bi bi-pencil text-white"></i>
                                                    </a>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-edit btn-sm disabled"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $editTooltip }}"
                                                            onclick="return showCustomError('{{ $editTooltip }}');">
                                                        <i class="bi bi-pencil text-white"></i>
                                                    </button>
                                                @endif
                                                
                                                <!-- Delete button - conditional -->
                                                @if($canDelete)
                                                    <button type="button"
                                                            class="btn btn-delete btn-sm"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $deleteTooltip }}"
                                                            onclick="confirmDelete('{{ $requisition->Id }}', '{{ $requisition->ReqNo }}')">
                                                        <i class="bi bi-trash text-white"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-delete btn-sm disabled"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $deleteTooltip }}"
                                                            onclick="return showCustomError('{{ $deleteTooltip }}');">
                                                        <i class="bi bi-trash text-white"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            <form id="delete-form-{{ $requisition->Id }}"
                                                  action="{{ route('interbranchrequisition.destroy', $requisition->Id) }}"
                                                  method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Non-HQ - Outgoing Tab -->
            <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle requisition-table" id="outgoingTable">
                                <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Requisition No</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($outgoingRequisitions as $requisition)
                                    @php
                                        // For outgoing requisitions, current branch is the sending branch (FromBranch)
                                        $isRaisedByCurrentBranch = $requisition->FromBranch == $currentBranch->Id;
                                        
                                        // Outgoing requisitions ARE raised by current branch
                                        $canModifyByBranch = $isRaisedByCurrentBranch;
                                        
                                        // Get status enum
                                        $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($requisition->Status);
                                        
                                        // Check if status allows editing (only Pending status)
                                        $statusAllowsEdit = $requisition->Status === 'P';
                                        $statusAllowsDelete = $requisition->Status === 'P';
                                        
                                        // Final decision combining branch logic AND status logic
                                        $canEdit = $canModifyByBranch && $statusAllowsEdit;
                                        $canDelete = $canModifyByBranch && $statusAllowsDelete;
                                        
                                        // Tooltip messages
                                        $editTooltip = '';
                                        $deleteTooltip = '';
                                        
                                        if (!$canModifyByBranch) {
                                            $editTooltip = 'You cannot edit requisitions raised by other branches.';
                                            $deleteTooltip = 'You cannot delete requisitions raised by other branches.';
                                        } else if (!$statusAllowsEdit) {
                                            $editTooltip = 'Cannot edit - requisition status is ' . ($statusEnum ? $statusEnum->label() : $requisition->Status);
                                            $deleteTooltip = 'Cannot delete - requisition status is ' . ($statusEnum ? $statusEnum->label() : $requisition->Status);
                                        } else {
                                            $editTooltip = 'Edit Requisition';
                                            $deleteTooltip = 'Delete Requisition';
                                        }
                                    @endphp
                                    <tr class="outgoing-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $requisition->ReqNo ?? '-' }}</td>
                                        <td>{{ $requisition->fromBranch->Name ?? '-' }}</td>
                                        <td>{{ $requisition->toBranch->Name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($requisition->CreatedOn)->format('d M Y') }}</td>
                                        <td>
                                            @if($statusEnum)
                                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $requisition->Status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $requisition->items->count() }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- View button - always available -->
                                                <a href="{{ route('interbranchrequisition.show', $requisition->Id) }}"
                                                   class="btn btn-view btn-sm"
                                                   data-bs-toggle="tooltip"
                                                   title="View Requisition">
                                                    <i class="bi bi-eye text-white"></i>
                                                </a>
                                                
                                                <!-- Edit button - conditional -->
                                                @if($canEdit)
                                                    <a href="{{ route('interbranchrequisition.edit', $requisition->Id) }}"
                                                       class="btn btn-edit btn-sm"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ $editTooltip }}">
                                                        <i class="bi bi-pencil text-white"></i>
                                                    </a>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-edit btn-sm disabled"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $editTooltip }}"
                                                            onclick="return showCustomError('{{ $editTooltip }}');">
                                                        <i class="bi bi-pencil text-white"></i>
                                                    </button>
                                                @endif
                                                
                                                <!-- Delete button - conditional -->
                                                @if($canDelete)
                                                    <button type="button"
                                                            class="btn btn-delete btn-sm"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $deleteTooltip }}"
                                                            onclick="confirmDelete('{{ $requisition->Id }}', '{{ $requisition->ReqNo }}')">
                                                        <i class="bi bi-trash text-white"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-delete btn-sm disabled"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $deleteTooltip }}"
                                                            onclick="return showCustomError('{{ $deleteTooltip }}');">
                                                        <i class="bi bi-trash text-white"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            <form id="delete-form-{{ $requisition->Id }}"
                                                  action="{{ route('interbranchrequisition.destroy', $requisition->Id) }}"
                                                  method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
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
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        // Initialize DataTables for the active tab
        initializeActiveTabDataTable();
        
        // Re-initialize DataTables when tab changes
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            // Destroy existing DataTable instances
            $('.requisition-table').DataTable().destroy();
            // Initialize DataTable for the newly active tab
            initializeActiveTabDataTable();
            initializeTooltips();
        });
        
        // Initialize tooltips
        function initializeTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        
        // Initialize DataTable for the currently active tab
        function initializeActiveTabDataTable() {
            var activeTable = $('.tab-pane.active .requisition-table');
            if (activeTable.length) {
                activeTable.DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    lengthChange: false,
                    dom: 'rt<"bottom"ip><"clear">',
                    language: {
                        emptyTable: "No requisitions found."
                    },
                    drawCallback: function() {
                        initializeTooltips();
                    }
                });
            }
        }
        
        // Initialize tooltips on page load
        initializeTooltips();
    });

    function confirmDelete(Id, reqNo) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete requisition: " + reqNo + ". This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + Id).submit();
            }
        });
    }

    function showCustomError(message) {
        Swal.fire({
            title: 'Action Not Allowed',
            text: message,
            icon: 'warning',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
        return false;
    }

    function hideCustomError() {
        document.getElementById('customErrorContainer').style.display = 'none';
    }
    
    // Handle filter form submission for specific tabs
    $('#filterForm').on('submit', function(e) {
        // Get current active tab
        var activeTab = $('.nav-link.active').attr('id');
        
        // Store the active tab in sessionStorage to restore after page reload
        if (activeTab) {
            sessionStorage.setItem('activeRequisitionTab', activeTab);
        }
    });
    
    // Restore active tab on page load
    $(document).ready(function() {
        var activeTab = sessionStorage.getItem('activeRequisitionTab');
        if (activeTab) {
            $('#' + activeTab).tab('show');
            sessionStorage.removeItem('activeRequisitionTab'); // Clear after use
        }
    });
</script>

<style>
.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 0.25rem;
    padding: 0.25rem 0.5rem;
    border: none;
    transition: all 0.2s ease-in-out;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Ensure tooltips work properly */
.tooltip {
    pointer-events: none;
}

/* Solid background colors with white icons */
.btn-view {
    background-color: #5b6b79 !important;
    color: white !important;
}

.btn-edit {
    background-color: #e58a00 !important;
    color: white !important;
}

.btn-delete {
    background-color: #dc3545 !important;
    color: white !important;
}

/* Hover effects */
.btn-view:hover {
    background-color: #0b5ed7 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-edit:hover:not(.disabled) {
    background-color: #e0a800 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.btn-delete:hover:not(.disabled) {
    background-color: #c82333 !important;
    color: white !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Disabled state for buttons */
.btn.disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
    pointer-events: auto; /* Allow tooltips on disabled buttons */
}

.btn.disabled:hover {
    background-color: inherit !important;
    transform: none !important;
    box-shadow: none !important;
}

/* Ensure icons are properly sized and white */
.bi {
    font-size: 0.875rem;
    color: white;
}

/* Badge styling */
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

/* Table responsive adjustments */
.table-responsive {
    border-radius: 0.375rem;
}

/* Add Requisition button styling */
.btn-success {
    background-color: #198754;
    border-color: #198754;
}

.btn-success:hover {
    background-color: #157347;
    border-color: #146c43;
}

/* Nav tabs styling */
.nav-tabs .nav-link {
    color: #495057;
    border: 1px solid transparent;
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

.nav-tabs .nav-link:hover {
    border-color: #e9ecef #e9ecef #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
    font-weight: 600;
}

/* Tab-specific row highlighting */
.incoming-row {
    background-color: rgba(13, 110, 253, 0.05) !important;
}

.outgoing-row {
    background-color: rgba(25, 135, 84, 0.05) !important;
}

/* Status-specific styling */
.status-pending {
    background-color: #ffc107;
}

.status-approved {
    background-color: #198754;
}

.status-rejected {
    background-color: #dc3545;
}

.status-funded {
    background-color: #0dcaf0;
}

/* Tab badge styling */
.nav-link .badge {
    font-size: 0.65em;
    padding: 0.25em 0.5em;
}

/* Empty table message */
.dataTables_empty {
    text-align: center;
    padding: 2rem !important;
    color: #6c757d;
}
</style>
@endsection