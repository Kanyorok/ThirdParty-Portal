@extends('layouts.app')
@section('title', 'Bid Scoring Consolidation')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Tender {{ $tender->TenderNo ?? 'N/A' }} - Consolidated Scores</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('bidscores.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

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

    @if ($errors->count() > 0)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Evaluation Status Alert --}}
    @if (isset($isEvaluationComplete) && !$isEvaluationComplete)
        <div class="alert alert-warning">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Evaluation Incomplete</strong>
            <p class="mb-1">{{ $evaluatedMembersCount ?? 0 }} of {{ $acceptedMembersCount ?? 0 }} committee members have submitted their evaluations.</p>
            @if (!empty($pendingMembers))
                <small>Pending: {{ implode(', ', $pendingMembers) }}</small>
            @endif
            <p class="mt-2 mb-0"><em>Award cannot be made until all committee members complete their evaluations.</em></p>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6"><strong>Item:</strong> {{ $tender->Title ?? 'N/A' }}</div>
        <div class="col-md-6">
            <strong>Evaluators:</strong> {{ $evaluatorCount }} Committee Members
            @if(!empty($existingAward))
                <span class="badge bg-info ms-2">Awarded to
                    {{ $existingAward->winningSupplier->thirdParty->ThirdPartyName
                        ?? $existingAward->winningSupplier->thirdParty->TradingName
                        ?? $existingAward->winningSupplier->SupplierName
                        ?? ('Supplier #'.$existingAward->WinningSupplierID) }}</span>
            @endif
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Supplier</th>
                    @foreach ($evaluators as $ev)
                        <th>{{ $ev['name'] }}</th>
                    @endforeach
                    <th>Average (out of 100)</th>
                    <th>Rank</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($supplierSummaries as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['supplier_name'] }}</td>
                        @foreach ($evaluators as $ev)
                            <td>{{ number_format($row['evaluator_scores'][$ev['id']] ?? 0, 2) }}</td>
                        @endforeach
                        <td><strong>{{ number_format($row['average'], 2) }}</strong></td>
                        <td>{{ $row['rank'] }}</td>
                        <td>
                            <div class="btn-group mb-1" role="group">
                                <a href="{{ route('bidscores.section-drilldown', $tender->Id) }}?supplier_id={{ $row['supplier_id'] }}" class="btn btn-sm btn-outline-primary" title="Section drilldown for this supplier">
                                    <i class="fas fa-list-alt"></i>
                                </a>
                                <a href="{{ route('bidscores.evaluator-drilldown', $tender->Id) }}?supplier_id={{ $row['supplier_id'] }}" class="btn btn-sm btn-outline-info" title="Evaluator drilldown for this supplier">
                                    <i class="fas fa-user-friends"></i>
                                </a>
                            </div>
                            @if(empty($awardBlocks) || !$awardBlocks)
                                @if(isset($isEvaluationComplete) && !$isEvaluationComplete)
                                    <button type="button" class="btn btn-sm btn-secondary" disabled 
                                            title="All committee members must complete evaluations before awarding">
                                        <i class="fas fa-hourglass-half"></i> Award
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('procawards.store') }}" class="d-inline" onsubmit="return disableSubmitButton(this);">
                                        @csrf
                                        <input type="hidden" name="tender_id" value="{{ $tender->Id }}">
                                        <input type="hidden" name="winning_supplier_id" value="{{ $row['supplier_id'] }}">
                                        <input type="hidden" name="award_justification" value="Awarded based on highest consolidated average score">
                                        <input type="hidden" name="awarded_amount" value="{{ $row['bid_amount'] ?? 0 }}">
                                        <input type="hidden" name="technical_score" value="{{ $row['average'] }}">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-trophy"></i> Award
                                        </button>
                                    </form>
                                @endif
                            @else
                                @if($row['is_awarded'])
                                    <span class="badge bg-success"><i class="fas fa-trophy"></i> Awarded</span>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Tender already awarded">
                                        <i class="fas fa-ban"></i> Award
                                    </button>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 4 + count($evaluators) }}" class="text-center">No evaluation data found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end">
        <form method="POST" action="{{ route('bidscores.consolidate', ['tenderId' => $tender->Id]) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check-double"></i> Consolidate Scores
            </button>
        </form>
    </div>
</div>

<script>
function forwardForAward() {
    if (confirm('Forward this tender for award processing? This will redirect you to the award creation page where you can create the official award.')) {
        const tenderId = {{ $tender->Id }};
        window.location.href = '{{ route('awards.create-from-consolidation', ['tenderId' => ':tenderId']) }}'.replace(':tenderId', tenderId);
    }
}

function rejectAllBids() {
    if (confirm('Are you sure you want to reject all bids? This action cannot be undone.')) {
        // Add your reject all bids logic here
        alert('Feature coming soon: Reject all bids functionality');
    }
}

function disableSubmitButton(form) {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }
    return true;
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>
@endsection

@push('styles')
<style>
    .table thead th {
        font-size: 0.85rem;
        font-weight: 600;
    }
    .badge.fs-6 {
        font-size: 1rem !important;
        padding: 0.5rem;
        border-radius: 50%;
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
