@extends('layouts.app')
@section('title', 'Plan Consolidation')
@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>📋 Procurement Plan Scheduling Summary</h4>
            <a href="{{ route('Procurement-Plan-Schedule.index') }}" class="btn btn-sm btn-outline-secondary">
                ← Back to Plan
            </a>
        </div>

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
                    <tbody id="items-table-body"></tbody>
                </table>
            </div>
        </form>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const loadBtn = document.getElementById('load-items-btn');
                    const planSelect = document.getElementById('approved-plan-select');
                    const tbody = document.getElementById('items-table-body');

                    const baseUrl = @json(route('Procurement-Plan-Schedule.view', ['PlanId' => '__PLAN_ID__']));

                    loadBtn.addEventListener('click', function () {
                        const planId = planSelect.value;
                        if (!planId || planId === '-- Choose Plan --') {
                            alert('Please select a plan.');
                            return;
                        }

                        document.getElementById('approved-plan-id-hidden').value = planId;

                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="spinner-border text-primary me-2" role="status">
                                            <span class="visually-hidden">Loading Items...</span>
                                        </div>
                                        <strong>Loading Items...</strong>
                                    </div>
                                </td>
                            </tr>`;

                        fetch(`${baseUrl.replace('__PLAN_ID__', planId)}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                tbody.innerHTML = ''; // Clear the spinner

                                if (Array.isArray(data) && data.length > 0) {
                                    data.forEach((line, index) => {
                                        const isScheduled = line.Status && line.Status !== 'Not Scheduled';

                                        const row = `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${line.item_name}</td>
                                            <td>${line.MergedQty}</td>
                                            <td>${line.ScheduleQTY ?? '-'}</td>
                                            <td>${line.ScheduleType ?? '-'}</td>
                                            <td><span class="badge ${isScheduled ? 'bg-success' : 'bg-secondary'}">${line.Status ?? 'Not Scheduled'}</span></td>
                                            <td>
                                                <a href="/procurement/Procurement-Plan-Schedule/edit/${line.LineItemID}?plan_id=${planId}" class="btn btn-sm btn-primary">Schedule</a>
                                            </td>
                                        </tr>`;
                                        tbody.insertAdjacentHTML('beforeend', row);
                                    });
                                } else {
                                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No lines created for the selected plan.</td></tr>';
                                    nWarning('No lines created for the selected plan.');
                                }
                            })
                            .catch(error => {
                                console.error('Error loading items:', error);
                                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Failed to load items.</td></tr>';
                                nError('An error occurred while loading items.');
                            });
                    });
                });
            </script>
        @endpush
    </div>
@endsection
