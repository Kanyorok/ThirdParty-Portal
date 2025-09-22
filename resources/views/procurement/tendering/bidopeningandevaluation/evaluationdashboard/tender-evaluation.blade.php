@extends('layouts.app')
@section('title', 'Tender Evaluation - ' . $tender->TenderNo)

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4><i class="fas fa-clipboard-check"></i> Tender Evaluation Overview</h4>
            <p class="text-muted mb-0">
                <strong>{{ $tender->TenderNo }}</strong> - {{ $tender->Title }}<br>
                <strong>Your Role:</strong> {{ $userRole }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('tender-criteria', $tender->Id) }}" class="btn btn-primary">
                <i class="fas fa-sliders-h"></i> Configure Sections & Criteria
            </a>
            <a href="{{ route('evaluationdashboard.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tender Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Tender Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Tender No:</dt>
                        <dd class="col-sm-8">{{ $tender->TenderNo }}</dd>
                        <dt class="col-sm-4">Title:</dt>
                        <dd class="col-sm-8">{{ $tender->Title }}</dd>
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge {{ $tender->Status ? $tender->Status->badgeClass() : 'bg-light text-dark' }}">
                                {{ $tender->Status ? $tender->Status->displayName() : 'Unknown' }}
                            </span>
                        </dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-6">Opening Date:</dt>
                        <dd class="col-sm-6">{{ $tender->OpeningDate ? \Carbon\Carbon::parse($tender->OpeningDate)->format('d/m/Y H:i') : 'Not set' }}</dd>
                        <dt class="col-sm-6">Submission Deadline:</dt>
                        <dd class="col-sm-6">{{ $tender->SubmissionDeadline ? \Carbon\Carbon::parse($tender->SubmissionDeadline)->format('d/m/Y H:i') : 'Not set' }}</dd>
                        <dt class="col-sm-6">Responsive Bids:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-info">{{ $responsiveBids->count() }} bids</span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Sections -->
    @if($sections && $sections->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Evaluation Sections</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($sections as $tenderSection)
                        @php $section = $tenderSection->sections; @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-title mb-0">{{ $section->SectionName }}</h6>
                                        <span class="badge bg-primary">{{ $tenderSection->Weight }}%</span>
                                    </div>
                                    @if($section->Description)
                                        <p class="card-text small text-muted mb-2">{{ $section->Description }}</p>
                                    @endif
                                    <div class="small">
                                        <i class="fas fa-list"></i> {{ $section->criteria->count() }} criteria
                                        @if($section->criteria->count() > 0)
                                            <div class="mt-2">
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($section->criteria as $criteria)
                                                        <li class="mb-1">
                                                            <i class="fas fa-check text-success me-1"></i>
                                                            {{ $criteria->CriteriaName }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <div class="text-muted mt-1">No criteria configured</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @php
                    $totalWeight = $sections->sum('Weight');
                    $isWeightValid = abs($totalWeight - 100) < 0.01;
                @endphp
                
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Total Section Weight:</strong>
                        <span class="h5 mb-0 {{ $isWeightValid ? 'text-success' : 'text-danger' }}">
                            {{ $totalWeight }}%
                            @if($isWeightValid)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-exclamation-triangle text-danger"></i>
                            @endif
                        </span>
                    </div>
                    @if(!$isWeightValid)
                        <div class="small text-danger mt-1">
                            ⚠️ Section weights must sum to exactly 100% for evaluation to proceed.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>No Evaluation Sections Configured</strong><br>
            This tender does not have evaluation sections set up. Please contact the administrator to configure evaluation sections and criteria before proceeding.
        </div>
    @endif

    <!-- Responsive Bids for Evaluation -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users"></i> Responsive Bids Ready for Evaluation</h5>
        </div>
        <div class="card-body">
            @if($responsiveBids->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h6>No Responsive Bids Available</h6>
                    <p class="text-muted">There are currently no responsive bids available for evaluation in this tender.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Supplier</th>
                                <th>Bid Amount</th>
                                <th>Submission Details</th>
                                <th>Evaluation Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($responsiveBids as $bid)
                                @php $evaluationStatus = $bid->getEvaluationStatus(); @endphp
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $bid->SupplierName }}</strong>
                                            <div class="small text-muted">
                                                ID: {{ $bid->SupplierId }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ $bid->Currency }} {{ number_format($bid->BidAmount, 2) }}</strong>
                                        @if($bid->ValidityPeriod)
                                            <div class="small text-muted">
                                                Valid: {{ $bid->ValidityPeriod }} days
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><strong>Source:</strong> {{ ucfirst($bid->SubmissionSource ?? 'manual') }}</div>
                                            @if($bid->ReceivedAt)
                                                <div><strong>Received:</strong> {{ \Carbon\Carbon::parse($bid->ReceivedAt)->format('d/m/Y H:i') }}</div>
                                            @endif
                                            @if($bid->DeliveryPeriod)
                                                <div><strong>Delivery:</strong> {{ $bid->DeliveryPeriod }} days</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $evaluationStatus['badge_class'] }}">
                                            <i class="{{ $evaluationStatus['icon'] }}"></i>
                                            {{ $evaluationStatus['label'] }}
                                        </span>
                                        @if($bid->TotalScore)
                                            <div class="small text-muted mt-1">
                                                Score: {{ $bid->TotalScore }}/100
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($sections && $sections->count() > 0 && abs($sections->sum('Weight') - 100) < 0.01)
                                                @if($evaluationStatus['status'] === 'pending-evaluation' || $evaluationStatus['status'] === 'evaluation-in-progress')
                                                    <a href="{{ route('evaluation.form', $bid->Id) }}" 
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i> 
                                                        {{ $evaluationStatus['status'] === 'pending-evaluation' ? 'Start Evaluation' : 'Continue Evaluation' }}
                                                    </a>
                                                @elseif($evaluationStatus['status'] === 'evaluation-complete')
                                                    <a href="{{ route('evaluation.form', $bid->Id) }}" 
                                                       class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-eye"></i> Review Evaluation
                                                    </a>
                                                @else
                                                    <span class="btn btn-outline-secondary btn-sm disabled">
                                                        <i class="fas fa-lock"></i> Not Available
                                                    </span>
                                                @endif
                                            @else
                                                <span class="btn btn-outline-warning btn-sm disabled" 
                                                      title="Evaluation sections not properly configured">
                                                    <i class="fas fa-exclamation-triangle"></i> Config Required
                                                </span>
                                            @endif
                                            
                                            <!-- Bid details dropdown -->
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                        data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="#" 
                                                           onclick="showBidDetails({{ $bid->Id }})">
                                                            <i class="fas fa-info-circle"></i> View Details
                                                        </a>
                                                    </li>
                                                    @if($bid->EvaluationNotes)
                                                        <li>
                                                            <a class="dropdown-item" href="#" 
                                                               onclick="showEvaluationNotes({{ $bid->Id }})">
                                                                <i class="fas fa-sticky-note"></i> View Notes
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Evaluation Summary -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h4>{{ $responsiveBids->count() }}</h4>
                                <small>Total Bids</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h4>{{ $responsiveBids->where('BidStatus', 'responsive')->where('TechnicalScore', null)->count() }}</h4>
                                <small>Pending Evaluation</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h4>{{ $responsiveBids->where('BidStatus', 'responsive')->whereNotNull('TechnicalScore')->count() }}</h4>
                                <small>In Progress</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h4>{{ $responsiveBids->where('BidStatus', 'evaluated')->count() }}</h4>
                                <small>Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function showBidDetails(bidId) {
    // Placeholder for bid details modal
    alert('Bid details for ID: ' + bidId + '\n\nThis will show comprehensive bid information including documents, supplier details, and submission history.');
}

function showEvaluationNotes(bidId) {
    // Placeholder for evaluation notes modal  
    alert('Evaluation notes for bid ID: ' + bidId + '\n\nThis will show evaluation comments and notes.');
}
</script>

@endsection
