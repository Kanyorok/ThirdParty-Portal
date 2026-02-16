@php use App\Enums\Inventory\Transfers; @endphp
@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'View Transfers')

@section('content')
@if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

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

        <ul class="nav nav-tabs mb-3" id="transferTabs" role="tablist">
            @if($isHeadOffice)
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" 
                            type="button" role="tab" aria-controls="all" aria-selected="true">
                        All Transfers
                        <span class="badge bg-secondary ms-1">{{ $allTransfers->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming" 
                            type="button" role="tab" aria-controls="incoming" aria-selected="false">
                        Incoming Transfers
                        <span class="badge bg-primary ms-1">{{ $incomingTransfers->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" 
                            type="button" role="tab" aria-controls="outgoing" aria-selected="false">
                        Outgoing Transfers
                        <span class="badge bg-success ms-1">{{ $outgoingTransfers->count() }}</span>
                    </button>
                </li>
            @else
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="incoming-tab" data-bs-toggle="tab" data-bs-target="#incoming" 
                            type="button" role="tab" aria-controls="incoming" aria-selected="true">
                        Incoming Transfers
                        <span class="badge bg-primary ms-1">{{ $incomingTransfers->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="outgoing-tab" data-bs-toggle="tab" data-bs-target="#outgoing" 
                            type="button" role="tab" aria-controls="outgoing" aria-selected="false">
                        Outgoing Transfers
                        <span class="badge bg-success ms-1">{{ $outgoingTransfers->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" 
                            type="button" role="tab" aria-controls="all" aria-selected="false">
                        All Transfers
                        <span class="badge bg-secondary ms-1">{{ $allTransfers->count() }}</span>
                    </button>
                </li>
            @endif
        </ul>

        <div class="tab-content" id="transferTabsContent">
            @if($isHeadOffice)
                <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
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
                                            $statusEnum = $transfer->Status instanceof Transfers
                                                ? $transfer->Status
                                                : (Transfers::tryFrom($transfer->Status) ?? null);
                                            
                                            $isIncomingToHQ = $transfer->ToBranch == $currentBranch->Id;
                                            $isOutgoingFromHQ = $transfer->FromBranch == $currentBranch->Id;
                                            
                                            $isPending = $statusEnum && $statusEnum->value === Transfers::Pending->value;
                                            $canEdit = $isOutgoingFromHQ && $isPending;
                                            $canDelete = $isOutgoingFromHQ && $isPending;
                                            
                                            $editTooltip = $isIncomingToHQ ? 
                                                'HQ cannot edit incoming transfers.' : 
                                                (!$isOutgoingFromHQ ? 'HQ cannot edit transfers between other branches.' : 
                                                (!$isPending ? 'Only pending transfers can be edited.' : 'Edit Transfer'));
                                            
                                            $deleteTooltip = $isIncomingToHQ ? 
                                                'HQ cannot delete incoming transfers.' : 
                                                (!$isOutgoingFromHQ ? 'HQ cannot delete transfers between other branches.' : 
                                                (!$isPending ? 'Only pending transfers can be deleted.' : 'Delete Transfer'));
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @if($statusEnum)
                                                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                                        {{ $statusEnum->label() }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $transfer->Status ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td class="d-flex gap-1">
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

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
                
                <div class="tab-pane fade" id="incoming" role="tabpanel" aria-labelledby="incoming-tab">
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
                                        @php
                                            $statusEnum = $transfer->Status instanceof Transfers
                                                ? $transfer->Status
                                                : (Transfers::tryFrom($transfer->Status) ?? null);
                                        @endphp
                                        <tr class="incoming-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @if($statusEnum)
                                                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                                        {{ $statusEnum->label() }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $transfer->Status ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td class="d-flex gap-1">
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <button type="button" class="btn btn-sm btn-warning disabled"
                                                        title="Cannot edit incoming transfers">
                                                    <i class="fas fa-edit"></i>
                                                </button>

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
                
                <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
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
                                            $statusEnum = $transfer->Status instanceof Transfers
                                                ? $transfer->Status
                                                : (Transfers::tryFrom($transfer->Status) ?? null);
                                            
                                            $isPending = $statusEnum && $statusEnum->value === Transfers::Pending->value;
                                            $canEdit = $isPending;
                                            $canDelete = $isPending;
                                            
                                            $editTooltip = !$isPending ? 'Only pending transfers can be edited.' : 'Edit Transfer';
                                            $deleteTooltip = !$isPending ? 'Only pending transfers can be deleted.' : 'Delete Transfer';
                                        @endphp
                                        <tr class="outgoing-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @if($statusEnum)
                                                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                                        {{ $statusEnum->label() }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $transfer->Status ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td class="d-flex gap-1">
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

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
                <div class="tab-pane fade show active" id="incoming" role="tabpanel" aria-labelledby="incoming-tab">
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
                                        @php
                                            $statusEnum = $transfer->Status instanceof Transfers
                                                ? $transfer->Status
                                                : (Transfers::tryFrom($transfer->Status) ?? null);
                                        @endphp
                                        <tr class="incoming-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @if($statusEnum)
                                                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                                        {{ $statusEnum->label() }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $transfer->Status ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td class="d-flex gap-1">
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <button type="button" class="btn btn-sm btn-warning disabled"
                                                        title="Cannot edit incoming transfers">
                                                    <i class="fas fa-edit"></i>
                                                </button>

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
                
                <div class="tab-pane fade" id="outgoing" role="tabpanel" aria-labelledby="outgoing-tab">
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
                                            $statusEnum = $transfer->Status instanceof Transfers
                                                ? $transfer->Status
                                                : (Transfers::tryFrom($transfer->Status) ?? null);
                                            
                                            $isPending = $statusEnum && $statusEnum->value === Transfers::Pending->value;
                                            $canEdit = $isPending;
                                            $canDelete = $isPending;
                                            
                                            $editTooltip = !$isPending ? 'Only pending transfers can be edited.' : 'Edit Transfer';
                                            $deleteTooltip = !$isPending ? 'Only pending transfers can be deleted.' : 'Delete Transfer';
                                        @endphp
                                        <tr class="outgoing-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @if($statusEnum)
                                                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                                        {{ $statusEnum->label() }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $transfer->Status ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td class="d-flex gap-1">
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

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
                
                <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
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
                                            $statusEnum = $transfer->Status instanceof Transfers
                                                ? $transfer->Status
                                                : (Transfers::tryFrom($transfer->Status) ?? null);
                                            
                                            $isPending = $statusEnum && $statusEnum->value === Transfers::Pending->value;
                                            
                                            $isIncoming = $transfer->ToBranch == $currentBranch->Id;
                                            $isOutgoing = $transfer->FromBranch == $currentBranch->Id;
                                            
                                            $canEdit = $isOutgoing && $isPending;
                                            $canDelete = $isOutgoing && $isPending;
                                            
                                            $editTooltip = $isIncoming ? 
                                                'Cannot edit incoming transfers.' : 
                                                (!$isOutgoing ? 'Cannot edit transfers where your branch is not involved.' : 
                                                (!$isPending ? 'Only pending transfers can be edited.' : 'Edit Transfer'));
                                            
                                            $deleteTooltip = $isIncoming ? 
                                                'Cannot delete incoming transfers.' : 
                                                (!$isOutgoing ? 'Cannot delete transfers where your branch is not involved.' : 
                                                (!$isPending ? 'Only pending transfers can be deleted.' : 'Delete Transfer'));
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $transfer->TransferID ?? '-' }}</td>
                                            <td>{{ Carbon::parse($transfer->TransferDate)->format('d M Y') }}</td>
                                            <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                                            <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                                            <td>{{ $transfer->transferredBy->Name ?? 'N/A' }}</td>
                                            <td>
                                                @if($statusEnum)
                                                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                                        {{ $statusEnum->label() }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $transfer->Status ?? 'N/A' }}</span>
                                                @endif
                                            </td>
                                            <td class="d-flex gap-1">
                                                <a href="{{ route('transactionstransfers.show', $transfer->Id) }}"
                                                   class="btn btn-sm btn-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>

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

    <style>
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }
        
        .dataTables_wrapper .dataTables_length select {
            margin: 0 0.5rem;
            padding: 0.25rem 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0.5rem;
            padding: 0.25rem 0.5rem;
        }
        
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
        
        .alert-info {
            background-color: #e7f1ff;
            border-color: #cfe2ff;
            color: #084298;
        }
        
        .alert-info .bi-info-circle-fill {
            color: #0d6efd;
        }
        
        .incoming-row {
            background-color: rgba(13, 110, 253, 0.05) !important;
        }
        
        .outgoing-row {
            background-color: rgba(25, 135, 84, 0.05) !important;
        }
        
        .badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }
        
        .nav-link .badge {
            font-size: 0.65em;
            padding: 0.25em 0.5em;
        }
        
        .btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function () {
            initializeActiveTabDataTable();
            
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $('.transfer-table').DataTable().destroy();
                initializeActiveTabDataTable();
            });
            
            function initializeTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            
            function initializeActiveTabDataTable() {
                var activeTable = $('.tab-pane.active .transfer-table');
                if (activeTable.length) {
                    activeTable.DataTable({
                        pageLength: 10, 
                        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]], 
                        ordering: true,
                        order: [[2, 'desc']], 
                        searching: true,
                        lengthChange: true, 
                        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>', // Layout with length menu on left
                        language: {
                            emptyTable: "No transfers found.",
                            lengthMenu: "Show _MENU_ entries",
                            search: "Search:",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            infoEmpty: "Showing 0 to 0 of 0 entries",
                            infoFiltered: "(filtered from _MAX_ total entries)",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        },
                        initComplete: function() {
                            initializeTooltips();
                        },
                        drawCallback: function() {
                            initializeTooltips();
                        },
                        responsive: true
                    });
                    
                    $('.dataTables_length').addClass('mb-2');
                    $('.dataTables_filter').addClass('mb-2');
                }
            }
            
            initializeTooltips();
            
            @if($isHeadOffice)
                function applyBranchFilter() {
                    var branchId = $('#branchFilter').val();
                    var table = $('.tab-pane.active #allTable').DataTable();
                    
                    if (branchId) {
                        var branchName = $('#branchFilter option:selected').text();
                        
                        table.search('').draw();
                        
                        table.columns([3, 4]).search(branchName).draw();
                    } else {
                        table.search('').columns([3, 4]).search('').draw();
                    }
                }
                
                function clearBranchFilter() {
                    $('#branchFilter').val('');
                    var table = $('.tab-pane.active #allTable').DataTable();
                    table.search('').columns([3, 4]).search('').draw();
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
@endsection