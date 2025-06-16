@extends('layouts.app')
@section('title', 'View Transfers')
@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Goods Transfer List</h4>
        <a href="{{ route('transactionstransfers.create') }}" class="btn btn-success">➕ New Transfer</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Transfer ID</th>
                    <th>Requisition ID</th>
                    <th>Date</th>
                    <th>From Branch</th>
                    <th>To Branch</th>
                    <th>Transferred By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $i => $transfer)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $transfer->TransferID ?? '-' }}</td>
                        <td>{{ $transfer->RequisitionId ?? '-' }}</td>
                        <td>{{ $transfer->TransferDate ?? '-' }}</td>
                        <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                        <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                        <td>{{ $transfer->TransferredBy ?? '-' }}</td>
                        <td>
                            @php
                                $statusEnum = $transfer->Status instanceof \App\Enums\Inventory\Transfers
                                    ? $transfer->Status
                                    : (\App\Enums\Inventory\Transfers::tryFrom($transfer->Status) ?? null);
                            @endphp

                            @if($statusEnum)
                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">
                                    {{ $statusEnum->label() }}
                                </span>
                            @else
                                <span class="badge bg-secondary">{{ $transfer->Status ?? '-' }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('transactionstransfers.show', $transfer->Id) }}" class="btn btn-sm btn-primary">View</a>
                            <a href="{{ route('transactionstransfers.edit', $transfer->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('transactionstransfers.destroy', $transfer->Id) }}" method="POST" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this transfer?')">Delete</button>
                </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No transfers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</script>
@endsection