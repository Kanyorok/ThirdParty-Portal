@php use App\Enums\Inventory\Transfers; @endphp
@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'View Transfers')

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Custom client‑side error --}}
    <div id="customErrorContainer" style="display:none;">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span id="customErrorMessage"></span>
            <button type="button" class="btn-close" aria-label="Close" onclick="hideCustomError()"></button>
        </div>
    </div>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Transferred Goods List</h3>
            <a href="{{ route('transactionstransfers.create') }}" class="btn btn-success">➕ New Transfer</a>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-3" id="transferTabs" role="tablist">
            @if($isHeadOffice)
                <!-- For Head Office - All Transfers Tab -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" 
                            type="button" role="tab" aria-controls="all" aria-selected="true">
                        All Transfers
                        <span class="badge bg-secondary ms-1">{{ $allTransfers->count() }}</span>
                    </button>
                </li>
                <!-- For Head Office - Incoming Tab -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming" 
                            type="button" role="tab" aria-controls="incoming" aria-selected="false">
                        Incoming Transfers
                        <span class="badge bg-primary ms-1">{{ $incomingTransfers->count() }}</span>
                    </button>
                </li>
                <!-- For Head Office - Outgoing Tab -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" 
                            type="button" role="tab" aria-controls="outgoing" aria-selected="false">
                        Outgoing Transfers
                        <span class="badge bg-success ms-1">{{ $outgoingTransfers->count() }}</span>
                    </button>
                </li>
            @else
                <!-- For Non-HQ Branches - Incoming Tab -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming" 
                            type="button" role="tab" aria-controls="incoming" aria-selected="true">
                        Incoming Transfers
                        <span class="badge bg-primary ms-1">{{ $incomingTransfers->count() }}</span>
                    </button>
                </li>
                <!-- For Non-HQ Branches - Outgoing Tab -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" 
                            type="button" role="tab" aria-controls="outgoing" aria-selected="false">
                        Outgoing Transfers
                        <span class="badge bg-success ms-1">{{ $outgoingTransfers->count() }}</span>
                    </button>
                </li>
                <!-- For Non-HQ Branches - All Tab -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" 
                            type="button" role="tab" aria-controls="all" aria-selected="false">
                        All Transfers
                        <span class="badge bg-secondary ms-1">{{ $allTransfers->count() }}</span>
                    </button>
                </li>
            @endif
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="transferTabsContent">
            @if($isHeadOffice)
                <!-- Head Office - All Transfers Tab -->
                <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                    <!-- Information for All Transfers Tab -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <p class="mb-0">
                                    <i>View all transfers across all branches. Use the branch filter to narrow down results.</i>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- HQ Branch Filter -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form id="branchFilterForm" class="row g-3">
                                <div class="col-md-6">
                                    <label for="branchFilter" class="form-label">Filter by Branch</label>
                                    <select class="form-select" id="branchFilter">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->Id }}">{{ $branch->Name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary me-2" onclick="applyBranchFilter()">Apply Filter</button>
                                    <button type="button" class="btn btn-secondary" onclick="clearBranchFilter()">Clear</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle transfer-table" id="allTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Transfer ID</th>
                                        <th>Date</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th>Transferred By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($allTransfers as $i => $transfer)
                                        @php
                                            // For HQ All tab: Determine if transfer is incoming (to HQ) or outgoing (from HQ)
                                            $isIncomingToHQ = $transfer->ToBranch == $currentBranch->Id;
                                            $isOutgoingFromHQ = $transfer->FromBranch == $currentBranch->Id;
                                            
                                            // HQ can edit/delete only outgoing transfers that are pending
                                            $canEdit = $isOutgoingFromHQ && $transfer->Status === 'P';
                                            $canDelete = $isOutgoingFromHQ && $transfer->Status === 'P';
                                            
                                            $editTooltip = $isIncomingToHQ ? 
                                                'HQ cannot edit incoming transfers.' : 
                                                (!$isOutgoingFromHQ ? 'HQ cannot edit transfers between other branches.' : 
                                                ($transfer->Status !== 'P' ? 'Only pending transfers can be edited.' : 'Edit Transfer'));
                                            
                                            $deleteTooltip = $isIncomingToHQ ? 
                                                'HQ cannot delete incoming transfers.' : 
                                                (!$isOutgoingFromHQ ? 'HQ cannot delete transfers between other branches.' : 
                                                ($transfer->Status !== 'P' ? 'Only pending transfers can be deleted.' : 'Delete Transfer'));
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusDescription = $transfer->transferStatus->Description ?? $transfer->Status ?? '-';
                                                    $statusCode = $transfer->transferStatus->Code ?? $transfer->Status;
                                                    
                                                    $transferEnum = \App\Enums\Inventory\Transfers::tryFrom($statusCode);
                                                    $badgeColor = $transferEnum ? $transferEnum->badgeColor() : 'secondary';
                                                    
                                                    if (!$transferEnum) {
                                                        $badgeColor = match(strtolower($statusDescription)) {
                                                            'pending', 'pending approval' => 'warning',
                                                            'in transit', 'transit' => 'primary',
                                                            'delivered', 'completed' => 'success',
                                                            'rejected', 'cancelled' => 'danger',
                                                            'under review', 'reviewing' => 'info',
                                                            'returned' => 'secondary',
                                                            default => 'secondary'
                                                        };
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }}">
                                                    {{ $statusDescription }}
                                                </span>
                                            </td>
                                            <td class="d-flex gap-1">
                                                {{-- View always allowed --}}
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Edit --}}
                                                @if($canEdit)
                                                    <a href="{{ route('transactionstransfers.edit', $transfer->Id) }}"
                                                    class="btn btn-sm btn-warning" title="{{ $editTooltip }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="return showCustomError('{{ $editTooltip }}');"
                                                            title="{{ $editTooltip }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endif

                                                {{-- Delete --}}
                                                @if($canDelete)
                                                    <form id="delete-form-{{ $transfer->Id }}"
                                                        action="{{ route('transactionstransfers.destroy', $transfer->Id) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="return confirmDelete('{{ $transfer->Id }}');"
                                                                title="{{ $deleteTooltip }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="return showCustomError('{{ $deleteTooltip }}');"
                                                            title="{{ $deleteTooltip }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Head Office - Incoming Tab -->
                <div class="tab-pane fade" id="incoming" role="tabpanel" aria-labelledby="incoming-tab">
                    <!-- Information for Incoming to HQ Tab -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <p class="mb-0">
                                   <i> <strong>Incoming to HQ:</strong> Transfers from other branches to HQ (HQ can only view these)</i>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle transfer-table" id="hqIncomingTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Transfer ID</th>
                                        <th>Date</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th>Transferred By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($incomingTransfers as $i => $transfer)
                                        <tr class="incoming-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusDescription = $transfer->transferStatus->Description ?? $transfer->Status ?? '-';
                                                    $statusCode = $transfer->transferStatus->Code ?? $transfer->Status;
                                                    
                                                    $transferEnum = \App\Enums\Inventory\Transfers::tryFrom($statusCode);
                                                    $badgeColor = $transferEnum ? $transferEnum->badgeColor() : 'secondary';
                                                    
                                                    if (!$transferEnum) {
                                                        $badgeColor = match(strtolower($statusDescription)) {
                                                            'pending', 'pending approval' => 'warning',
                                                            'in transit', 'transit' => 'primary',
                                                            'delivered', 'completed' => 'success',
                                                            'rejected', 'cancelled' => 'danger',
                                                            'under review', 'reviewing' => 'info',
                                                            'returned' => 'secondary',
                                                            default => 'secondary'
                                                        };
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }}">
                                                    {{ $statusDescription }}
                                                </span>
                                            </td>
                                            <td class="d-flex gap-1">
                                                {{-- View only for incoming transfers --}}
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Edit disabled for incoming --}}
                                                <button type="button" class="btn btn-sm btn-warning disabled"
                                                        title="Cannot edit incoming transfers">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                {{-- Delete disabled for incoming --}}
                                                <button type="button" class="btn btn-sm btn-danger disabled"
                                                        title="Cannot delete incoming transfers">
                                                    <i class="fas fa-trash"></i>
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
                
                <!-- Head Office - Outgoing Tab -->
                <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
                    <!-- Information for Outgoing from HQ Tab -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <p class="mb-0">
                                    <i><strong>Outgoing from HQ:</strong> Transfers from HQ to other branches (HQ can edit/delete if pending)</i>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle transfer-table" id="hqOutgoingTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Transfer ID</th>
                                        <th>Date</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th>Transferred By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($outgoingTransfers as $i => $transfer)
                                        @php
                                            // HQ can edit/delete only pending outgoing transfers
                                            $canEdit = $transfer->Status === 'P';
                                            $canDelete = $transfer->Status === 'P';
                                            
                                            $editTooltip = $transfer->Status !== 'P' ? 
                                                'Only pending transfers can be edited.' : 'Edit Transfer';
                                            
                                            $deleteTooltip = $transfer->Status !== 'P' ? 
                                                'Only pending transfers can be deleted.' : 'Delete Transfer';
                                        @endphp
                                        <tr class="outgoing-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusDescription = $transfer->transferStatus->Description ?? $transfer->Status ?? '-';
                                                    $statusCode = $transfer->transferStatus->Code ?? $transfer->Status;
                                                    
                                                    $transferEnum = \App\Enums\Inventory\Transfers::tryFrom($statusCode);
                                                    $badgeColor = $transferEnum ? $transferEnum->badgeColor() : 'secondary';
                                                    
                                                    if (!$transferEnum) {
                                                        $badgeColor = match(strtolower($statusDescription)) {
                                                            'pending', 'pending approval' => 'warning',
                                                            'in transit', 'transit' => 'primary',
                                                            'delivered', 'completed' => 'success',
                                                            'rejected', 'cancelled' => 'danger',
                                                            'under review', 'reviewing' => 'info',
                                                            'returned' => 'secondary',
                                                            default => 'secondary'
                                                        };
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }}">
                                                    {{ $statusDescription }}
                                                </span>
                                            </td>
                                            <td class="d-flex gap-1">
                                                {{-- View always allowed --}}
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Edit --}}
                                                @if($canEdit)
                                                    <a href="{{ route('transactionstransfers.edit', $transfer->Id) }}"
                                                    class="btn btn-sm btn-warning" title="{{ $editTooltip }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="return showCustomError('{{ $editTooltip }}');"
                                                            title="{{ $editTooltip }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endif

                                                {{-- Delete --}}
                                                @if($canDelete)
                                                    <form id="delete-form-{{ $transfer->Id }}"
                                                        action="{{ route('transactionstransfers.destroy', $transfer->Id) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="return confirmDelete('{{ $transfer->Id }}');"
                                                                title="{{ $deleteTooltip }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="return showCustomError('{{ $deleteTooltip }}');"
                                                            title="{{ $deleteTooltip }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
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
                    <!-- Information for Incoming Tab -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <p class="mb-0">
                                    <i><strong>Incoming to {{ $currentBranch->Name }}:</strong> Transfers from other branches to {{ $currentBranch->Name }} (View only)</i><br>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle transfer-table" id="incomingTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Transfer ID</th>
                                        <th>Date</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th>Transferred By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($incomingTransfers as $i => $transfer)
                                        <tr class="incoming-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusDescription = $transfer->transferStatus->Description ?? $transfer->Status ?? '-';
                                                    $statusCode = $transfer->transferStatus->Code ?? $transfer->Status;
                                                    
                                                    $transferEnum = \App\Enums\Inventory\Transfers::tryFrom($statusCode);
                                                    $badgeColor = $transferEnum ? $transferEnum->badgeColor() : 'secondary';
                                                    
                                                    if (!$transferEnum) {
                                                        $badgeColor = match(strtolower($statusDescription)) {
                                                            'pending', 'pending approval' => 'warning',
                                                            'in transit', 'transit' => 'primary',
                                                            'delivered', 'completed' => 'success',
                                                            'rejected', 'cancelled' => 'danger',
                                                            'under review', 'reviewing' => 'info',
                                                            'returned' => 'secondary',
                                                            default => 'secondary'
                                                        };
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }}">
                                                    {{ $statusDescription }}
                                                </span>
                                            </td>
                                            <td class="d-flex gap-1">
                                                {{-- View only for incoming transfers --}}
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Edit disabled for incoming --}}
                                                <button type="button" class="btn btn-sm btn-warning disabled"
                                                        title="Cannot edit incoming transfers">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                {{-- Delete disabled for incoming --}}
                                                <button type="button" class="btn btn-sm btn-danger disabled"
                                                        title="Cannot delete incoming transfers">
                                                    <i class="fas fa-trash"></i>
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
                
                <!-- Non-HQ - Outgoing Tab -->
                <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
                    <!-- Information for Outgoing Tab -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <p class="mb-0">
                                    <i><strong>Outgoing from {{ $currentBranch->Name }}:</strong> Transfers from {{ $currentBranch->Name }} to other branches (Edit/Delete if pending)</i>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle transfer-table" id="outgoingTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Transfer ID</th>
                                        <th>Date</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th>Transferred By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($outgoingTransfers as $i => $transfer)
                                        @php
                                            // Non-HQ can edit/delete only pending outgoing transfers
                                            $canEdit = $transfer->Status === 'P';
                                            $canDelete = $transfer->Status === 'P';
                                            
                                            $editTooltip = $transfer->Status !== 'P' ? 
                                                'Only pending transfers can be edited.' : 'Edit Transfer';
                                            
                                            $deleteTooltip = $transfer->Status !== 'P' ? 
                                                'Only pending transfers can be deleted.' : 'Delete Transfer';
                                        @endphp
                                        <tr class="outgoing-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusDescription = $transfer->transferStatus->Description ?? $transfer->Status ?? '-';
                                                    $statusCode = $transfer->transferStatus->Code ?? $transfer->Status;
                                                    
                                                    $transferEnum = \App\Enums\Inventory\Transfers::tryFrom($statusCode);
                                                    $badgeColor = $transferEnum ? $transferEnum->badgeColor() : 'secondary';
                                                    
                                                    if (!$transferEnum) {
                                                        $badgeColor = match(strtolower($statusDescription)) {
                                                            'pending', 'pending approval' => 'warning',
                                                            'in transit', 'transit' => 'primary',
                                                            'delivered', 'completed' => 'success',
                                                            'rejected', 'cancelled' => 'danger',
                                                            'under review', 'reviewing' => 'info',
                                                            'returned' => 'secondary',
                                                            default => 'secondary'
                                                        };
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }}">
                                                    {{ $statusDescription }}
                                                </span>
                                            </td>
                                            <td class="d-flex gap-1">
                                                {{-- View always allowed --}}
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Edit --}}
                                                @if($canEdit)
                                                    <a href="{{ route('transactionstransfers.edit', $transfer->Id) }}"
                                                    class="btn btn-sm btn-warning" title="{{ $editTooltip }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="return showCustomError('{{ $editTooltip }}');"
                                                            title="{{ $editTooltip }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endif

                                                {{-- Delete --}}
                                                @if($canDelete)
                                                    <form id="delete-form-{{ $transfer->Id }}"
                                                        action="{{ route('transactionstransfers.destroy', $transfer->Id) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="return confirmDelete('{{ $transfer->Id }}');"
                                                                title="{{ $deleteTooltip }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="return showCustomError('{{ $deleteTooltip }}');"
                                                            title="{{ $deleteTooltip }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Non-HQ - All Tab -->
                <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                    <!-- Information for All Tab -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <div>
                                <p class="mb-0">
                                    <i><strong>All Transfers:</strong> View all transfers involving {{ $currentBranch->Name }} (as sending or receiving branch)</i><br>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle transfer-table" id="allTable">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Transfer ID</th>
                                        <th>Date</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th>Transferred By</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($allTransfers as $i => $transfer)
                                        @php
                                            // Determine if transfer is incoming or outgoing for non-HQ
                                            $isIncoming = $transfer->ToBranch == $currentBranch->Id;
                                            $isOutgoing = $transfer->FromBranch == $currentBranch->Id;
                                            
                                            // Non-HQ can edit/delete only outgoing transfers that are pending
                                            $canEdit = $isOutgoing && $transfer->Status === 'P';
                                            $canDelete = $isOutgoing && $transfer->Status === 'P';
                                            
                                            $editTooltip = $isIncoming ? 
                                                'Cannot edit incoming transfers.' : 
                                                (!$isOutgoing ? 'Cannot edit transfers where your branch is not involved.' : 
                                                ($transfer->Status !== 'P' ? 'Only pending transfers can be edited.' : 'Edit Transfer'));
                                            
                                            $deleteTooltip = $isIncoming ? 
                                                'Cannot delete incoming transfers.' : 
                                                (!$isOutgoing ? 'Cannot delete transfers where your branch is not involved.' : 
                                                ($transfer->Status !== 'P' ? 'Only pending transfers can be deleted.' : 'Delete Transfer'));
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusDescription = $transfer->transferStatus->Description ?? $transfer->Status ?? '-';
                                                    $statusCode = $transfer->transferStatus->Code ?? $transfer->Status;
                                                    
                                                    $transferEnum = \App\Enums\Inventory\Transfers::tryFrom($statusCode);
                                                    $badgeColor = $transferEnum ? $transferEnum->badgeColor() : 'secondary';
                                                    
                                                    if (!$transferEnum) {
                                                        $badgeColor = match(strtolower($statusDescription)) {
                                                            'pending', 'pending approval' => 'warning',
                                                            'in transit', 'transit' => 'primary',
                                                            'delivered', 'completed' => 'success',
                                                            'rejected', 'cancelled' => 'danger',
                                                            'under review', 'reviewing' => 'info',
                                                            'returned' => 'secondary',
                                                            default => 'secondary'
                                                        };
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }}">
                                                    {{ $statusDescription }}
                                                </span>
                                            </td>
                                            <td class="d-flex gap-1">
                                                {{-- View always allowed --}}
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                {{-- Edit --}}
                                                @if($canEdit)
                                                    <a href="{{ route('transactionstransfers.edit', $transfer->Id) }}"
                                                    class="btn btn-sm btn-warning" title="{{ $editTooltip }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="return showCustomError('{{ $editTooltip }}');"
                                                            title="{{ $editTooltip }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endif

                                                {{-- Delete --}}
                                                @if($canDelete)
                                                    <form id="delete-form-{{ $transfer->Id }}"
                                                        action="{{ route('transactionstransfers.destroy', $transfer->Id) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="return confirmDelete('{{ $transfer->Id }}');"
                                                                title="{{ $deleteTooltip }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="return showCustomError('{{ $deleteTooltip }}');"
                                                            title="{{ $deleteTooltip }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <script>
        $(document).ready(function () {
            // Initialize DataTables for the active tab
            initializeActiveTabDataTable();
            
            // Re-initialize DataTables when tab changes
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                // Destroy existing DataTable instances
                $('.transfer-table').DataTable().destroy();
                // Initialize DataTable for the newly active tab
                initializeActiveTabDataTable();
            });
            
            // Initialize DataTable for the currently active tab
            function initializeActiveTabDataTable() {
                var activeTable = $('.tab-pane.active .transfer-table');
                if (activeTable.length) {
                    activeTable.DataTable({
                        pageLength: 10,
                        ordering: true,
                        order: [[2, 'desc']], // Sort by Date column
                        searching: true,
                        lengthChange: true,
                        dom: 'rt<"bottom"ip><"clear">',
                        language: {
                            emptyTable: "No transfers found.",
                            lengthMenu: "Show _MENU_ entries",
                            search: "Search:",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        }
                    });
                }
            }
            
            // Branch filter function for HQ
            @if($isHeadOffice)
                function applyBranchFilter() {
                    var branchId = $('#branchFilter').val();
                    var table = $('#allTable').DataTable();
                    
                    if (branchId) {
                        // Filter by From Branch OR To Branch
                        $.fn.dataTable.ext.search.push(
                            function(settings, data, dataIndex) {
                                var fromBranch = data[3]; // From Branch column
                                var toBranch = data[4]; // To Branch column
                                return fromBranch.includes(branchId) || toBranch.includes(branchId);
                            }
                        );
                        table.draw();
                        // Remove the filter function after applying
                        $.fn.dataTable.ext.search.pop();
                    } else {
                        table.search('').columns().search('').draw();
                    }
                }
                
                function clearBranchFilter() {
                    $('#branchFilter').val('');
                    var table = $('#allTable').DataTable();
                    table.search('').columns().search('').draw();
                }
            @endif
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to delete this transfer. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
            return false;
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

        /* Information alert styling */
        .alert-info {
            background-color: #e7f1ff;
            border-color: #cfe2ff;
            color: #084298;
        }

        .alert-info .bi-info-circle-fill {
            color: #0d6efd;
        }

        /* Tab-specific row highlighting */
        .incoming-row {
            background-color: rgba(13, 110, 253, 0.05) !important;
        }

        .outgoing-row {
            background-color: rgba(25, 135, 84, 0.05) !important;
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

        /* Tab badge styling */
        .nav-link .badge {
            font-size: 0.65em;
            padding: 0.25em 0.5em;
        }

        /* Disabled button styling */
        .btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
@endsection