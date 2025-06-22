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
    <h4 class="mb-4">Select Requisition Type and Number</h4>

    <form class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="requisition_type" class="form-label">Requisition Type</label>
                <select class="form-select" id="requisition_type" required>
                    <option value="">Select Requisition Type</option>
                    <option value="interbranch">InterBranch Requisition</option>
                    <option value="procurement">Procurement Plan Requisition</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="requisition_id" class="form-label">Requisition Number</label>
                <select class="form-select" id="requisition_id" required>
                    <option value="">Select Requisition</option>
                </select>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('transactionstransfers.store') }}" id="transferForm">
        @csrf
        <div id="transferDetails" style="display: none">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="transferDate" class="form-label">Transfer Date</label>
                    <input type="date" class="form-control" id="transferDate" name="TransferDate" required>
                </div>
                <div class="col-md-2">
                    <label for="fromBranch" class="form-label">From Branch</label>
                    <input type="text" class="form-control" id="fromBranch" readonly>
                    <input type="hidden" name="FromBranch" id="FromBranch">
                </div>
                <div class="col-md-2">
                    <label for="toBranch" class="form-label">To Branch</label>
                    <input type="text" class="form-control" id="toBranch" readonly>
                    <input type="hidden" name="ToBranch" id="ToBranch">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transferred By</label>
                    <select name="TransferredBy" class="form-select select2" required>
                        <option value="">-- Select User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->Id }}">{{ $user->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="RequisitionId" id="RequisitionId">
                <input type="hidden" name="RequisitionType" id="RequisitionType">
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
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn">✅ Submit Transfer</button>
        </div>
    </form>
</div>

<script>
    const requisitionTypeSelect = document.getElementById('requisition_type');
    const requisitionIdSelect = document.getElementById('requisition_id');
    const transferDetails = document.getElementById('transferDetails');
    const itemsBody = document.getElementById('itemsBody');

    const fromBranchText = document.getElementById('fromBranch');
    const toBranchText = document.getElementById('toBranch');
    const fromBranchHidden = document.getElementById('FromBranch');
    const toBranchHidden = document.getElementById('ToBranch');
    const requisitionTypeHidden = document.getElementById('RequisitionType');
    const requisitionIdHidden = document.getElementById('RequisitionId');

    let selectedType = '';

    const requisitionsBaseUrl = "{{ url(route('requisitions.by-type', ['type' => 'PLACEHOLDER'])) }}";
    const requisitionDetailsBaseUrl = "{{ url(route('requisitions.details', ['id' => 'PLACEHOLDER'])) }}";

    requisitionTypeSelect.addEventListener('change', function () {
        selectedType = this.value;
        requisitionTypeHidden.value = selectedType;
        requisitionIdSelect.innerHTML = '<option value="">Loading...</option>';

        requisitionIdHidden.value = '';
        itemsBody.innerHTML = '';
        fromBranchText.value = '';
        toBranchText.value = '';
        fromBranchHidden.value = '';
        toBranchHidden.value = '';
        transferDetails.style.display = 'none';

        if (!selectedType) {
            requisitionIdSelect.innerHTML = '<option value="">Select Requisition</option>';
            return;
        }

        const url = requisitionsBaseUrl.replace('PLACEHOLDER', selectedType);

        fetch(url)
            .then(response => response.json())
            .then(data => {
                requisitionIdSelect.innerHTML = '<option value="">Select Requisition</option>';
                data.forEach(req => {
                    const text = selectedType === 'interbranch' ? req.ReqNo : req.RequisitionNo;
                    requisitionIdSelect.innerHTML += `<option value="${req.Id}">${text}</option>`;
                });
            })
            .catch(error => {
                console.error('Error fetching requisitions:', error);
                requisitionIdSelect.innerHTML = '<option value="">Failed to load</option>';
            });
    });

    requisitionIdSelect.addEventListener('change', function () {
        const id = this.value;

        itemsBody.innerHTML = '';
        fromBranchText.value = '';
        toBranchText.value = '';
        fromBranchHidden.value = '';
        toBranchHidden.value = '';
        transferDetails.style.display = 'none';

        if (!id || !selectedType) {
            requisitionIdHidden.value = '';
            return;
        }

        requisitionIdHidden.value = id;

        const detailsUrl = requisitionDetailsBaseUrl.replace('PLACEHOLDER', id) + `?type=${selectedType}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                if (selectedType === 'procurement') {
                    fromBranchText.value = 'Headquarters';
                    fromBranchHidden.value = '{{ \App\Models\Core\Branch::where("IsHQ", 1)->value("Id") ?? "" }}';
                } else {
                    fromBranchText.value = data.from_branch?.Name || 'N/A';
                    fromBranchHidden.value = data.from_branch?.Id || '';
                }

                toBranchText.value = data.to_branch?.Name || 'N/A';
                toBranchHidden.value = data.to_branch?.Id || '';

                itemsBody.innerHTML = '';
                data.items.forEach((item, index) => {
                    const dispatchedQty = item.DispatchedQty ?? item.ApprovedQty;
                    itemsBody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                ${item.ItemName}
                                <input type="hidden" name="items[${index}][item]" value="${item.Item}">
                            </td>
                            <td>${item.ItemCode}</td>
                            <td>
                                <input type="text" class="form-control" value="${item.UOMCode}" readonly>
                                <input type="hidden" name="items[${index}][uom]" value="${item.UOM}">
                            </td>
                            <td><input type="number" class="form-control" name="items[${index}][approved_qty]" value="${item.ApprovedQty}" readonly></td>
                            <td><input type="number" class="form-control" name="items[${index}][dispatched_qty]" value="${dispatchedQty}" min="0" max="${item.ApprovedQty}" required></td>
                            <td><input type="text" class="form-control" name="items[${index}][remarks]" maxlength="255"></td>
                        </tr>
                    `;
                });

                transferDetails.style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading requisition details:', error);
                requisitionIdHidden.value = '';
            });
    });
</script>
@endsection
