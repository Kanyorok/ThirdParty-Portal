@extends('layouts.app')
@section('title', 'Approve Inter-Branch Requisition')
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

<div class="container mt-4">
    <h3>Inter-Branch Requisition Approval</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('interbranchrequisitionapproval.index') }}" id="requisition-selection-form">
        <div class="mb-3">
            <label for="ReqId" class="form-label">Select Pending Requisition</label>
            <select name="ReqId" id="ReqId" class="form-select" onchange="this.form.submit()">
                <option value="">-- Choose Requisition To Approve --</option>
                @foreach($pendingRequisitions as $requisitionOption)
                    <option
                        value="{{ $requisitionOption->Id }}" {{ old('ReqId', request()->ReqId) == $requisitionOption->Id ? 'selected' : '' }}>
                        {{ $requisitionOption->ReqNo }} ({{ $requisitionOption->fromBranch?->Name ?? '?' }}
                        → {{ $requisitionOption->toBranch?->Name ?? '?' }})
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <div id="requisition-details" class="mt-4">
        @if(isset($requisition) && $requisition)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Requisition Details</h4>
                <span
                    class="badge bg-primary">Logged in as: {{ Auth::user()->role() ? Auth::user()->role()->name : 'Unknown User' }}</span>
            </div>
            <!-- Requisition Summary -->
            <div class="row mb-4 bg-light p-3 border rounded">
                <div class="col-md-4"><strong>Requisition No.:</strong> {{ $requisition->ReqNo ?? 'N/A' }}</div>
                <div class="col-md-4">
                    <strong>Date:</strong> {{ $requisition->CreatedOn ? $requisition->CreatedOn->format('Y-m-d') : 'N/A' }}
                </div>
                <div class="col-md-4"><strong>From Branch:</strong> {{ $requisition->fromBranch->Name ?? '-' }}</div>
                <div class="col-md-4"><strong>To Branch:</strong> {{ $requisition->toBranch->Name ?? '-' }}</div>
                <div class="col-md-4"><strong>Status:</strong>
                    @php
                        $statusEnum = \App\Enums\Inventory\InterBranchRequisitionEnum::tryFrom($requisition->Status);
                    @endphp
                    @if($statusEnum)
                        <span
                            class="badge bg-{{ $statusEnum->badgeColor() }}{{ $statusEnum->badgeColor() === 'warning' ? ' text-dark' : ' text-light' }}">{{ $statusEnum->label() }}</span>
                    @else
                        <span class="badge bg-secondary">{{ $requisition->Status }}</span>
                    @endif
                </div>
                <div class="col-md-4"><strong>Requested By:</strong> {{ $requisition->creator->Name?? '-' }}</div>
            </div>

            <!-- Requisition Items Table -->
            <div class="mb-4">
                <h5>Requested Items</h5>
                @if($requisition->items && $requisition->items->isNotEmpty())
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>UOM</th>
                            <th>Requested Qty</th>
                            <th>Approved Qty</th>
                            <th>Remarks</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($requisition->items as $index => $requisitionItem)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $requisitionItem->item?->ItemName ?? 'N/A' }}</td>
                                <td>{{ $requisitionItem->uom?->Code ?? 'N/A' }}</td>
                                <td>{{ $requisitionItem->RequestedQty }}</td>
                                <td>
                                    <input type="number" min="0" name="approved_qty[{{ $requisitionItem->Id }}]"
                                           class="form-control"
                                           value="{{ old('approved_qty.' . $requisitionItem->Id, $requisitionItem->RequestedQty) }}"
                                           form="approval-form">
                                </td>
                                <td>
                                    <input type="text" name="item_remarks[{{ $requisitionItem->Id }}]"
                                           class="form-control"
                                           value="{{ old('item_remarks.' . $requisitionItem->Id, $requisitionItem->Remarks) }}"
                                           placeholder="Optional remarks" form="approval-form">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning">No items available for this requisition.</div>
                @endif
            </div>

            <!-- Approval Form -->
            <div class="card p-4 shadow-sm border rounded">
                <h5>Approval Decision</h5>
                <form method="POST" action="{{ route('interbranchrequisitionapproval.submit') }}" id="approval-form">
                    @csrf
                    <input type="hidden" name="ReqId" value="{{ $requisition->Id }}">

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="comments" rows="3" class="form-control">{{ old('comments') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select" required>
                            <option value="">-- Choose Action --</option>
                            <option value="APPROVED">Approve</option>
                            <option value="REJECTED">Reject</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        @else
            <div class="alert alert-info">Please select a requisition to view details and approve.</div>
        @endif
    </div>
</div>
@endsection
