@php
    use App\Enums\Inventory\Transfers;
    use Carbon\Carbon;

    $statusEnum = $transferitem->Status instanceof Transfers
        ? $transferitem->Status
        : (Transfers::tryFrom($transferitem->Status) ?? null);

    $isPending = $statusEnum && $statusEnum->value === Transfers::Pending->value;
@endphp

@extends('layouts.app')

@section('title', 'Transfer Details')

@section('content')
<div class="container mt-4">
    <div id="customErrorContainer" style="display:none;">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span id="customErrorMessage"></span>
            <button type="button" class="btn-close" aria-label="Close" onclick="hideCustomError()"></button>
        </div>
    </div>

    <h4 class="mb-4">Transfer No. - {{ $transferitem->TransferID }}</h4>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <p><strong>Transfer Date:</strong>
                {{ $transferitem->TransferDate ? Carbon::parse($transferitem->TransferDate)->format('d M Y') : 'N/A' }}
            </p>
            <p><strong>From Branch:</strong> {{ $transferitem->fromBranch->Name ?? 'N/A' }}</p>
            <p><strong>To Branch:</strong> {{ $transferitem->toBranch->Name ?? 'N/A' }}</p>
            <p><strong>Transferred By:</strong> {{ $transferitem->transferredBy->Name ?? 'N/A' }}</p>
            <p><strong>Status:</strong>
                @if($statusEnum)
                    <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                        {{ $statusEnum->label() }}
                    </span>
                @else
                    <span class="badge bg-secondary">{{ $transferitem->Status ?? 'N/A' }}</span>
                @endif
            </p>
        </div>
    </div>

    <h5 class="mb-3 fw-bold">Transferred Items</h5>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Approved Qty</th>
                    <th>Dispatched Qty</th>
                    <th>UOM</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transferitem->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                        <td>{{ $item->ApprovedQty ?? 'N/A' }}</td>
                        <td>{{ $item->DispatchedQty ?? 'N/A' }}</td>
                        <td>{{ $item->uom->Code ?? 'N/A' }}</td>
                        <td>{{ $item->Remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No items found for this transfer.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 d-flex gap-2">
        <a href="{{ route('transactionstransfers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        @if($isPending)
            <a href="{{ route('transactionstransfers.edit', $transferitem->Id) }}"
               class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        @else
            <button type="button" class="btn btn-warning"
                    onclick="return showCustomError('Only pending transfers can be edited.');">
                <i class="fas fa-edit"></i> Edit
            </button>
        @endif

        @if($isPending)
            <form id="delete-form-{{ $transferitem->Id }}"
                  action="{{ route('transactionstransfers.destroy', $transferitem->Id) }}"
                  method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger"
                        onclick="return confirmDelete('{{ $transferitem->Id }}');">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        @else
            <button type="button" class="btn btn-danger"
                    onclick="return showCustomError('Only pending transfers can be deleted.');">
                <i class="fas fa-trash"></i> Delete
            </button>
        @endif
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    function confirmDelete(id) {
        if (confirm('⚠️ Are you sure you want to delete this transfer?')) {
            document.getElementById('delete-form-' + id).submit();
        }
        return false;
    }

    function showCustomError(message) {
        document.getElementById('customErrorMessage').textContent = message;
        document.getElementById('customErrorContainer').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return false;
    }

    function hideCustomError() {
        document.getElementById('customErrorContainer').style.display = 'none';
    }
</script>
@endsection
