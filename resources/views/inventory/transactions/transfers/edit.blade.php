@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Transfer - {{ $transferitem->TransferID }}</h2>

        <form method="POST" action="{{ route('transactionstransfers.update', $transferitem->Id) }}">
            @csrf
            @method('PUT')

            <input type="hidden" name="RequisitionType" value="{{ $transferitem->RequisitionType }}">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="TransferDate" class="form-label">Transfer Date</label>
                    <input type="date" name="TransferDate" class="form-control"
                           value="{{ old('TransferDate', $transferitem->TransferDate) }}">
                </div>

                <div class="col-md-6">
                    <label for="TransferredBy" class="form-label">Transferred By</label>
                    <select name="TransferredBy" id="TransferredBy" class="form-select select2" required>
                        <option value="">-- Select User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->Id }}"
                                {{ old('TransferredBy', $transferitem->TransferredBy) == $user->Id ? 'selected' : '' }}>
                                {{ $user->Name }}
                            </option>
                        @endforeach
                    </select>
            </div>


                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">From Branch</label>
                        <input type="text" class="form-control" value="{{ $transferitem->fromBranch->Name ?? 'N/A' }}"
                               readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">To Branch</label>
                        <input type="text" class="form-control" value="{{ $transferitem->toBranch->Name ?? 'N/A' }}"
                               readonly>
                    </div>
                </div>

                <h5 class="mb-3">Transferred Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Item Code</th>
                        <th>Approved Qty</th>
                        <th>Dispatched Qty</th>
                        <th>UOM</th>
                        <th>Remarks</th>
                    </tr>
                        </thead>
                        <tbody>
                    @foreach($transferitem->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <input type="hidden" name="RequisitionId" value="{{ $transferitem->RequisitionId }}">
                                <input type="hidden" name="FromBranch" value="{{ $transferitem->FromBranch }}">
                                <input type="hidden" name="ToBranch" value="{{ $transferitem->ToBranch }}">
                                <input type="hidden" name="items[{{ $index }}][item]" value="{{ $item->Item }}">
                                <input type="text" class="form-control" value="{{ $item->item->ItemName ?? 'N/A' }}"
                                       readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{ $item->item->ItemCode ?? 'N/A' }}"
                                       readonly>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][approved_qty]" class="form-control"
                                       value="{{ old("items.$index.approved_qty", $item->ApprovedQty) }}" required>
                            </td>
                            <td>
                                <input type="number" name="items[{{ $index }}][dispatched_qty]" class="form-control"
                                       value="{{ old("items.$index.dispatched_qty", $item->DispatchedQty) }}" required>
                            </td>
                            <td>
                                <input type="text" class="form-control" value="{{ $item->uom->Code ?? 'N/A' }}"
                                       readonly>
                                <input type="hidden" name="items[{{ $index }}][uom]" value="{{ $item->UOM }}">
                            </td>

                            <td>
                                <input type="text" name="items[{{ $index }}][remarks]" class="form-control"
                                       value="{{ old("items.$index.remarks", $item->Remarks) }}">
                            </td>
                        </tr>
                    @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Update Transfer</button>

                <a href="{{ route('transactionstransfers.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
