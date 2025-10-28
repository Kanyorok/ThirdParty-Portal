<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            padding: 16px;
        }
    </style>
    <title>Consolidated Scores</title>
    @stack('styles')
    @stack('scripts')
</head>
<body>
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-body d-flex align-items-center justify-content-between">
        <div>
            <h6 class="mb-1">Consolidated Scores</h6>
            <div class="text-muted small">RFQ: <strong>{{ $rfq->RFQNumber ?? $rfqId }}</strong> · Evaluators:
                <strong>{{ $evaluatorCount }}</strong></div>
        </div>
        <form class="d-flex align-items-center gap-2" method="GET">
            <input type="hidden" name="embed" value="1">
            <label class="form-label mb-0">Evaluated RFQ</label>
            <select name="rfq" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach($evaluatedRfqs as $opt)
                    <option
                        value="{{ $opt['Id'] }}" {{ (int)request('rfq', $rfqId) === (int)$opt['Id'] ? 'selected' : '' }}>{{ $opt['RFQNumber'] }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="mb-4">
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light text-center align-middle">
            <tr>
                <th rowspan="2">#</th>
                <th rowspan="2">Bidder</th>
                @foreach($sectionColumns as $sec)
                    <th colspan="{{ max(1, count($sec['criteria'])) }}">{{ $sec['name'] }} ({{ $sec['weight'] }}%)</th>
                @endforeach
                <th rowspan="2">Total Weighted Score (%)</th>
                <th rowspan="2">Rank</th>
                <th rowspan="2">Recommendation</th>
                <th rowspan="2">Award</th>
            </tr>
            <tr>
                @foreach($sectionColumns as $sec)
                    @if(count($sec['criteria']))
                        @foreach($sec['criteria'] as $c)
                            <th>{{ $c['name'] }}</th>
                        @endforeach
                    @else
                        <th>-</th>
                    @endif
                @endforeach
            </tr>
            </thead>
            <tbody>
            @forelse ($supplierSummaries as $i => $sup)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $sup['supplier_name'] }}</td>
                    @foreach($sectionColumns as $sec)
                        @if(count($sec['criteria']))
                            @foreach($sec['criteria'] as $c)
                                @php
                                    $score = $supplierCriterionAvgScores[$sup['supplier_id']][$c['id']] ?? 0;
                                @endphp
                                <td>
                                    <div class="d-flex flex-column text-center">
                                        <small>{{ number_format($score, 1) }}/10</small>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-info"
                                                 style="width: {{ min(100, ($score/10)*100) }}%;"></div>
                                        </div>
                                    </div>
                                </td>
                            @endforeach
                        @else
                            <td>-</td>
                        @endif
                    @endforeach
                    <td><strong>{{ number_format($sup['total_weighted_average'], 1) }}%</strong></td>
                    <td>{{ $sup['rank'] }}</td>
                    <td><span
                            class="badge {{ $sup['recommendation']['class'] }}">{{ $sup['recommendation']['status'] }}</span>
                    </td>
                    <td>
                        <form
                            action="{{ route('evaluations.award', ['rfq' => $rfqId, 'supplier' => $sup['supplier_id']]) }}"
                            method="POST">
                            @csrf
                            <input type="hidden" name="Comments" value="Awarded via consolidated view (embedded)">
                            <button
                                class="btn btn-sm {{ ($award && $award->SupplierId == $sup['supplier_id']) ? 'btn-success' : 'btn-outline-primary' }}">
                                {{ ($award && $award->SupplierId == $sup['supplier_id']) ? 'Awarded' : 'Award' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 4 + count($sectionColumns) }}" class="text-center">No data</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mb-4">
    <h6>Criteria Averages</h6>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Criteria</th>
                <th>Average Score (out of 10)</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($criteriaSummary as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['criteria_name'] }}</td>
                    <td>{{ $row['average_score_out_of_10'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No data</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mb-2">
    <h6>Sections</h6>
    <div class="row">
        @foreach ($sections as $sec)
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h6 class="card-title mb-2">{{ $sec['name'] }}</h6>
                        <p class="text-muted">Weight: {{ $sec['weight'] }}%</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>


