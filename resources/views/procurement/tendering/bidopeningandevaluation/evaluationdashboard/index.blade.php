@extends('layouts.app')
@section('title', 'Evaluator Dashboard')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4><i class="fas fa-clipboard-check"></i> Evaluator Dashboard</h4>
            <p class="text-muted mb-0">
                Role: <strong>{{ $userRole ?? 'Committee Member' }}</strong>
            </p>
        </div>
        {{-- Requirement notice for committee & criteria setup (show only when needed) --}}
        @if(isset($needsCommitteeSetup) && $needsCommitteeSetup || isset($needsCriteriaSetup) && $needsCriteriaSetup)
            <div class="text-end">
                <div class="text-danger fw-bold">IMPORTANT: You must appoint the Tender Committee and set up Tender Criteria before performing evaluations.
                    <br>
                    @if(isset($needsCommitteeSetup) && $needsCommitteeSetup)
                        <a href="/procurement/tendercommittee" class="btn btn-sm btn-outline-primary mt-2 me-2">Appoint Tender Committee</a>
                    @endif
                    @if(isset($needsCriteriaSetup) && $needsCriteriaSetup)
                        <a href="/procurement/tenderevaluations" class="btn btn-sm btn-outline-danger mt-2">Go to Tender Criteria Setup</a>
                    @endif
                </div>
            </div>
        @endif
        <div class="text-end">
            @if(isset($tenderCount) && isset($bidsCount))
                <div class="small text-muted">
                    <i class="fas fa-info-circle"></i>
                    {{ $tenderCount }} tender(s) • {{ $bidsCount }} bid(s) ready for evaluation
                </div>
            @endif
        </div>
    </div>

    <!-- Persistent requirement banner: always visible and prominent -->
    <div class="w-100 mb-3">
        <div class="alert alert-danger fw-bold mb-0" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            REQUIRED: You must appoint the Tender Committee and set up Tender Criteria before performing evaluations.
            <div class="mt-2">
                <a href="/procurement/tendercommittee" class="btn btn-sm btn-outline-light me-2">Appoint Tender Committee</a>
                <a href="/procurement/tenderevaluations" class="btn btn-sm btn-light">Set Up Tender Criteria</a>
            </div>
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

    @if(isset($message))
        <!-- No Committee Assignments -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-info">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-slash fa-3x text-info mb-3"></i>
                        <h5>No Evaluation Assignments</h5>
                        <p class="text-muted mb-4">{{ $message }}</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-info-circle"></i> What to do?</h6>
                                        <ul class="list-unstyled text-start small">
                                            <li>• Wait for committee appointment</li>
                                            <li>• Check Member Response page</li>
                                            <li>• Accept pending appointments</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-link"></i> Useful Links</h6>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('memberresponse.index') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-reply"></i> Member Response
                                            </a>
                                            <a href="{{ route('tendercommittee.index') }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-users"></i> Committee Status
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Evaluation Dashboard -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Bids Ready for Evaluation</h5>
            </div>
            <div class="card-body">
                @if($evaluationData->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h6>No Bids Ready for Evaluation</h6>
                        <p class="text-muted">There are currently no responsive bids available for evaluation.</p>
                    </div>
                @else
    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tender</th>
                    <th>Supplier</th>
                                    <th>Bid Amount</th>
                                    <th>Evaluation Sections</th>
                                    <th>Configuration Status</th>
                                    <th>Evaluation Status</th>
                                    <th>Your Progress</th>
                                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                                @foreach($evaluationData->groupBy('tender_id') as $tenderId => $tenderBids)
                                    @php
                                        $firstBid = $tenderBids->first();
                                        $tenderBidsCount = $tenderBids->count();
                                    @endphp
                                    <!-- Tender Header Row -->
                                    <tr class="table-info">
                                        <td colspan="8">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><i class="fas fa-file-contract"></i> {{ $firstBid['tender_no'] }}</strong>
                                                    - {{ $firstBid['tender_title'] }}
                                                    <span class="badge bg-primary ms-2">{{ $tenderBidsCount }} bids</span>
                                                </div>
                                                <div>
                                                    @if($firstBid['can_evaluate'])
                                                        <a href="{{ route('evaluator.tender-evaluation', $tenderId) }}"
                                                           class="btn btn-sm btn-success">
                                                            <i class="fas fa-play"></i> Start Evaluation
                                                        </a>
                                                    @else
                                                        <span class="badge bg-warning">⚠️ Not Ready</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Bid Rows -->
                                    @foreach($tenderBids as $bid)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="small text-muted">
                                                    Role: <strong>{{ $bid['user_role'] }}</strong>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $bid['supplier_name'] }}</strong>
                                            </td>
                                            <td class="text-end">
                                                <strong>{{ $bid['currency'] }} {{ number_format($bid['bid_amount'], 2) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @if($bid['sections_configured'])
                                                    <span class="badge bg-success">{{ $bid['sections_count'] }} sections</span>
                                                    <div class="small text-muted">
                                                        Weight: {{ $bid['total_weight'] }}%
                                                    </div>
                                                @else
                                                    <span class="badge bg-warning">⚠️ Not Configured</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!$bid['sections_configured'])
                                                    <span class="badge bg-danger">❌ No Sections</span>
                                                @elseif(!$bid['weight_valid'])
                                                    <span class="badge bg-danger">❌ Invalid Weights</span>
                                                @else
                                                    <span class="badge bg-success">✅ Ready</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $bid['evaluation_status']['badge_class'] }}">
                                                    <i class="{{ $bid['evaluation_status']['icon'] }}"></i>
                                                    {{ $bid['evaluation_status']['label'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($bid['has_evaluated'])
                                                    <span class="badge bg-success">✅ Completed</span>
                                                    @if($bid['total_score'])
                                                        <div class="small text-muted">
                                                            Score: {{ $bid['total_score'] }}/100
                                                        </div>
                                                    @endif
                        @else
                                                    <span class="badge bg-secondary">⏳ Pending</span>
                        @endif
                    </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($bid['can_evaluate'] && !$bid['has_evaluated'])
                                                        <a href="{{ route('evaluator.tender-evaluation', $tenderId) }}"
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i> Evaluate
                                                        </a>
                                                    @elseif($bid['has_evaluated'])
                                                        <a href="{{ route('evaluator.tender-evaluation', $tenderId) }}"
                                                           class="btn btn-sm btn-outline-info">
                                                            <i class="fas fa-eye"></i> Review
                                                        </a>
                                                    @else
                                                        <span class="btn btn-sm btn-secondary disabled">
                                                            <i class="fas fa-lock"></i> Locked
                                                        </span>
                                                    @endif

                                                    <!-- Quick actions menu -->
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item" href="#"
                                                                   onclick="showBidDetails({{ $bid['bid_id'] }})">
                                                                    <i class="fas fa-info-circle"></i> Bid Details
                                                                </a>
                                                            </li>
                                                            @if($bid['sections_configured'])
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                       onclick="showEvaluationSections({{ $tenderId }})">
                                                                        <i class="fas fa-list"></i> View Sections
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            @if($bid['evaluation_notes'])
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                       onclick="showEvaluationNotes({{ $bid['bid_id'] }})">
                                                                        <i class="fas fa-sticky-note"></i> Notes
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                    </td>
                </tr>
                                    @endforeach

                                    <!-- Tender Summary Row -->
                                    @if($tenderBids->count() > 1)
                                        <tr class="table-light">
                                            <td class="ps-4">
                                                <small class="text-muted">
                                                    <i class="fas fa-calculator"></i> Summary
                                                </small>
                                            </td>
                                            <td colspan="4">
                                                <small class="text-muted">
                                                    {{ $tenderBids->count() }} responsive bids •
                                                    Average: {{ $bid['currency'] }} {{ number_format($tenderBids->avg('bid_amount'), 2) }} •
                                                    Range: {{ $bid['currency'] }} {{ number_format($tenderBids->min('bid_amount'), 2) }} - {{ number_format($tenderBids->max('bid_amount'), 2) }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">
                                                    {{ $tenderBids->where('has_evaluated', true)->count() }}/{{ $tenderBids->count() }} evaluated
                                                </small>
                                            </td>
                                            <td></td>
                                        </tr>
                                    @endif
                                @endforeach
            </tbody>
        </table>
    </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats -->
        @if($evaluationData->isNotEmpty())
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4>{{ $evaluationData->groupBy('tender_id')->count() }}</h4>
                            <small>Active Tenders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4>{{ $evaluationData->count() }}</h4>
                            <small>Total Bids</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>{{ $evaluationData->where('has_evaluated', true)->count() }}</h4>
                            <small>Evaluated</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4>{{ $evaluationData->where('can_evaluate', true)->where('has_evaluated', false)->count() }}</h4>
                            <small>Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<script>
function showBidDetails(bidId) {
    alert('Bid details for ID: ' + bidId + '\n\nThis will show comprehensive bid information including documents, supplier details, and submission history.');
}

function showEvaluationSections(tenderId) {
    alert('Evaluation sections for tender ID: ' + tenderId + '\n\nThis will show the configured sections, criteria, and weights for evaluation.');
}

function showEvaluationNotes(bidId) {
    alert('Evaluation notes for bid ID: ' + bidId + '\n\nThis will show evaluation comments and notes from committee members.');
}
</script>
@endsection
