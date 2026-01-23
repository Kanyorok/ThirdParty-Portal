@extends('layouts.app')
@section('title', '📄 View Contract Details')

@section('content')
    <div class="container mt-4">
        <h4>📄 Contract Details – ID: {{ $contract->Id }}</h4>

        <div class="card">
            <div class="card-body">
                <p><strong>Ref No:</strong> {{ $contract->ContractNo ?? 'CONTRACT/PROC/2025/00' . $contract->Id }}</p>
                <p><strong>Vendor:</strong> {{ $contract->winningSupplier->supplierMaster->party->ThirdPartyName ?? $contract->winningSupplier->supplierMaster->party->TradingName ?? 'N/A' }}</p>
                <p><strong>Status:</strong> <span class="badge bg-{{ ($contract->ContractStatus === 'Active' || $contract->ContractStatus === 'Executed') ? 'success' : 'secondary' }}">{{ $contract->ContractStatus ?? 'N/A' }}</span></p>
                <p><strong>Period:</strong> {{ $contract->ContractStartDate ?? 'N/A' }} to {{ $contract->ContractEndDate ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
@endsection
