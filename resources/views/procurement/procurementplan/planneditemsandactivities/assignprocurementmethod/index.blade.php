@extends('layouts.app')
@section('title', 'Plan Consolidation')
@section('content')

<div class="container mt-4">
    <h4 class="mb-4"> Assign Procurement Method</h4>

    <!-- Plan Selection -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Select Approved Plan</label>
                    <select class="form-select" id="approved-plan-select">
                        <option selected disabled>-- Choose Plan --</option>
                        @foreach ($approvedPlans as $plan)
                            <option value="{{ $plan->PlanID }}">{{ $plan->ReferenceNumber }} – {{ $plan->Title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button id="load-items-btn" type="button" class="btn btn-outline-primary w-100"> Load Items</button>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('procurement-set-method.store') }}">
        @csrf

        <input type="hidden" name="approved_plan_id" id="approved-plan-id-hidden">

        <!-- Items Table -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Planned Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Est. Cost</th>
                                <th>Assigned Method</th>
                                <th>Assign Method</th>
                                <th>Justification (if override)</th>
                            </tr>
                        </thead>
                        <tbody id="items-table-body">
                            <!-- Items will load here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="text-end">
            <button type="submit" class="btn btn-primary"> Save Assigned Methods</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back();">Cancel</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const procurementModes = @json($procurementModes);

    document.addEventListener('DOMContentLoaded', function () {
        const loadBtn = document.getElementById('load-items-btn');
        const planSelect = document.getElementById('approved-plan-select');

        loadBtn.addEventListener('click', function () {
            const planId = planSelect.value;
            if (!planId) {
                alert('Please select a plan.');
                return;
            }

            document.getElementById('approved-plan-id-hidden').value = planId;

            fetch(`/procurement/procurement/set-method/plan-items/${planId}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('items-table-body');
                    tbody.innerHTML = '';

                    const selectOptions = procurementModes.map(mode =>
                        `<option value="${mode.id}">${mode.Name}</option>`
                    ).join('');

                    data.forEach(line => {
                        const row = `
                            <tr>
                                <td>${line.item_name}</td>
                                <td>${line.MergedQty}</td>
                                <td>KES ${parseFloat(line.EstimatedUnitCost).toLocaleString()}</td>
                                <td><span class="badge bg-secondary">${line.ProcurementMethod || 'N/A'}</span></td>
                                <td>
                                    <select class="form-select" name="assigned_method[${line.LineItemID}]">
                                        <option value="" disabled selected>-- Select Method --</option>
                                        ${selectOptions}
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="justification[${line.LineItemID}]" placeholder="Only if changing from suggestion">
                                </td>
                            </tr>
                        `;
                        tbody.insertAdjacentHTML('beforeend', row);
                    });
                })
                .catch(error => {
                    console.error('Error loading items:', error);
                    alert('An error occurred while loading items.');
                });
        });
    });
</script>
@endpush


@endsection
