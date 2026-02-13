@extends('layouts.app')
@section('title', 'Award Management')
@section('content')

<div class="container mt-4">
    <!-- Header with Type Selector -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-0">
                🏆 Award Management
                @if($type === 'rfq')
                    <span class="badge bg-success ms-2">RFQ</span>
                @else
                    <span class="badge bg-primary ms-2">Tender</span>
                @endif
            </h4>
            <p class="text-muted">{{ $tender->TenderNo }} – {{ $tender->Title }}</p>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body py-2">
                    <label class="form-label mb-1 fw-bold">Switch Type & Item:</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <select id="typeSelector" class="form-select form-select-sm" onchange="switchType()">
                                <option value="tender" {{ $type === 'tender' ? 'selected' : '' }}>📋 Tender</option>
                                <option value="rfq" {{ $type === 'rfq' ? 'selected' : '' }}>💰 RFQ</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <select id="itemSelector" class="form-select form-select-sm" onchange="switchItem()">
                                @if($type === 'tender')
                                    @foreach($availableItems['tender'] ?? [] as $item)
                                        <option value="{{ $item['id'] }}" {{ $item['id'] == $tender->Id ? 'selected' : '' }}>
                                            {{ $item['number'] }}
                                            @if($item['has_award'])
                                                ✅
                                            @endif
                                        </option>
                                    @endforeach
                                @else
                                    @foreach($availableItems['rfq'] ?? [] as $item)
                                        <option value="{{ $item['id'] }}" {{ $item['id'] == $tender->Id ? 'selected' : '' }}>
                                            {{ $item['number'] }}
                                            @if($item['has_award'])
                                                ✅
                                            @endif
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Existing Award Alert -->
    @if($existingAward)
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle"></i> 
            This {{ $type === 'rfq' ? 'RFQ' : 'tender' }} has already been awarded to 
            <strong>{{ 
                $existingAward->winningSupplier->supplierMaster->party->TradingName 
                ?? $existingAward->supplier->supplierMaster->party->TradingName 
                ?? $existingAward->winningSupplier->thirdParty->ThirdPartyName
                ?? $existingAward->winningSupplier->thirdParty->TradingName
                ?? $existingAward->winningSupplier->SupplierName
                ?? ('Supplier #'.($existingAward->WinningSupplierID ?? $existingAward->SupplierId)) 
            }}</strong> 
            on {{ optional($existingAward->AwardDate)->format('d/m/Y') ?? optional($existingAward->CreatedOn)->format('d/m/Y') }}.
            @if(method_exists($existingAward,'status_badge') || isset($existingAward->status_badge))
            Status: <span class="badge {{ $existingAward->status_badge['class'] ?? 'bg-warning text-dark' }}">{{ $existingAward->status_badge['text'] ?? $existingAward->AwardStatus }}</span>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Evaluation Completeness Alert --}}
    @if(!$existingAward && isset($evaluationCompleteness) && !$evaluationCompleteness['is_complete'])
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <strong>Evaluations Incomplete:</strong>
            {{ $evaluationCompleteness['members_completed'] }} of {{ $evaluationCompleteness['total_members'] }}
            committee member(s) have completed evaluations for all {{ $evaluationCompleteness['total_bids'] }}
            {{ $type === 'rfq' ? 'supplier response(s)' : 'bid(s)' }}.
            <strong>Award creation is disabled until all evaluations are done.</strong>
            @if(!empty($evaluationCompleteness['pending_members']))
                <br><small class="text-muted mt-1 d-block">
                    <i class="fas fa-user-clock me-1"></i>Pending:
                    {{ implode(', ', $evaluationCompleteness['pending_members']) }}
                </small>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Award Content -->
    <div class="card shadow-sm">
        <div class="card-header {{ $type === 'rfq' ? 'bg-success' : 'bg-primary' }} text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                @if($type === 'rfq')
                    💰 RFQ Award Processing
                @else
                    🏆 Tender Award Processing  
                @endif
            </h5>
            <div class="badge bg-light text-dark">
                {{ $type === 'rfq' ? 'Quotation Comparison' : 'Evaluation Results' }}
            </div>
        </div>
        
        <div class="card-body">
            @if(empty($evaluationData))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No {{ $type === 'rfq' ? 'responsive quotations' : 'evaluation results' }} found.
                    @if($type === 'tender')
                        Please ensure evaluations have been completed and scores consolidated.
                    @else
                        Please ensure quotations have been submitted and responsiveness checked.
                    @endif
                </div>
            @else
                <form action="{{ route('procawards.store') }}" method="POST" id="awardForm">
                    @csrf
                    <input type="hidden" name="tender_id" value="{{ $tender->Id }}">
                    <input type="hidden" name="award_type" value="{{ $type }}">
                    
                    @if($type === 'rfq')
                        @include('procurement.awards.partials.rfq_evaluation', ['suppliers' => $evaluationData])
                    @else
                        @include('procurement.awards.partials.tender_evaluation', ['scores' => $evaluationData])
                    @endif

                    @if(!$existingAward)
                        <!-- Award Form Section -->
                        <div class="border-top pt-4 mt-4">
                            <h6 class="fw-bold mb-3">📝 Award Details</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Award Justification <span class="text-danger">*</span></label>
                                <textarea name="award_justification" class="form-control" rows="3" required
                                          placeholder="Provide detailed justification for this award decision...">{{ old('award_justification', $type === 'rfq' ? 'Most competitive responsive quotation with acceptable terms and delivery schedule.' : 'Highest scoring responsive bidder based on comprehensive technical and financial evaluation.') }}</textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Contract Value</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $tender->currency->Code ?? 'KES' }}</span>
                                        <input type="number" name="awarded_amount" class="form-control" step="0.01" 
                                               placeholder="Final contract amount" value="{{ old('awarded_amount') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Contract Start Date</label>
                                    <input type="date" name="contract_start_date" class="form-control" 
                                           value="{{ old('contract_start_date') }}" min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Contract End Date</label>
                                    <input type="date" name="contract_end_date" class="form-control" 
                                           value="{{ old('contract_end_date') }}">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="notify_unsuccessful" id="notify" value="1" 
                                               {{ old('notify_unsuccessful', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notify">
                                            <i class="fas fa-envelope me-1"></i> 
                                            Send notifications to unsuccessful {{ $type === 'rfq' ? 'suppliers' : 'bidders' }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="generateContract">
                                        <label class="form-check-label" for="generateContract">
                                            <i class="fas fa-file-contract me-1"></i> 
                                            Generate draft contract after approval
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <a href="{{ route('procawards.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Awards List
                            </a>
                            @if($type === 'tender')
                                <a href="{{ route('bidscores.index') }}?tender_id={{ $tender->Id }}" class="btn btn-outline-info ms-2">
                                    <i class="fas fa-chart-bar"></i> View Score Details
                                </a>
                            @endif
                        </div>
                        <div>
                            @if($existingAward && ($existingAward->AwardStatus === \App\Models\Procurement\TenderAward::STATUS_PENDING))
                                <form action="{{ route('awards.cancel', $existingAward->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this pending award and re-open for re-award?');">
                                    @csrf
                                    <input type="hidden" name="cancel_reason" value="Cancelled to re-award">
                                    <button type="submit" class="btn btn-outline-danger me-2">
                                        <i class="fas fa-times"></i> Cancel Award
                                    </button>
                                </form>
                            @endif

                            {{-- Contract Creation Button --}}
                            @if($existingAward && ($existingAward->AwardStatus === 'Approved'))
                                @if(empty($existingAward->ContractStatus))
                                    <a href="{{ route('contracts.createFromAward', ['awardId' => $existingAward->Id, 'type' => $type]) }}" class="btn btn-success me-2">
                                        <i class="fas fa-file-contract"></i> Create Contract
                                    </a>
                                @elseif($existingAward->ContractStatus === 'Draft Created')
                                    <a href="{{ route('contracts.edit', ['id' => $existingAward->Id, 'type' => $type]) }}" class="btn btn-warning me-2">
                                        <i class="fas fa-file-signature"></i> Continue Contract Draft
                                    </a>
                                @else
                                    <a href="{{ route('contracts.show', ['id' => $existingAward->Id, 'type' => $type]) }}" class="btn btn-primary me-2">
                                        <i class="fas fa-file-alt"></i> View Contract
                                    </a>
                                @endif
                            @endif

                            <button type="button" class="btn btn-info me-2" onclick="previewAward()">
                                <i class="fas fa-eye"></i> Preview
                            </button>

                            {{-- Approval Buttons --}}
                            @if($showApprovalButtons)
                                <button type="button" class="btn btn-success me-2" onclick="triggerApprove()">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="button" class="btn btn-danger me-2" onclick="triggerReject()">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            @endif

                            @if(!$existingAward)
                                @php
                                    $evaluationsIncomplete = (isset($evaluationCompleteness) && !$evaluationCompleteness['is_complete']);
                                @endphp
                                <button type="submit" class="btn {{ $type === 'rfq' ? 'btn-success' : 'btn-primary' }}"
                                    {{ $evaluationsIncomplete ? 'disabled' : '' }}
                                    @if($evaluationsIncomplete)
                                        title="All committee members must complete evaluations for all bids before awarding"
                                        data-bs-toggle="tooltip"
                                    @endif
                                >
                                    <i class="fas fa-check"></i> Create Award (Pending Approval)
                                </button>
                            @elseif($existingAward->AwardStatus === 'Pending' && !$showApprovalButtons && $type === 'rfq')
                                {{-- Manual Submit for Approval (Rescue/Initial) --}}
                                {{-- Manual Submit for Approval (Rescue/Initial) --}}
                                <form action="{{ route('awards.submit-approval', $existingAward->Id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="award_id" value="{{ $existingAward->Id }}">
                                    <button type="submit" class="btn btn-warning text-dark me-2">
                                        <i class="fas fa-paper-plane"></i> Submit for Approval
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    </div>
</div>

{{-- Approval Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
                @csrf
                <input type="hidden" name="award_id" id="approvalAwardId" value="{{ $existingAward->Id ?? '' }}">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Award</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Approval Remarks <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" required
                                  placeholder="Enter remarks for this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Award</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Rejection Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="award_id" id="rejectionAwardId" value="{{ $existingAward->Id ?? '' }}">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Award</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required
                                  placeholder="Please provide a reason for rejecting this award..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Award</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function switchType() {
    const typeSelector = document.getElementById('typeSelector');
    const selectedType = typeSelector.value;
    const currentTenderId = {{ $tender->Id }};
    
    // Update the item selector options based on selected type
    updateItemSelector(selectedType);
    
    // Redirect to the unified interface with new type
    window.location.href = `{{ url('/procurement/awards/unified') }}/${currentTenderId}?type=${selectedType}`;
}

function switchItem() {
    const itemSelector = document.getElementById('itemSelector');
    const selectedId = itemSelector.value;
    const currentType = document.getElementById('typeSelector').value;
    
    if (selectedId && selectedId !== '{{ $tender->Id }}') {
        window.location.href = `{{ url('/procurement/awards/unified') }}/${selectedId}?type=${currentType}`;
    }
}

function updateItemSelector(type) {
    const itemSelector = document.getElementById('itemSelector');
    itemSelector.innerHTML = '<option value="">Loading...</option>';
    
    // Get items of selected type from availableItems data
    const availableItems = @json($availableItems);
    const itemsOfType = availableItems[type] || [];
    
    itemSelector.innerHTML = '';
    itemsOfType.forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = item.number + (item.has_award ? ' ✅' : '');
        if (item.id === {{ $tender->Id }}) {
            option.selected = true;
        }
        itemSelector.appendChild(option);
    });
}

function previewAward() {
    // Collect form data and show preview modal
    alert('Award preview functionality - to be implemented');
}

// Update item selector when type changes
document.getElementById('typeSelector').addEventListener('change', function() {
    const selectedType = this.value;
    const itemSelector = document.getElementById('itemSelector');
    
    // Clear current options
    itemSelector.innerHTML = '<option value="">Loading...</option>';
    
    // You could make an AJAX call here to get items of the selected type
    // For now, we'll just trigger the switch
});

    function triggerApprove() {
        const form = document.getElementById('approveForm');
        const id = '{{ $existingAward->Id ?? "" }}';
        
        if (id) {
            form.action = `{{ route('awards.approve', ':id') }}`.replace(':id', id) + `?type={{ $type }}`;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        } else {
            alert('Error: Award ID missing.');
        }
    }

    function triggerReject() {
        const form = document.getElementById('rejectForm');
        const id = '{{ $existingAward->Id ?? "" }}';

        if (id) {
            form.action = `{{ route('awards.reject', ':id') }}`.replace(':id', id) + `?type={{ $type }}`;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        } else {
             alert('Error: Award ID missing.');
        }
    }
</script>

@endsection
