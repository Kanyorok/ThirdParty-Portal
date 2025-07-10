@php
    use App\Enums\Inventory\Transfers;
@endphp

@extends('layouts.app')

@section('title', 'Approve Stock Transactions')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
    <div class="container">
        <h4 class="mb-4">Select Transactions to Approve</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <label>Transaction Type</label>
                    <select name="transaction_type" class="form-select" onchange="this.form.submit()">
                        <option value="Stock Transfer" {{ $transactionType == 'Stock Transfer' ? 'selected' : '' }}>
                            Stock Transfer
                        </option>
                        <option value="Stock Adjustment" {{ $transactionType == 'Stock Adjustment' ? 'selected' : '' }}>
                            Stock Adjustment
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>From Branch</label>
                    <input type="text" name="branch" class="form-control" placeholder="Branch ID or Name"
                           value="{{ request('branch') }}">
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

        <div class="table-responsive">
            <table id="approvalsTable" class="table table-bordered table-striped align-middle">
                <thead class="table-light">
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
                    <th>ACTIONS</th>
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
                        <td>{{ \Carbon\Carbon::parse($record->CreatedOn)->format('d/m/Y') }}</td>
                        <td>
                            @if($transactionType == 'Stock Transfer')
                                {{ $record->transferredBy->Name ?? 'N/A'}}
                            @elseif($transactionType == 'Stock Adjustment')
                                {{-- CORRECTED LINE: Only display the user's Name via the relationship --}}
                                {{ $record->adjustedBy->Name ?? 'N/A' }}
                            @else
                                {{ $record->creator->name ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            @if($transactionType == 'Stock Transfer' && $statusEnum)
                                <span class="badge bg-{{ $statusEnum->badgeColor() }}">{{ $statusEnum->label() }}</span>
                            @else
                                {{ $record->Status ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('transactionsapproval.approve', ['Id' => $record->Id, 'transaction_type' => $transactionType]) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('transactionsapproval.reject', ['Id' => $record->Id]) }}">
                                @csrf
                                <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('transactionsapproval.show', ['Id' => $record->Id, 'transaction_type' => $transactionType]) }}"
                               class="btn btn-sm btn-primary">
                                View
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="9" class="text-center">No pending {{ strtolower($transactionType) }}s found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            const table = $('#approvalsTable');
            @if(!$records->isEmpty())
            table.DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                language: {
                    emptyTable: "No records available"
                },
                columnDefs: [
                    {orderable: false, targets: [7, 8]}
                ]
            });
            @endif
        });
    </script>
@endsection
