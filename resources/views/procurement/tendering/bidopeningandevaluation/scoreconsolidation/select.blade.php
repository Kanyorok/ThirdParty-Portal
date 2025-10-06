@extends('layouts.app')
@section('title', 'Select Tender for Score Consolidation')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📊 Select Tender for Score Consolidation</h4>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Tenders</h5>
                </div>
                <div class="card-body">
                    @if($tenders->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No tenders available for consolidation.
                        </div>
                    @else
                        <form method="GET" action="{{ route('bidscores.index') }}">
                            <div class="mb-3">
                                <label for="tender_id" class="form-label fw-bold">Select Tender</label>
                                <select name="tender_id" id="tender_id" class="form-select" required>
                                    <option value="">-- Choose a Tender --</option>
                                    @foreach($tenders as $tender)
                                        <option value="{{ $tender->Id }}">
                                            {{ $tender->TenderNo }} - {{ $tender->Title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-calculator"></i> View Consolidated Scores
                                </button>
                                <a href="{{ route('evaluationdashboard.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
