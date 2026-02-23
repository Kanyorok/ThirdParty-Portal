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

    @php
        $pendingNames = collect($pendingEvaluators ?? [])->pluck('name')->implode(', ');
    @endphp

    @if((($pendingEvaluatorCount ?? 0) > 0) && (empty($awardBlocks) || !$awardBlocks))
        <div class="alert alert-warning">
            <strong>Evaluation pending:</strong> {{ $pendingNames }}.
            Awarding is disabled until all evaluators complete evaluation or are marked as skipped.
        </div>
    @elseif(!empty($canAward) && (empty($awardBlocks) || !$awardBlocks))
        <div class="alert alert-success">
            All evaluator actions are complete. You can proceed with awarding.
        </div>
    @endif

    @if(($evaluators->count() ?? 0) > 0 && (empty($awardBlocks) || !$awardBlocks))
        <div class="card mb-3">
            <div class="card-header fw-bold">Evaluator Status</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Evaluator</th>
                            <th>Status</th>
                            <th style="width: 45%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluators as $ev)
                            <tr>
                                <td>{{ $ev['name'] }}</td>
                                <td>
                                    @if(!empty($ev['is_pending']))
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif(!empty($ev['is_skipped']))
                                        <span class="badge bg-secondary">Skipped</span>
                                    @else
                                        <span class="badge bg-success">Completed</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($ev['is_pending']))
                                        <form method="POST" action="{{ route('bidscores.skip-evaluator', ['tenderId' => $tender->Id, 'memberId' => $ev['id']]) }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="text"
                                                   name="reason"
                                                   class="form-control form-control-sm"
                                                   placeholder="Reason for skipping evaluator (optional)">
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-warning"
                                                    onclick="return confirm('Mark {{ $ev['name'] }} as skipped for this tender?');">
                                                Skip Evaluator
                                            </button>
                                        </form>
                                    @elseif(!empty($ev['is_skipped']) && !empty($ev['skip_reason']))
                                        <small class="text-muted">Reason: {{ $ev['skip_reason'] }}</small>
                                    @else
                                        <small class="text-muted">No action required</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

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
                            @if(!empty($awardBlocks))
                                @if($row['is_awarded'])
                                    <span class="badge bg-success"><i class="fas fa-trophy"></i> Awarded</span>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Tender already awarded">
                                        <i class="fas fa-ban"></i> Award
                                    </button>
                                @endif
                            @elseif(!empty($canAward))
                                <form method="POST" action="{{ route('procawards.store') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="tender_id" value="{{ $tender->Id }}">
                                    <input type="hidden" name="winning_supplier_id" value="{{ $row['supplier_id'] }}">
                                    <input type="hidden" name="award_justification" value="Awarded based on highest consolidated average score">
                                    <input type="hidden" name="technical_score" value="{{ $row['average'] }}">
                                    <input type="hidden" name="total_score" value="{{ $row['average'] }}">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-trophy"></i> Award
                                    </button>
                                </form>
                            @else
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        disabled
                                        title="Evaluation pending for: {{ $pendingNames }}">
                                    <i class="fas fa-hourglass-half"></i> Award
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 5 + count($evaluators) }}" class="text-center">No evaluation data found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end">
        <form method="POST" action="{{ route('bidscores.consolidate', ['tenderId' => $tender->Id]) }}">
            @csrf
            <button type="submit"
                    class="btn btn-primary"
                    {{ (($pendingEvaluatorCount ?? 0) > 0) ? 'disabled' : '' }}
                    title="{{ (($pendingEvaluatorCount ?? 0) > 0) ? ('Evaluation pending for: ' . $pendingNames) : 'Consolidate Scores' }}">
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
