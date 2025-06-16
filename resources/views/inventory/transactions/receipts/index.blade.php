@extends('layouts.app')

@section('title', 'Goods Receipts')

@section('content')

@php
    use App\Enums\Inventory\Transfers;
@endphp

{{-- Custom Error/Success Message Containers --}}
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

<div class="card mb-4">
    <div class="mb-2 d-flex justify-content-between">
        <a href="{{ route('transactionsreceipts.create') }}" class="btn btn-success">➕ New Receipt</a>
    </div>

    <div class="card-header bg-light">Transfers Receipts</div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead class="table-secondary">
                <tr>
                    <th>#</th>
                    <th>Receipt ID</th>
                    <th>Transfer Ref</th>
                    <th>From</th>
                    <th>Received By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Discrepancy</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $index => $receipt)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $receipt->ReceiptId ?? 'N/A' }}</td>
                        <td>{{ optional($receipt->transfer)->TransferID ?? 'N/A' }}</td>
                        <td>{{ optional($receipt->transfer->fromBranch)->Name ?? 'N/A' }}</td>
                        <td>{{ $receipt->ReceivedBy ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($receipt->ReceivedDate)->format('Y-m-d') }}</td>
                        <td>
                            @php
                                $statusEnum = $receipt->Status instanceof Transfers
                                    ? $receipt->Status
                                    : (Transfers::tryFrom($receipt->Status) ?? null);
                            @endphp
                            @if($statusEnum)
                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                            @else
                                {{-- Default to 'Completed' for display if status is not an enum value,
                                    but 'Pending' for restriction logic below. --}}
                                <span class="badge bg-secondary">{{ $receipt->Status ?? 'Completed' }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $hasDiscrepancy = $receipt->items->some(function ($item) {
                                    $dispatched = optional($item->transferItem)->DispatchedQty ?? 0;
                                    $received = $item->ReceivedQty ?? 0;
                                    return $dispatched !== $received;
                                });
                            @endphp
                            {{ $hasDiscrepancy ? 'Yes' : 'No' }}
                        </td>
                        <td>
                            <a href="{{ route('transactionsreceipts.show', $receipt->Id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('transactionsreceipts.edit', $receipt->Id) }}"
                               class="btn btn-sm btn-warning"
                               onclick="@if($receipt->Status !== 'Pending') return showCustomError('You cannot edit this receipt because a decision has already been made.'); @endif">
                               Edit
                            </a>
                            <a href="#"
                               class="btn btn-sm btn-danger"
                               onclick="@if($receipt->Status !== 'Pending') return showCustomError('You cannot delete this receipt because a decision has already been made.'); @else confirmDelete('{{ $receipt->Id }}'); return false; @endif">
                               Delete
                            </a>
                            <form id="delete-form-{{ $receipt->Id }}"
                                  action="{{ route('transactionsreceipts.destroy', $receipt->Id) }}"
                                  method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No receipts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $receipts->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts') {{-- Using @push('scripts') to include JS at the end of the body --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
     
    });


    function confirmDelete(id) {
        if (confirm('⚠️ Are you sure you want to delete this receipt?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }

   
    function showCustomError(message) {
        document.getElementById('customErrorMessage').innerHTML = message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="hideCustomError()"></button>';
        document.getElementById('customErrorContainer').style.display = 'block';
        window.scrollTo({top: 0, behavior: 'smooth'});
        return false; 
    }

   
    function hideCustomError() {
        document.getElementById('customErrorContainer').style.display = 'none';
    }
</script>
@endpush
