@extends('layouts.app')
@section('title', 'Policy Details')

@section('content')
    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Policy Summary</h5>
                <div class="row">
                    <div class="col-md-4"><strong>Policy No:</strong><br>{{ $policy->PolicyNumber }}</div>
                    <div class="col-md-4"><strong>Customer:</strong><br>{{ $policy->customer->FullName }}</div>
                    <div class="col-md-4"><strong>Insurer:</strong><br>{{ $policy->insurer->Name }}</div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4"><strong>Status:</strong><br><span
                            class="badge bg-success">{{ $policy->Status->label() }}</span></div>
                    <div class="col-md-4"><strong>Start
                            Date:</strong><br>{{ \Carbon\Carbon::parse($policy->PolicyStartDate)->format('d/m/Y') }}
                    </div>
                    <div class="col-md-4"><strong>End
                            Date:</strong><br>{{ \Carbon\Carbon::parse($policy->PolicyEndDate)->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>

        {{-- 💳 Installment Tracker --}}
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Installment Tracker</div>
            <div class="card-body">
                @php
                    $totalPaid = $installments->sum('Amount');
                    $riderPremium = $policy->rideraddon->AdditionalPremium ?? 0;
                    $balance = ($policy->PremiumAmount + $riderPremium) - $totalPaid;
                    $progress = ($policy->PremiumAmount + $riderPremium) > 0
                        ? round(($totalPaid / ($policy->PremiumAmount + $riderPremium)) * 100)
                        : 0;
                @endphp

                <div class="row mb-3">
                    <div class="col-md-4"><strong>Total
                            Premium:</strong><br>{{ number_format($policy->PremiumAmount, 2) }}</div>
                    <div class="col-md-4"><strong>Total
                            Rider
                            AddOns:</strong><br>{{ number_format($policy->rideraddon->AdditionalPremium ?? 0, 2) }}
                    </div>
                    <div class="col-md-4"><strong>Paid So
                            Far:</strong><br>KES {{ number_format($totalPaid, 2) }}</div>
                    <div class="col-md-4"><strong>Balance:</strong><br>KES {{ number_format($balance, 2) }}</div>
                </div>

                <label class="form-label">Payment Progress</label>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" style="width: {{ $progress }}%;">
                        {{ $progress }}%
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4"><strong>Installment
                            Amount:</strong><br>KES {{ number_format($policy->InstallmentAmount ?? 0, 2) }}</div>
                    @php
                        $nextInstallment = $installments->sortBy('NextPaymentDate')->first();
                    @endphp
                    <div class="col-md-4"><strong>Next Due
                            Date:</strong> <br>
                        @if($nextInstallment && $nextInstallment->NextPaymentDate)
                            {{ \Carbon\Carbon::parse($nextInstallment->NextPaymentDate)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="col-md-4">
                        <strong>Frequency:</strong><br>{{ $policy->paymentfrequency->Description ?? '-' }}</div>
                </div>

                @if($progress < 100 && isset($policy->NextInstallmentDueDate) && \Carbon\Carbon::parse($policy->NextInstallmentDueDate)->isPast())
                    <div class="alert alert-warning mt-3">Next installment is overdue.</div>
                @endif

                <a href="{{ route('bancassurance.premiums.create', $policy->Id) }}" class="btn btn-success mt-3">Record
                    Premium Payment</a>
            </div>
        </div>

        {{-- 📄 Payment History --}}
        <div class="card">
            <div class="card-header bg-secondary text-white">Payment History</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>#</th>
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
                            <td>{{$loop->iteration}}</td>
                            <td>{{ \Carbon\Carbon::parse($row->PaymentDate)->format('d/m/Y') }}</td>
                            <td>KES {{ number_format($row->Amount, 2) }}</td>
                            <td>{{ $row->paymentModes->Description }}</td>
                            <td>{{ $row->ReferenceNumber }}</td>
                            <td>{{ $row->CustomerID }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No premium payments found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
