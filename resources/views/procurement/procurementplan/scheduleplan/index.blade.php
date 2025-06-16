@extends('layouts.app')
@section('title', 'Plan Consolidation')
@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>📋 Procurement Plan Scheduling Summary</h4>
            <a href="/" class="btn btn-sm btn-outline-secondary">← Back to Plan</a>
        </div>

        <!-- Plan Selection -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Select Plan</label>
                        <select class="form-select" id="approved-plan-select">
                            <option selected disabled>-- Choose Plan --</option>
                            @foreach ($draftedplans as $plan)
                                <option value="{{ $plan->PlanID }}">{{ $plan->ReferenceNumber }}
                                    – {{ $plan->Title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button id="load-items-btn" type="button" class="btn btn-outline-primary w-100"> Load Items
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('procurement-set-method.store') }}">
            @csrf

            <input type="hidden" name="approved_plan_id" id="approved-plan-id-hidden">
            <div class="table-responsive">
                <table class="table table-bordered align-middle table-striped">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Total Qty</th>
                        <th>Scheduled Qty</th>
                        <th>Schedule Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody id="items-table-body">
                    </tbody>
                </table>
            </div>
        </form>


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loadBtn = document.getElementById('load-items-btn');
        const planSelect = document.getElementById('approved-plan-select');
        const hiddenPlanId = document.getElementById('approved-plan-id-hidden');

                    const baseUrl = @json(route('Procurement-Plan-Schedule.view', ['PlanId' => '__PLAN_ID__']));

                    loadBtn.addEventListener('click', function () {
                        const planId = planSelect.value;
                        if (!planId) {
                            alert('Please select a plan.');
                            return;
                        }

            hiddenPlanId.value = planId;

            const fetchUrl = baseUrl.replace('__PLAN_ID__', planId);

            fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('items-table-body');
                    tbody.innerHTML = '';

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach((line, index) => {
                            const mergedQty = parseInt(line.MergedQty) || 0;
                            const scheduledQty = parseInt(line.ScheduleQTY) || 0;

                            let statusLabel = '';
                            let badgeClass = '';

                            if (scheduledQty === 0) {
                                statusLabel = 'Not Scheduled';
                                badgeClass = 'bg-danger';
                            } else if (scheduledQty < mergedQty) {
                                statusLabel = 'Partially Scheduled';
                                badgeClass = 'bg-warning text-dark';
                            } else if (scheduledQty === mergedQty) {
                                statusLabel = 'Fully Scheduled';
                                badgeClass = 'bg-success';
                            } else {
                                statusLabel = 'Overscheduled';
                                badgeClass = 'bg-secondary';
                            }

                            const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${line.item_name}</td>
                                    <td>${mergedQty}</td>
                                    <td>${scheduledQty > 0 ? scheduledQty : '-'}</td>
                                    <td>${line.ScheduleType ?? '-'}</td>
                                    <td><span class="badge ${badgeClass}">${statusLabel}</span></td>
                                    <td>
                                        <a href="/procurement/Procurement-Plan-Schedule/create/${line.LineItemID}?plan_id=${planId}" class="btn btn-sm btn-primary">Schedule</a> |
                                        <a href="/procurement/Procurement-Plan-Schedule/edit/${line.LineItemID}?plan_id=${planId}" class="btn btn-sm btn-warning">Edit</a> |
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.history.back();">Cancel</button>
                                    </td>
                                </tr>
                            `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                    } else {
                        alert('No lines created for the selected plan.');
                    }
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
