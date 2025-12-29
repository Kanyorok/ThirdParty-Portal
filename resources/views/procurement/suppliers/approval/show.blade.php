@extends('layouts.app')
@section('title', 'Review Supplier')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-primary">Review Supplier Request</h3>
        <a href="{{ route('suppliers-approval.index') }}" class="btn btn-secondary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i> Back to Queue
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <!-- Main Details (Left/Top) -->
        <div class="col-lg-8">
            <div class="card p-4 shadow-sm mb-4">
                <h5 class="fw-bold mb-3">Supplier Information: {{ $supplier->SupplierID }}</h5>
                <!-- Including partial usage or copying table structure for robustness -->
                <table class="table table-striped table-borderless">
                    <tbody>
                        <tr>
                            <td class="fw-bold" style="width: 30%;">Legal Name</td>
                            <td>{{ $supplier->party->ThirdPartyName ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Trading Name</td>
                            <td>{{ $supplier->party->TradingName ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Tax PIN</td>
                            <td>{{ $supplier->party->TaxPIN ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Address</td>
                            <td>{{ $supplier->party->PhysicalAddress ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Email</td>
                            <td>{{ $supplier->party->Email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Phone</td>
                            <td>{{ $supplier->party->Phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Business Type</td>
                            <td>
                                @if(isset($supplier->party->types))
                                {{ $supplier->party->types->pluck('Name')->join(', ') }}
                                @else
                                N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Categories</td>
                            <td>
                                @if($supplier->categories->count() > 0)
                                <ul class="mb-0">
                                    @foreach($supplier->categories as $category)
                                    <li>{{ $category->Name ?? $category->Description ?? 'Category' }}</li>
                                    @endforeach
                                </ul>
                                @else
                                <span class="text-muted fst-italic">No categories assigned</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Workflow History -->
            <div class="card p-4 shadow-sm mb-4">
                <h5 class="fw-bold mb-3">Workflow History</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Action</th>
                                <th>Performed By</th>
                                <th>Timestamp</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $record)
                            <tr>
                                <td>{{ $record->Name }}</td> <!-- Action Name -->
                                <td>{{ $record->creator->Name ?? 'Unknown' }}</td>
                                <td>{{ \Carbon\Carbon::parse($record->CreatedOn)->format('d/m/Y H:i') }}</td>
                                <td>{{ $record->Comments }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No history found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Action Panel (Right/Sidebar) -->
        <div class="col-lg-4">
            <div class="card p-4 shadow-sm border-primary">
                <h5 class="fw-bold mb-3 text-primary">Approval Actions</h5>

                @if($canApprove)
                <div class="d-grid gap-2">
                    <!-- Approve Form -->
                    <form action="{{ route('suppliers-approval.approve', $supplier->SupplierID) }}" method="POST" onsubmit="return confirm('Are you sure you want to APPROVE this supplier?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg w-100 mb-2">
                            <i class="fas fa-check-circle me-2"></i> Approve Supplier
                        </button>
                    </form>

                    <!-- Reject Button (Triggers Modal) -->
                    <button type="button" class="btn btn-danger btn-lg w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times-circle me-2"></i> Reject Supplier
                    </button>
                </div>
                @else
                <div class="alert alert-secondary">
                    <i class="fas fa-info-circle me-2"></i>
                    @if($supplier->ApprovalStatus->name === 'Approved')
                    Supplier is already approved.
                    @else
                    You are not pending for this stage or lack permissions.
                    @endif
                </div>
                @endif
            </div>

            <div class="card p-4 shadow-sm mt-3">
                <h5 class="fw-bold mb-2">Current Status</h5>
                <div class="alert alert-info mb-0">
                    {{ $supplier->ApprovalStatus->name ?? $supplier->ApprovalStatus }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('suppliers-approval.reject', $supplier->SupplierID) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Supplier</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-bold">Reason for Rejection *</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" required minlength="5" placeholder="Please provide a reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection