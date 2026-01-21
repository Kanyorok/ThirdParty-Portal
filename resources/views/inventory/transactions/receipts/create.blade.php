@extends('layouts.app')

@section('title', 'Create Receipt')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

@section('content')
    <div class="card mb-4">
        <div class="card-header bg-success text-white">📥 Post Goods Receipt</div>
        <div class="card-body">
            <form method="POST" action="{{ route('transactionsreceipts.store') }}" id="transferForm">
                @csrf
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>

                    </div>
                @endif



          <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Transfer Ref <span class="text-danger">*</span></label>
                    <select id="transferId" name="TransferID" class="form-select" required>
                        <option value="">Select Transfer</option>
                        @foreach($transfers as $transfer)
                            <option value="{{ $transfer->Id }}" {{ old('TransferID') == $transfer->Id ? 'selected' : '' }}>
                                {{ $transfer->TransferID }}
                            </option>
                        @endforeach
                    </select>
                    @error('TransferID')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Received By <span class="text-danger">*</span></label>
                    {{-- Hidden field for form submission --}}
                    <input type="hidden" name="ReceivedBy" value="{{ $currentUser->Id ?? auth()->id() }}">
                    {{-- Display-only field for user visibility --}}
                    <input type="text" class="form-control" value="{{ $currentUser->Name ?? auth()->user()->Name }}" readonly>
                    <small class="text-muted">Current user</small>
                    @error('ReceivedBy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col">
                    <label class="form-label">Receive Date <span class="text-danger">*</span></label>
                    {{-- Hidden field for form submission with proper format --}}
                    <input type="hidden" id="receivedDateHidden" name="ReceivedDate" value="{{ now()->format('Y-m-d') }}">
                    {{-- Display-only field for user visibility --}}
                    <input type="text" class="form-control" value="{{ now()->format('m/d/Y') }}" readonly>
                    <small class="text-muted">Current date (non-editable)</small>
                    @error('ReceivedDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

                <div class="mb-3">
                    <label class="form-label">Items Received</label>
                    <table class="table table-bordered">
                        <thead>
            <tr>
                <th>Product</th>
                <th>UOM</th>
                <th>Unit Cost</th>
                <th>Dispatched Qty</th>
                <th>Qty Received <span class="text-danger">*</span></th>
                <th>Discrepancy</th>
                <th>Qty Damaged</th>
                <th>Store <span class="text-danger">*</span></th>
              <th>Remarks</th>
            </tr>
          </thead>
                        <tbody id="itemsTableBody">
                        @if(old('items'))
                            @foreach(old('items') as $index => $item)
                                <tr>
                                    <td>
                                        {{ $item['item_name'] ?? 'Item' }} {{-- Optional: pass ItemName from controller to old input --}}
                                        <input type="hidden" name="items[{{ $index }}][item]"
                                               value="{{ $item['item'] }}">
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][uom]"
                                               class="form-control uom"
                                               value="{{ $item['uom'] ?? '' }}" readonly>
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][unit_cost]"
                                               class="form-control unit-cost"
                                               value="{{ $item['unit_cost'] ?? 0 }}" readonly>
                                    </td>

                                    <td>
                                        <input type="number" name="items[{{ $index }}][dispatched_qty]"
                                               class="form-control dispatched-qty"
                                               value="{{ $item['dispatched_qty'] ?? 0 }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][received_qty]"
                                               class="form-control received-qty"
                                               value="{{ $item['received_qty'] ?? 0 }}" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][discrepancy]"
                                               class="form-control discrepancy"
                                               value="{{ $item['discrepancy'] ?? 0 }}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][damaged_qty]"
                                               class="form-control"
                                               value="{{ $item['damaged_qty'] ?? 0 }}">
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][store_id]" class="form-select" required>
                                            <option value="">-- Select Store --</option>
                                            @if(isset($item['store_options']))
                                                @foreach($item['store_options'] as $store)
                                                    <option
                                                        value="{{ $store['Id'] }}" {{ (string)($item['store_id'] ?? '') === (string)$store['Id'] ? 'selected' : '' }}>
                                                        {{ $store['StoreName'] }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="items[{{ $index }}][remarks]" class="form-control"
                                               value="{{ $item['remarks'] ?? '' }}">
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>

                    </table>
      </div>

      <div class="mb-3">
          <label class="form-label">General Remarks</label>
          <textarea name="GeneralRemarks" class="form-control">{{ old('GeneralRemarks') }}</textarea>
      </div>

                <button type="submit" class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Post Receipt
                </button>

            </form>
  </div>
    </div>

    <script>
    document.getElementById('transferId').addEventListener('change', function () {
        const transferId = this.value;
        if (!transferId) return;

        fetch(`/inventory/transactionsreceipts/transfer-items/${transferId}`)
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('itemsTableBody');
                tableBody.innerHTML = "";

                data.items.forEach((item, index) => {
                    const dispatchedQty = item.DispatchedQty ?? 0;
                    const stores = item.stores ?? [];

                    const row = `
                        <tr>
                            <td>
                                ${item.item?.ItemName ?? 'N/A'}
                                <input type="hidden" name="items[${index}][item]" value="${item.Item ?? item.item?.Id ?? ''}">
                            </td>

                            <td>
                                <input type="text" class="form-control" value="${item.UOMCode ?? ''}" readonly>
                                <input type="hidden" name="items[${index}][uom]" value="${item.UOM ?? ''}">
                            </td>

                            <td>
                                <input type="number" class="form-control" name="items[${index}][unit_cost]" value="${item.UnitCost ?? 0}" readonly>
                                <input type="hidden" name="items[${index}][price_id]" value="${item.PriceID ?? 0}">
                            </td>

                            <td>
                                <input type="number" name="items[${index}][dispatched_qty]" class="form-control dispatched-qty" value="${dispatchedQty}" readonly>
                            </td>

                            <td>
                                <input type="number" name="items[${index}][received_qty]" class="form-control received-qty" min="0" value="${dispatchedQty}" required>
                            </td>

                            <td>
                                <input type="number" name="items[${index}][discrepancy]" class="form-control discrepancy" value="0" readonly>
                            </td>

                            <td>
                                <input type="number" name="items[${index}][damaged_qty]" class="form-control" min="0" value="0">
                            </td>

                            <td>
                                <select name="items[${index}][store_id]" class="form-select" required>
                                    <option value="">-- Select Store --</option>
                                    ${stores.map(store => `<option value="${store.Id}">${store.StoreName}</option>`).join('')}
                                </select>
                            </td>

                            <td>
                                <input type="text" name="items[${index}][remarks]" class="form-control">
                            </td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            })
            .catch(error => {
                console.error("Error fetching transfer data:", error);
            });
    });

    // Recalculate discrepancy when quantity is changed
    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('received-qty')) {
            const row = event.target.closest('tr');
            const dispatchedInput = row.querySelector('.dispatched-qty');
            const discrepancyInput = row.querySelector('.discrepancy');

            const dispatched = parseFloat(dispatchedInput.value) || 0;
            const received = parseFloat(event.target.value) || 0;
            const discrepancy = dispatched - received;

            discrepancyInput.value = discrepancy;
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        $('.select2').select2({
            placeholder: 'Select user',
            allowClear: true
        });
    });
</script>
@endsection
