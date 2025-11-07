@extends('layouts.app')

@section('title', 'RFQ Consolidated Scores')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>RFQ {{ $rfq->RFQNumber ?? $rfqId }} - Consolidated Scores</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('evaluations.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-suppliers" type="button"
                        role="tab">Suppliers
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-criteria" type="button" role="tab">
                    Criteria Averages
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sections" type="button" role="tab">
                    Sections
                </button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 p-3">
            <div class="tab-pane fade show active" id="tab-suppliers" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Consolidated Supplier Scores</h6>
                    <form class="d-flex" method="GET">
                        <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" {{ request('filter','all')==='all' ? 'selected' : '' }}>All Responses
                            </option>
                            <option value="evaluated" {{ request('filter')==='evaluated' ? 'selected' : '' }}>Only
                                Evaluated
                            </option>
                        </select>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Supplier</th>
                            @foreach ($sections as $sec)
                                <th>{{ $sec['name'] }} ({{ $sec['weight'] }}%)</th>
                            @endforeach
                            <th>Total Weighted Avg (%)</th>
                            <th>Award</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($supplierSummaries as $i => $sup)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $sup['supplier_name'] }}</td>
                                @foreach ($sections as $sec)
                                    @php
                                        $found = collect($sup['section_scores'])->firstWhere('section_id', $sec['id']);
                                    @endphp
                                    <td>{{ $found['score'] ?? 0 }}%</td>
                                @endforeach
                                <td><strong>{{ $sup['total_weighted_average'] }}%</strong></td>
                                <td>
                                    <form
                                        action="{{ route('evaluations.award', ['rfq' => $rfqId, 'supplier' => $sup['supplier_id']]) }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="Comments" value="Awarded via consolidated view">
                                        <button
                                            class="btn btn-sm {{ ($award && $award->SupplierId == $sup['supplier_id']) ? 'btn-success' : 'btn-outline-primary' }}">
                                            {{ ($award && $award->SupplierId == $sup['supplier_id']) ? 'Awarded' : 'Award' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + count($sections) }}" class="text-center">No data</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-criteria" role="tabpanel">
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

            <div class="tab-pane fade" id="tab-sections" role="tabpanel">
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
        </div>
    </div>
@endsection


