@extends('layouts.app')
@section('title', 'Select Tender for Score Consolidation')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📊 Score Consolidation - Select Tender</h4>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Available Tenders for Evaluation</h5>
                </div>
                <div class="card-body">
                    @if($tenders->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No published tenders with suppliers found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tender No.</th>
                                        <th>Title</th>
                                        <th>Suppliers</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenders as $tender)
                                    <tr>
                                        <td>{{ $tender->TenderNo }}</td>
                                        <td>{{ Str::limit($tender->Title, 50) }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $tender->tenderSuppliers->count() }} Suppliers</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Published</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('bidscores.index', ['tender_id' => $tender->Id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-chart-bar"></i> View Scores
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6>Instructions</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <ul>
                            <li>Select a tender to view consolidated evaluation scores</li>
                            <li>Only published tenders with registered suppliers are shown</li>
                            <li>Scores are automatically calculated from committee evaluations</li>
                            <li>Rankings are based on weighted section scores</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
