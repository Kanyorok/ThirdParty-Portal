@extends('layouts.app') {{-- Assuming you have a main layout file --}}

@section('title', 'Procurement Plans') {{-- Sets the page title --}}

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Consolidated Procurement Plans</h1>
        {{-- Ensure this route name 'procurement-plans.create' correctly points to your ProcurementPeriodController@create method --}}
        <a href="{{ route('procurement-plans.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> New Plan
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('procurement-plans.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="yearFilter" class="form-label">Year</label>
                        <select class="form-select" name="year" id="yearFilter">
                            <option value="">All Years</option>
                            @if(isset($years) && count($years) > 0)
                            @foreach($years as $yearValue)
                            <option value="{{ $yearValue }}" {{ request('year') == $yearValue ? 'selected' : '' }}>{{ $yearValue }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select class="form-select" name="status" id="statusFilter">
                            <option value="">All Statuses</option>
                            @foreach(\App\Enums\ProcurementPlanStatusEnum::cases() as $statusEnum)
                            <option value="{{ $statusEnum->value }}" {{ request('status') == $statusEnum->value ? 'selected' : '' }}>
                                {{ $statusEnum->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <a href="{{ route('procurement-plans.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-sync"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('partials.alerts') {{-- Optional: Include a partial for displaying session success/error messages --}}

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Plan Reference & Title</th> {{-- Combined for better display --}}
                            <th>Year</th>
                            <th>Items</th>
                            <th>Estimated Cost (KES)</th>
                            <th>Status</th>
                            <th>Created Assets</th> {{-- New column from your previous version --}}
                            <th>Created By</th> {{-- Changed header for clarity --}}
                            <th>Created On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan) {{-- $plan here is an instance of ProcurementPeriod --}}
                        <tr>
                            <td>
                                {{-- Assuming PlanRefNo and Title are attributes on your ProcurementPeriod model --}}
                                <strong>{{ $plan->PlanRefNo ?? 'N/A' }}</strong>
                                <div class="text-muted small">{{ $plan->Title ?? 'No Title' }}</div>
                            </td>
                            <td>{{ $plan->Year ?? 'N/A' }}</td>
                            <td>{{ $plan->items_count ?? 0 }}</td> {{-- Expected to be loaded via withCount or as an accessor --}}
                            <td>{{ number_format($plan->total_cost ?? 0, 2) }}</td> {{-- Expected as an accessor or calculated property --}}
                            <td>
                                @if($plan->Status instanceof \App\Enums\ProcurementPlanStatusEnum)
                                <span class="badge bg-{{ $plan->Status->color() }}">
                                    {{ $plan->Status->label() }}
                                </span>
                                @else
                                <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </td>
                            <td>{{ $plan->created_assets_count ?? 0 }}</td> {{-- Expected via withCount or accessor --}}
                            <td>{{ $plan->createdBy->name ?? 'System' }}</td> {{-- Assumes createdBy relationship exists --}}
                            <td>{{ $plan->CreatedOn ? $plan->CreatedOn->format('d M Y') : 'N/A' }}</td> {{-- Using CreatedOn as per model const --}}
                            <td>
                                <div class="d-flex gap-1"> {{-- Using gap-1 for slightly less space --}}
                                    {{-- Ensure these route names correctly point to your ProcurementPeriodController methods --}}
                                    <a href="{{ route('procurement-plans.show', $plan->Id) }}" class="btn btn-sm btn-info" title="View Details & Items">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('procurement-plans.edit', $plan->Id) }}" class="btn btn-sm btn-primary" title="Edit Plan Header">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($plan->Status instanceof \App\Enums\ProcurementPlanStatusEnum && $plan->isPending()) {{-- Check instance and then call method --}}
                                    <button type="button" class="btn btn-sm btn-success open-approval-modal"
                                        title="Approve/Reject Plan"
                                        data-bs-toggle="modal"
                                        data-bs-target="#approvalModal"
                                        data-plan-id="{{ $plan->Id }}"
                                        data-plan-title="{{ $plan->Title ?? 'this plan' }}"
                                        data-form-action="{{ route('procurement-plans.approve', $plan->Id) }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                    {{-- Optional: Delete button for Draft plans --}}
                                    @if($plan->Status instanceof \App\Enums\ProcurementPlanStatusEnum && $plan->isDraft())
                                    <form method="POST" action="{{ route('procurement-plans.destroy', $plan->Id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this draft plan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Draft Plan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">No procurement plans found matching your criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($plans) && $plans->hasPages())
            <div class="card-footer bg-light border-top-0">
                {{ $plans->withQueryString()->links() }} {{-- withQueryString preserves filter parameters --}}
            </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="approvalForm" method="POST"> {{-- Action will be set by JavaScript --}}
                    @csrf
                    {{-- Depending on how you handle the approval route, you might need @method('PATCH') or @method('PUT') here if it's not a POST --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="approvalModalLabel">Process Procurement Plan: <span id="modalPlanTitle"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="plan_id" id="modalPlanId"> {{-- Optional: if needed by controller beyond route param --}}
                        <div class="mb-3">
                            <label for="decision" class="form-label">Decision</label>
                            <select class="form-select" name="decision" id="decision" required>
                                <option value="">-- Select Decision --</option>
                                <option value="approve">Approve</option>
                                <option value="reject">Reject</option>
                                <option value="return">Return for Revision</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments</label>
                            <textarea class="form-control" name="comments" id="comments" rows="3" placeholder="Reason or notes for your decision..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Submit Decision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const approvalModalElement = document.getElementById('approvalModal');
        if (approvalModalElement) {
            const approvalModal = new bootstrap.Modal(approvalModalElement);
            const approvalForm = document.getElementById('approvalForm');
            const modalPlanTitle = document.getElementById('modalPlanTitle');
            const modalPlanIdInput = document.getElementById('modalPlanId'); // Optional

            document.querySelectorAll('.open-approval-modal').forEach(button => {
                button.addEventListener('click', function() {
                    const planTitle = this.dataset.planTitle;
                    const formAction = this.dataset.formAction;
                    const planId = this.dataset.planId; // Optional

                    if (approvalForm) {
                        approvalForm.action = formAction;
                    }
                    if (modalPlanTitle) {
                        modalPlanTitle.textContent = planTitle;
                    }
                    if (modalPlanIdInput) { // Optional
                        modalPlanIdInput.value = planId;
                    }
                    approvalModal.show();
                });
            });
        }
    });
</script>
@endpush