@extends('layouts.app')
@section('title', 'Bid Scoring Consolidation')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📊 Bid Scoring Consolidation – {{ $tender->TenderNo }}</h4>
        <a href="{{ route('bidscores.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Selection
        </a>
    </div>

    <!-- Tender Info Summary -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Tender:</strong> {{ $tender->Title }}
                </div>
                <div class="col-md-4">
                    <strong>Total Suppliers:</strong> {{ $suppliers->count() }}
                </div>
                <div class="col-md-4">
                    <strong>Currency:</strong> {{ $tender->currency->Code ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    @if(empty($evaluationData))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>No Evaluation Data Found!</strong>
            <br>Committee evaluations have not been completed for this tender yet.
        </div>
    @else
        <!-- Consolidated Scoring Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center align-middle">
                    <tr>
                        <th rowspan="2">#</th>
                        <th rowspan="2">Bidder</th>
                        @php
                            $firstSupplier = reset($evaluationData);
                            $sectionColumns = $firstSupplier['section_scores'] ?? [];
                        @endphp
                        @foreach($sectionColumns as $sectionData)
                            <th>{{ $sectionData['name'] }}<br><small>({{ number_format($sectionData['weight'], 1) }}%)</small></th>
                        @endforeach
                        <th rowspan="2">Total Weighted Score (%)</th>
                        <th rowspan="2">Rank</th>
                        <th rowspan="2">Recommendation</th>
                        <th rowspan="2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evaluationData as $supplierId => $data)
                        <tr>
                            <td class="text-center">{{ $data['rank'] }}</td>
                            <td>
                                <strong>{{ $data['supplier']->SupplierName }}</strong>
                            </td>
                            @foreach($data['section_scores'] as $sectionScore)
                                <td class="text-center">
                                    <span class="badge bg-info">{{ number_format($sectionScore['raw_score'], 1) }}/10</span>
                                    <br>
                                    <small>{{ number_format($sectionScore['weighted_score'], 1) }}%</small>
                                </td>
                            @endforeach
                            <td class="text-center">
                                <strong class="text-primary">{{ number_format($data['total_weighted_score'], 2) }}%</strong>
                            </td>
                            <td class="text-center">
                                @if($data['rank'] == 1)
                                    <span class="badge bg-success fs-6">{{ $data['rank'] }}</span>
                                @elseif($data['rank'] <= 3)
                                    <span class="badge bg-warning fs-6">{{ $data['rank'] }}</span>
                                @else
                                    <span class="badge bg-secondary fs-6">{{ $data['rank'] }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($data['rank'] == 1)
                                    <span class="badge bg-success">{{ $data['recommendation'] }}</span>
                                @elseif($data['rank'] <= 3)
                                    <span class="badge bg-warning">{{ $data['recommendation'] }}</span>
                                @else
                                    <span class="badge bg-danger">{{ $data['recommendation'] }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('bidscores.show', [$tender->Id, $supplierId]) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="View detailed evaluation">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Summary Statistics -->
    @if(!empty($evaluationData))
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>📈 Evaluation Summary</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $topSupplier = reset($evaluationData);
                            $lowestScore = end($evaluationData);
                        @endphp
                        <ul class="list-unstyled mb-0">
                            <li><strong>Highest Score:</strong> {{ $topSupplier['supplier']->SupplierName }} ({{ number_format($topSupplier['total_weighted_score'], 2) }}%)</li>
                            <li><strong>Lowest Score:</strong> {{ $lowestScore['supplier']->SupplierName }} ({{ number_format($lowestScore['total_weighted_score'], 2) }}%)</li>
                            <li><strong>Score Range:</strong> {{ number_format($topSupplier['total_weighted_score'] - $lowestScore['total_weighted_score'], 2) }}%</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>🏆 Award Recommendations</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $recommended = collect($evaluationData)->where('rank', 1)->first();
                            $reserves = collect($evaluationData)->whereBetween('rank', [2, 3]);
                        @endphp
                        <ul class="list-unstyled mb-0">
                            <li><strong>Primary Recommendation:</strong><br>
                                <span class="text-success">{{ $recommended['supplier']->SupplierName ?? 'N/A' }}</span>
                            </li>
                            <li><strong>Reserve List:</strong><br>
                                @forelse($reserves as $reserve)
                                    <span class="text-warning">{{ $reserve['supplier']->SupplierName }}</span><br>
                                @empty
                                    <span class="text-muted">None</span>
                                @endforelse
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
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
