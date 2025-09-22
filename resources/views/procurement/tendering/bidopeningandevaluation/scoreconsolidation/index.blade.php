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
        <div class="col-md-6"><strong>Evaluators:</strong> {{ $evaluatorCount }} Committee Members</div>
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
