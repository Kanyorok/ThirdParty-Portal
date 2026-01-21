@extends('layouts.app')
@section('title', '📊 Contract Execution Monitor')

@section('content')
    <div class="container mt-4">
        <h4>📊 Contract Execution – CONTRACT/PROC/2025/010</h4>

        <!-- Summary Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">{{ $contract->tender->Title ?? 'Contract' }}</h5>
                <p class="mb-1">Vendor: <strong>{{ $contract->winningSupplier->supplierMaster->party->ThirdPartyName ?? $contract->winningSupplier->supplierMaster->party->TradingName ?? 'N/A' }}</strong></p>
                <p class="mb-1">Contract Period: <strong>{{ $contract->ContractStartDate ? $contract->ContractStartDate->format('d-M-Y') : 'N/A' }} to {{ $contract->ContractEndDate ? $contract->ContractEndDate->format('d-M-Y') : 'N/A' }}</strong></p>
                <p class="mb-2">Contract Value: <strong>{{ $contract->tender->Currency->Code ?? 'KES' }} {{ number_format($contract->ContractValue, 2) }}</strong></p>

                @if($contract->ContractStatus === 'Approved')
                    <form action="{{ route('contracts.lifecycle.execution.submit', $contract->Id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to execute this contract? This marks it as Active.')">
                            <i class="fas fa-check-circle me-1"></i> Confirm Execution / Activate
                        </button>
                    </form>
                @else
                    <span class="badge bg-success">Active / Executed</span>
                @endif
            </div>
        </div>

        <!-- LPOs Issued -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">📄 Linked LPOs</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 text-center">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>LPO No</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Deliveries</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td>LPO/2025/321</td>
                        <td>2025-07-10</td>
                        <td>KES 850,000</td>
                        <td><span class="badge bg-success">Approved</span></td>
                        <td><a href="#" class="btn btn-sm btn-outline-primary">Track</a></td>
                    </tr>
                    <!-- More rows -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Milestones / Deliverables -->
        <div class="card shadow-sm">
            <div class="card-header">📦 Contract Milestones / Deliverables</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0 text-center">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Description</th>
                        <th>Expected Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td>Supply of Executive Desks (Batch 1)</td>
                        <td>2025-07-30</td>
                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                        <td>Awaiting dispatch</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Remaining Office Chairs (Batch 2)</td>
                        <td>2025-08-15</td>
                        <td><span class="badge bg-info text-dark">In Progress</span></td>
                        <td>Partial delivery received</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
