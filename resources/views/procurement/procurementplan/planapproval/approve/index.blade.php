@extends('layouts.app')
@section('title', 'Review Procurement Plan')
@section('content')

<div class="container mt-4">
    <h3>Procurement Plan Approval</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Form for selecting a draft plan -->
    <form method="GET" action="{{ route('planning.approval.index') }}" id="plan-selection-form">
        <div class="mb-3">
            <label for="PlanID" class="form-label">Select Submitted Plan</label>
            <select name="PlanID" id="PlanID" class="form-select" onchange="this.form.submit()">
                <option value="">-- Choose Plan To Approve --</option>
                @foreach($draftPlans as $planOption)
                    <option
                        value="{{ $planOption->PlanID }}" {{ old('PlanID', request()->PlanID) == $planOption->PlanID ? 'selected' : '' }}>
                        {{ $planOption->ReferenceNumber }} - {{ $planOption->Title }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
    <div id="plan-details" class="mt-4">
        @if($selectedPlan)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>🔎 Review Procurement Plan – {{ $selectedPlan->ReferenceNumber ?? 'N/A' }}</h4>
                <span
                    class="badge bg-primary">Logged in as: {{ Auth::user()->role() ? Auth::user()->role()->name : 'Unknown User' }}</span>
            </div>
            <!-- Plan Summary -->
            <div class="row mb-4 bg-light p-3 border rounded">
                <div class="col-md-4"><strong>Title:</strong> {{ $selectedPlan->Title }}</div>
                <div class="col-md-4"><strong>Status:</strong>
                    <span class="badge bg-{{ $selectedPlan->Status->badgeColor() }} text-dark">
                        {{ $selectedPlan->Status->label() }}
                    </span>
                </div>
                <div class="col-md-4"><strong>Current Level:</strong> {{ $selectedPlan->CurrentApprLevel ?? 'N/A' }}
                </div>
            </div>

            <!-- Plan Items Table -->
            <div class="mb-4">
                <h5>📋 Plan Items Summary</h5>
                @if($selectedPlan->lineItems->isNotEmpty())
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Branch</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Budget Line</th>
                            <th>Procurement Method</th>
                            <th>Schedule</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($selectedPlan->lineItems as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->item->ItemName ?? 'N/A' }}</td>
                                <td>{{ $item->branch->Name ?? 'N/A' }}</td>
                                <td>{{ $item->MergedQty ?? 'N/A' }}</td>
                                <td>{{ $item->EstimatedUnitCost ?? 'N/A' }}</td>
                                <td>{{ $item->MergedQty && $item->EstimatedUnitCost ? number_format($item->MergedQty * $item->EstimatedUnitCost, 2) : 'N/A' }}</td>
                                <td>{{ $item->budgetLine->Description ?? 'N/A' }}</td>
                                <td>{{ $item->procurementMode->Name ?? 'N/A' }}</td>
                                <td>{{ $item->SchedulePeriod ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning">No items available for this plan.</div>
                @endif
            </div>

            <!-- Approval Form -->
            <div class="card p-4 shadow-sm border rounded">
                <h5>✅ Approval Decision</h5>
                <form method="POST" action="{{ route('planning.submitDecision') }}">
                    @csrf
                    <input type="hidden" name="planId" value="{{ $selectedPlan->PlanID }}">

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" name="role" class="form-control"
                               value="{{ Auth::user()->role() ? Auth::user()->role()->name : 'N/A' }}" required
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select" required>
                            <option value="">-- Choose Action --</option>
                            <option value="APPROVED">✅ Approve</option>
                            <option value="REJECTED">❌ Reject</option>
                            <option value="RETURNED">↩️ Return</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="comments" rows="3" class="form-control" required></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">📤 Submit</button>
                    </div>
                </form>
            </div>
        @else
            <div class="alert alert-info">Please select a draft plan to view details.</div>
        @endif
    </div>
</div>
<!-- JavaScript to handle plan selection -->
<script>
    function handlePlanSelection(selectElement) {
        const selectedValue = selectElement.value;
        if (selectedValue === '') {
            window.location.href = "{{ route('planning.approval.index') }}";
        } else {
            selectElement.form.submit();
        }
    }
</script>
@endsection
