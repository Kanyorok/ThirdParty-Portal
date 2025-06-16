@php
    use App\Enums\Inventory\Transfers;
@endphp

@extends('layouts.app')

@section('title', 'Approve Stock Transactions')

@section('content')
<div class="container">
    <h4 class="mb-4">Approve Stock Transactions</h4>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label>Transaction Type</label>
                <select name="transaction_type" class="form-select" onchange="this.form.submit()">
                    <option value="Stock Transfer" {{ $transactionType == 'Stock Transfer' ? 'selected' : '' }}>Stock Transfer</option>
                    <option value="Stock Issue" {{ $transactionType == 'Stock Issue' ? 'selected' : '' }}>Stock Issue</option>
                    <option value="Stock Adjustment" {{ $transactionType == 'Stock Adjustment' ? 'selected' : '' }}>Stock Adjustment</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>From Branch</label>
                <input type="text" name="branch" class="form-control" placeholder="Branch ID or Name" value="{{ request('branch') }}">
            </div>
            <div class="col-md-2">
                <label>From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label>To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>TYPE</th>
                <th>REF NO</th>
                <th>BRANCH</th>
                <th>DATE</th>
                <th>INITIATED BY</th>
                <th>STATUS</th>
                <th>APPROVE</th>
                <th>REJECT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
                @php
                    $statusEnum = $record->Status instanceof Transfers
                        ? $record->Status
                        : (Transfers::tryFrom($record->Status) ?? null);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $transactionType }}</td>
                    <td>
                        @if($transactionType == 'Stock Transfer')
                            {{ $record->TransferID ?? 'N/A' }}
                        @elseif($transactionType == 'Stock Issue')
                            {{ $record->IssueID ?? 'N/A' }}
                        @elseif($transactionType == 'Stock Adjustment')
                            {{ $record->AdjustmentId ?? 'N/A' }}
                        @endif
                    </td>
                    <td>
    @if($transactionType == 'Stock Transfer')
        {{ $record->fromBranch->Name ?? 'N/A' }}
    @else
        {{ $record->branch->Name ?? 'N/A' }}
    @endif
</td>

                    <td>{{ \Carbon\Carbon::parse($record->CreatedOn)->format('Y-m-d') }}</td>
                    <td>{{ $record->creator->name ?? 'N/A' }}</td>
                    <td>
                        @if($transactionType == 'Stock Transfer' && $statusEnum)
                            <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                        @else
                            {{ $record->Status ?? 'N/A' }}
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('transactionsapproval.approve', ['Id' => $record->Id, 'transaction_type' => $transactionType]) }}">
                         @csrf
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                        </form>
                    </td>
                    <td>
                        
                        @if($transactionType === 'Stock Transfer' && $statusEnum && $statusEnum->value === 'pe')
                            <form method="POST" action="{{ route('transactionsapproval.reject', $record->Id) }}">
                                @csrf
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @elseif($transactionType !== 'Stock Transfer' && $record->Status === 'Pending')
                            <form method="POST" action="{{ route('transactionsapproval.reject', $record->Id) }}">
                                @csrf
                                <button class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No pending {{ strtolower($transactionType) }}s found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection