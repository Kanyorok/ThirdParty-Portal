@extends('layouts.app')
@section('title', 'Direct Procurement LPO Creation')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">🚚 Direct Procurement LPO Creation</h4>
                        <p class="text-muted mb-0">Create Local Purchase Orders from approved procurement plan items
                            designated for direct procurement</p>
                    </div>
                    <div>
                        <a href="{{ route('lpo.origination.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="{{ route('procurement-plan-consolidation.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-clipboard-list"></i> Manage Plans
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Direct Procurement Plans Available for LPO -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-shipping-fast"></i> Approved Plans with Direct Procurement Items
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Plan Details</th>
                                    <th>Direct Procurement Items</th>
                                    <th>Estimated Values</th>
                                    <th>Plan Status</th>
                                    <th>Approval Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($directProcurementPlans as $plan)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong class="text-info">{{ $plan->Title }}</strong>
                                                <div class="text-muted small">
                                                    Plan ID: {{ $plan->PlanID }}
                                                </div>
                                                @if($plan->Description)
                                                    <div class="text-muted small">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        {{ Str::limit($plan->Description, 60) }}
                                                    </div>
                                                @endif
                                                <div class="text-muted small">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Period: {{ $plan->SchedulePeriod }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                @php
                                                    $directItems = $plan->planLineItems;
                                                    $totalItems = $directItems->count();
                                                    $totalQty = $directItems->sum('MergedQty');
                                                @endphp

                                                <div class="mb-1">
                                                    <strong class="text-primary">{{ $totalItems }}</strong>
                                                    <span class="text-muted">item(s) • </span>
                                                    <strong class="text-success">{{ number_format($totalQty) }}</strong>
                                                    <span class="text-muted">total qty</span>
                                                </div>

                                                @foreach($directItems->take(3) as $item)
                                                    <div class="mb-1 p-1 bg-light rounded">
                                                        <small class="text-dark">
                                                            <strong>{{ $item->item?->ItemName ?? 'N/A' }}</strong>
                                                            <span class="text-muted">
                                                                    ({{ number_format($item->MergedQty) }} @
                                                                    {{ number_format($item->EstimatedUnitCost, 2) }})
                                                                </span>
                                                        </small>
                                                        <div class="text-muted" style="font-size: 0.7rem;">
                                                            {{ $item->procurementMode?->Description ?? 'Direct Procurement' }}
                                                        </div>
                                                    </div>
                                                @endforeach

                                                @if($totalItems > 3)
                                                    <div class="text-muted small">
                                                        <i class="fas fa-ellipsis-h"></i> and {{ $totalItems - 3 }} more
                                                        items
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $totalEstimatedValue = $plan->planLineItems->sum(function($item) {
                                                    return $item->MergedQty * $item->EstimatedUnitCost;
                                                });
                                                $avgUnitCost = $plan->planLineItems->avg('EstimatedUnitCost');
                                            @endphp
                                            <div>
                                                <strong class="text-success">
                                                    {{ number_format($totalEstimatedValue, 2) }}
                                                </strong>
                                                <div class="text-muted small">Total Estimated</div>
                                            </div>
                                            <div class="mt-1">
                                                    <span class="text-info">
                                                        {{ number_format($avgUnitCost, 2) }}
                                                    </span>
                                                <div class="text-muted small">Avg. Unit Cost</div>
                                            </div>
                                        </td>
                                        <td>
                                            @switch($plan->Status)
                                                @case('Approved')
                                                    <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i>{{ $plan->Status }}
                                                        </span>
                                                    @break
                                                @case('Draft')
                                                    <span class="badge bg-secondary">
                                                            <i class="fas fa-edit me-1"></i>{{ $plan->Status }}
                                                        </span>
                                                    @break
                                                @case('Pending Approval')
                                                    <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>{{ $plan->Status }}
                                                        </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-info">{{ $plan->Status }}</span>
                                            @endswitch

                                            @if($plan->ApprovedBy)
                                                <div class="text-muted small">
                                                    By: User #{{ $plan->ApprovedBy }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($plan->ApprovedOn)
                                                <div class="text-success small">
                                                    <i class="fas fa-calendar-check me-1"></i>
                                                    {{ $plan->ApprovedOn->format('d M Y') }}
                                                </div>
                                                <div class="text-muted small">
                                                    {{ $plan->ApprovedOn->format('H:i') }}
                                                </div>
                                            @elseif($plan->CreatedOn)
                                                <div class="text-muted small">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $plan->CreatedOn->format('d M Y') }}
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                @if($plan->Status === 'Approved' && $plan->planLineItems->count() > 0)
                                                    <a href="{{ route('lpo.create.direct-procurement', $plan->PlanID) }}"
                                                       class="btn btn-sm btn-info text-white"
                                                       title="Create LPO from Direct Procurement Plan">
                                                        <i class="fas fa-plus-circle"></i> Create LPO
                                                    </a>
                                                @else
                                                    <span class="btn btn-sm btn-outline-secondary disabled"
                                                          title="Plan not ready for LPO creation">
                                                            <i class="fas fa-ban"></i> Not Ready
                                                        </span>
                                                @endif

                                                <button type="button"
                                                        class="btn btn-sm btn-outline-info dropdown-toggle dropdown-toggle-split"
                                                        data-bs-toggle="dropdown" title="More Actions">
                                                    <span class="visually-hidden">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('procurement-plan-consolidation.show', $plan->PlanID) }}">
                                                            <i class="fas fa-eye me-2"></i>View Plan Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item"
                                                                onclick="showPlanSummary({{ $plan->PlanID }})">
                                                            <i class="fas fa-info-circle me-2"></i>Plan Summary
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item"
                                                                onclick="showDirectItems({{ $plan->PlanID }})">
                                                            <i class="fas fa-list me-2"></i>View All Items
                                                        </button>
                                                    </li>
                                                    @if($plan->planLineItems->count() > 0)
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item text-info"
                                                                    onclick="showEstimatedCosts({{ $plan->PlanID }})">
                                                                <i class="fas fa-calculator me-2"></i>Cost Breakdown
                                                            </button>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No Direct Procurement Plans Available</h6>
                                            <p class="text-muted">No approved procurement plans with direct procurement
                                                items are available at this time.</p>
                                            <div class="mt-3">
                                                <a href="{{ route('procurement-plan-consolidation.index') }}"
                                                   class="btn btn-primary me-2">
                                                    <i class="fas fa-plus"></i> Manage Plans
                                                </a>
                                                <a href="{{ route('lpo.origination.contract-based') }}"
                                                   class="btn btn-outline-success">
                                                    <i class="fas fa-file-contract"></i> Try Contract-Based LPO
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($directProcurementPlans->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $directProcurementPlans->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Information Panel -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle"></i> Direct Procurement LPO Guidelines
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-info">✅ Advantages:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Fastest LPO creation method
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Pre-approved budget allocation
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Streamlined procurement process
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Bulk item selection available
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-info">📋 Requirements:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Plan must be in 'Approved' status
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Items must have 'Direct Procurement' method
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Items should be in 'Pending' execution status
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Supplier selection required during LPO creation
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-pie"></i> Direct Procurement Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-info mb-0">{{ $directProcurementPlans->total() }}</h4>
                                        <small class="text-muted">Available Plans</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-0">
                                            {{ $directProcurementPlans->where('Status', 'Approved')->count() }}
                                        </h4>
                                        <small class="text-muted">Approved</small>
                                    </div>
                                </div>
                                <hr>
                                @if($directProcurementPlans->count() > 0)
                                    <div class="small">
                                        @php
                                            $totalItems = $directProcurementPlans->sum(function($plan) {
                                                return $plan->planLineItems->count();
                                            });
                                            $totalValue = $directProcurementPlans->sum(function($plan) {
                                                return $plan->planLineItems->sum(function($item) {
                                                    return $item->MergedQty * $item->EstimatedUnitCost;
                                                });
                                            });
                                        @endphp
                                        <div class="d-flex justify-content-between">
                                            <span>Total Items:</span>
                                            <span class="text-info">{{ number_format($totalItems) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Total Estimated Value:</span>
                                            <span class="text-success">{{ number_format($totalValue, 0) }}</span>
                                        </div>
                                    </div>
                                    <hr>
                                @endif
                                <div class="small text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    <strong>Tip:</strong> Direct procurement is most efficient for routine, low-value
                                    items with pre-approved budgets.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plan Summary Modal -->
    <div class="modal fade" id="planSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Procurement Plan Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="planSummaryContent">
                    <!-- Plan details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Direct Items Modal -->
    <div class="modal fade" id="directItemsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Direct Procurement Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="directItemsContent">
                    <!-- Direct items will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown Modal -->
    <div class="modal fade" id="costBreakdownModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cost Breakdown</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="costBreakdownContent">
                    <!-- Cost breakdown will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showPlanSummary(planId) {
            const modal = new bootstrap.Modal(document.getElementById('planSummaryModal'));
            document.getElementById('planSummaryContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();

            // TODO: Implement AJAX call to fetch plan details
            setTimeout(() => {
                document.getElementById('planSummaryContent').innerHTML = `
                    <p><strong>Plan ID:</strong> ${planId}</p>
                    <p>Additional plan details can be loaded via AJAX here.</p>
                `;
            }, 1000);
        }

        function showDirectItems(planId) {
            const modal = new bootstrap.Modal(document.getElementById('directItemsModal'));
            document.getElementById('directItemsContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();

            // TODO: Implement AJAX call to fetch direct items
            setTimeout(() => {
                document.getElementById('directItemsContent').innerHTML = `
                    <p><strong>Plan ID:</strong> ${planId}</p>
                    <p>List of all direct procurement items will be displayed here.</p>
                `;
            }, 1000);
        }

        function showEstimatedCosts(planId) {
            const modal = new bootstrap.Modal(document.getElementById('costBreakdownModal'));
            document.getElementById('costBreakdownContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();

            // TODO: Implement AJAX call to fetch cost breakdown
            setTimeout(() => {
                document.getElementById('costBreakdownContent').innerHTML = `
                    <p><strong>Plan ID:</strong> ${planId}</p>
                    <p>Detailed cost breakdown will be displayed here.</p>
                `;
            }, 1000);
        }
    </script>
@endsection
