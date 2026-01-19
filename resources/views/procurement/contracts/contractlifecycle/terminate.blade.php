@extends('layouts.app')
@section('title', '🚫 Terminate Contract')

@section('content')
    <div class="container mt-4">
        <h4>🚫 Terminate Contract – {{ $contract->ContractNo ?? 'Contract #' . $contract->id }}</h4>

        <!-- Summary -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5>📦 {{ $contract->tender->TenderTitle ?? 'Contract' }}</h5>
                <p class="mb-1">Vendor: <strong>{{ $contract->winningSupplier->supplier_name ?? 'N/A' }}</strong></p>
                <!-- formatting dates if they exist -->
                <p class="mb-1">Period: <strong>{{ $contract->ContractStartDate ?? 'N/A' }} to {{ $contract->ContractEndDate ?? 'N/A' }}</strong></p>
                <p>Status: <span class="badge bg-{{ ($contract->ContractStatus === 'Active' || $contract->ContractStatus === 'Executed') ? 'success' : 'secondary' }}">{{ $contract->ContractStatus }}</span></p>
            </div>
        </div>

        <!-- Termination Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-danger text-white">⚠️ Termination Details</div>
            <div class="card-body">
                <form action="{{ route('contracts.lifecycle.terminate.submit', $contract->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="termination_reason" class="form-label">Reason for Termination <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="termination_reason" name="termination_reason" rows="4" required placeholder="Please provide a detailed reason for terminating this contract..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="termination_date" class="form-label">Termination Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="termination_date" name="termination_date" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label for="settlement_details" class="form-label">Settlement Details (Optional)</label>
                        <textarea class="form-control" id="settlement_details" name="settlement_details" rows="3" placeholder="Describe any financial or legal settlements agreed upon..."></textarea>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Warning: This action is irreversible. The contract status will be updated to "Terminated".
                    </div>

                    <div class="d-flex justify-content-between">
                        <!-- Try to go back to view if possible, else index -->
                        <a href="{{ route('contracts.lifecycle.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Confirm Termination</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
