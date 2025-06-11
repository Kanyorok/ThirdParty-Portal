@extends('layouts.app')
@section('title', 'View Transfers')
@section('content')
<div class="container bg-white shadow-sm rounded p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Goods Transfer List</h4>
        <a href="{{ route('transactionstransfers.create') }}" class="btn btn-success">➕ New Transfer</a>
    </div>

    <div class="mb-3">
        <input type="text" class="form-control" placeholder="🔍 Search by Store, Date or Transfer ID" id="searchInput">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="transfersTable">
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
                @forelse($transfers as $i => $transfer)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $transfer->TransferID ?? '-' }}</td>
                        <td>{{ $transfer->TransferDate ?? '-' }}</td>
                        
                        <td>{{ optional($transfer->fromBranch)->Name ?? '-' }}</td>
                        <td>{{ optional($transfer->toBranch)->Name ?? '-' }}</td>
                        <td>{{ $transfer->TransferredBy ?? '-' }}</td>
                        <td>
                            @if(isset($transfer->Status))
                                @if($transfer->Status === 'Completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($transfer->Status === 'Pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-secondary">{{ $transfer->Status }}</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('transactionstransfers.show', $transfer->Id) }}" class="btn btn-sm btn-primary">🔍 View</a>
                            <a href="{{ route('transactionstransfers.edit', $transfer->Id) }}" class="btn btn-sm btn-secondary">✏️ Edit</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        let rows = document.querySelectorAll('#transfersTable tbody tr');
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(val) ? '' : 'none';
        });
    });
</script>
@endsection