@extends('layouts.app')
@section('title', 'Policy Details')

@section('content')
<div class="container mt-4">
    {{-- 🔹 Policy Summary --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2 px-3">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i> Policy Summary</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Policy No:</strong><br>{{ $policy->PolicyNumber ?? '-' }}</div>
                <div class="col-md-4"><strong>Customer:</strong><br>{{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}</div>
                <div class="col-md-4"><strong>Insurer:</strong><br>{{ $policy->insurer->Name ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <strong>Status:</strong><br>
                    <span class="badge bg-primary">
                        {{ $policy->Status->label() }}
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>Start Date:</strong><br>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}
                </div>
                <div class="col-md-4">
                    <strong>End Date:</strong><br>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- 💳 Installment Tracker --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2 px-3">
            <h6 class="mb-0"><i class="bi bi-wallet2 me-2"></i> Installment Tracker</h6>
        </div>
        <div class="card-body">
            @php
                $totalPaid = $installments->sum('Amount');
                $riderPremium = $policy->rideraddon->AdditionalPremium ?? 0;
                $totalPremium = $policy->PremiumAmount + $riderPremium;
                $balance = $totalPremium - $totalPaid;
                $progress = $totalPremium > 0 ? round(($totalPaid / $totalPremium) * 100) : 0;
                $nextInstallment = $installments->sortBy('NextPaymentDate')->first();
            @endphp

            <div class="row mb-3">
                <div class="col-md-3"><strong>Total Premium:</strong><br>KES {{ number_format($policy->PremiumAmount, 2) }}</div>
                <div class="col-md-3"><strong>Rider AddOns:</strong><br>KES {{ number_format($riderPremium, 2) }}</div>
                <div class="col-md-3"><strong>Paid So Far:</strong><br>KES {{ number_format($totalPaid, 2) }}</div>
                <div class="col-md-3"><strong>Balance:</strong><br>KES {{ number_format($balance, 2) }}</div>
            </div>

            <label class="form-label fw-semibold">Payment Progress</label>
            <div class="progress mb-4" style="height: 22px;">
                <div class="progress-bar bg-success fw-semibold" role="progressbar" style="width: {{ $progress }}%;">
                    {{ $progress }}%
                </div>
            </div>

            <div class="row">
                <div class="col-md-4"><strong>Installment Amount:</strong><br>{{ number_format($policy->InstallmentAmount ?? 0, 2) }}</div>
                <div class="col-md-4"><strong>Next Due Date:</strong><br>
                    @if($nextInstallment && $nextInstallment->NextPaymentDate)
                        {{ \Carbon\Carbon::parse($nextInstallment->NextPaymentDate)->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </div>
                <div class="col-md-4"><strong>Frequency:</strong><br>{{ $policy->paymentfrequency->Description ?? '-' }}</div>
            </div>

            @if($progress < 100 && isset($policy->NextInstallmentDueDate) && \Carbon\Carbon::parse($policy->NextInstallmentDueDate)->isPast())
                <div class="alert alert-primary mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i> Next installment is overdue.
                </div>
            @endif

            <div class="text-end mt-4">
                <a href="{{ route('bancassurance.premiums.create', $policy->Id) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Record Payment
                </a>
            </div>
        </div>
    </div>

    {{-- 📄 Payment History --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2 px-3">
            <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i> Payment History</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:5%">#</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Reference</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($installments as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->PaymentDate)->format('d/m/Y') }}</td>
                            <td>KES {{ number_format($row->Amount, 2) }}</td>
                            <td>{{ $row->paymentModes->Description ?? '-' }}</td>
                            <td>{{ $row->ReferenceNumber ?? '-' }}</td>
                            <td>{{ $row->CustomerID ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox me-2"></i> No premium payments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
