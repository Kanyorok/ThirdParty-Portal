@php
    use App\Enums\Inventory\Transfers;
    use Carbon\Carbon;
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

        @if(session('fail'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('fail') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Transaction Type</label>
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
                    <label class="form-label">Search Branch</label>
                    <input type="text" name="branch" class="form-control" placeholder="Branch ID or Name"
                           value="{{ request('branch') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
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
                    <th>FROM BRANCH</th>
                    <th>TO BRANCH</th>
                    <th>DATE</th>
                    <th>INITIATED BY</th>
                    <th>STATUS</th>
                    <th>APPROVE</th>
                    <th>ACTIONS</th>
                </tr>
                </thead>
                <tbody>
                @forelse($records as $index => $record)
                    @php
                        $statusEnum = Transfers::tryFrom($record->Status) ?? null;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $transactionType }}</td>
                        <td>
                            @if($transactionType == 'Stock Transfer')
                                {{ $record->TransferID ?? 'N/A' }}
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
                        <td>
                            @if($transactionType == 'Stock Transfer')
                                {{ $record->toBranch->Name ?? 'N/A' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ Carbon::parse($record->CreatedOn)->format('d M Y H:i') }}</td>
                        <td>
                            @if($transactionType == 'Stock Transfer')
                                {{ $record->creator->name ?? $record->creator->Name ?? 'N/A' }}
                            @else
                                {{ $record->creator->name ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            @if($statusEnum)
                                @php
                                    $statusDisplay = $statusEnum->label();
                                    $badgeColor = $statusEnum->badgeColor();
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">{{ $statusDisplay }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $record->Status ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('transactionsapproval.approve', ['Id' => $record->Id]) }}"
                                  onsubmit="return confirm('Are you sure you want to approve this {{ strtolower($transactionType) }}?')">
                                @csrf
                                <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                                <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('transactionsapproval.show', ['Id' => $record->Id, 'transaction_type' => $transactionType]) }}"
                                   class="btn btn-sm btn-primary" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal{{ $record->Id }}"
                                        title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Reject Modal -->
                    <div class="modal fade" id="rejectModal{{ $record->Id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject {{ $transactionType }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('transactionsapproval.reject', ['Id' => $record->Id]) }}">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="transaction_type" value="{{ $transactionType }}">
                                        <div class="mb-3">
                                            <label class="form-label">Reason for Rejection</label>
                                            <textarea name="reason" class="form-control" rows="4" 
                                                      placeholder="Please provide a reason for rejecting this {{ strtolower($transactionType) }}..."
                                                      required></textarea>
                                            <div class="form-text">Maximum 2000 characters.</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No pending {{ strtolower($transactionType) }}s found for your branch.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
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
                    emptyTable: "No records available",
                    search: "Search records:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                columnDefs: [
                    {orderable: false, targets: [8, 9]} // Action columns
                ],
                order: [[5, 'desc']] // Default order by date descending
            });
            @endif
        });
    </script>
@endsection