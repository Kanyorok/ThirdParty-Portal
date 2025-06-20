@extends('layouts.app')

@section('title', 'Create Transfer')

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
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">Select a Requisition to Transfer</h4>

    <form method="GET" action="{{ route('transactionstransfers.create') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="requisition_id" class="form-label">Requisition Number</label>
                <select class="form-select" id="requisition_id" name="requisition_id" onchange="this.form.submit()" required>
                    <option value="">Select Approved Requisition</option>
                    @foreach($approvedRequisitions as $req)
                        <option value="{{ $req->Id }}" {{ (isset($requisition) && $requisition->Id == $req->Id) ? 'selected' : '' }}>
                            {{ $req->ReqNo }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('transactionstransfers.store') }}" id="transferForm">
        @csrf
        @if(isset($requisition))
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="transferDate" class="form-label">Transfer Date</label>
                <input type="date" class="form-control" id="transferDate" name="TransferDate" required>
            </div>
            <div class="col-md-2">
                <label for="fromBranch" class="form-label">From Branch</label>
                <input type="text" class="form-control" id="fromBranch" value="{{ $requisition->fromBranch->Name ?? '' }}" readonly>
                <input type="hidden" name="FromBranch" value="{{ $requisition->FromBranch }}">
            </div>
            <div class="col-md-2">
                <label for="toBranch" class="form-label">To Branch</label>
                <input type="text" class="form-control" id="toBranch" value="{{ $requisition->toBranch->Name ?? '' }}" readonly>
                <input type="hidden" name="ToBranch" value="{{ $requisition->ToBranch }}">
            </div>

            <div class="col-md-4">
                <label for="transferredBy" class="form-label">Transferred By</label>
                <input type="text" class="form-control" id="transferredBy" name="TransferredBy" value="{{ old('TransferredBy', Auth::user()->name ?? '') }}"> {{-- Pre-fill with current user's name --}}
            </div>
            <input type="hidden" name="RequisitionId" value="{{ $requisition->Id }}">
        </div>

        <div id="itemsSection">
            <div class="mb-3">
                <h5>Requisition Items</h5>
                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Item Code</th>
                            <th>UOM</th>
                            <th>Approved Qty</th>
                            <th>Dispatched Qty</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requisition->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $item->item->ItemName ?? 'N/A' }}
                                <input type="hidden" name="items[{{ $index }}][item]" value="{{ $item->Item }}">
                            </td>
                            <td>
                                {{-- Display Item Code (assuming it's loaded with item relation) --}}
                                {{ $item->item->ItemCode ?? 'N/A' }}
                            </td>
                              <td>
                                
                                    {{-- Display UOM Code --}}
                                    {{ $item->item->uom->Code ?? 'N/A' }}

                                    {{-- Save UOM ID in hidden input --}}
                                    <input type="hidden" name="items[{{ $index }}][uom]" value="{{ $item->item->UOM ?? '' }}">
                                </td>

                            <td>
                                <input type="number" class="form-control" name="items[{{ $index }}][approved_qty]" value="{{ $item->ApprovedQty }}" min="1" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control" name="items[{{ $index }}][dispatched_qty]" value="{{ old('items.'.$index.'.dispatched_qty', $item->ApprovedQty) }}" min="0" max="{{ $item->ApprovedQty }}" required> {{-- Default to approved qty, cap at approved qty --}}
                            </td>
                            <td>
                                <input type="text" class="form-control" name="items[{{ $index }}][remarks]" value="{{ old('items.'.$index.'.remarks', $item->Remarks ?? '') }}" maxlength="255">
                            </td>
                        </tr>
                        @endforeach
                        @if(count($requisition->items) == 0)
                        <tr>
                            <td colspan="7" class="text-center">No items found for this requisition.</td> {{-- Adjusted colspan --}}
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @if(isset($requisition))
        <button type="submit" class="btn btn-primary" id="submitBtn">✅ Submit Transfer</button>
        @else
        <button type="submit" class="btn btn-primary d-none" id="submitBtn" disabled>✅ Submit Transfer</button>
        @endif
    </form>
</div>
@endsection
