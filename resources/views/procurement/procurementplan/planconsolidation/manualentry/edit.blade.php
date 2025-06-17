@extends('layouts.app')
@section('title', 'Edit Procurement Plan Item')
@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4>Edit Procurement Plan Item</h4>
        <form method="POST" action="{{ route('plan.manual-input.update', ['lineItem' => $lineItem->LineItemID]) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="PlanID" class="form-label">Procurement Plan</label>
                    <select id="PlanID" class="form-select" disabled>
                        @foreach ($plans as $plan)
                            <option
                                value="{{ $plan->PlanID }}" {{ $lineItem->PlanID == $plan->PlanID ? 'selected' : '' }}>
                                {{ $plan->Title }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="PlanID" value="{{ $lineItem->PlanID }}">
                </div>

                <div class="mb-3 col-md-6">
                    <label for="ItemID" class="form-label">Item</label>
                    <select id="ItemID" class="form-select" disabled>
                        @foreach ($items as $item)
                            <option value="{{ $item->Id }}" {{ $lineItem->ItemID == $item->Id ? 'selected' : '' }}>
                                {{ $item->ItemName }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="ItemID" value="{{ $lineItem->ItemID }}">
                </div>

                <div class="mb-3 col-md-6">
                    <label for="CategoryID" class="form-label">Category</label>
                    <select id="CategoryID" class="form-select" disabled>
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->Id }}" {{ $lineItem->item->CategoryID == $category->Id ? 'selected' : '' }}>
                                {{ $category->Name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="CategoryID" value="{{ $lineItem->item->CategoryID }}">
                </div>

                <div class="mb-3 col-md-6">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control"
                           value="{{ $lineItem->MergedQty }}" min="1" required>
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label">Unit of Measure</label>
                    <input type="text" class="form-control" value="{{ $lineItem->item->uom->Name ?? 'N/A' }}" readonly>
                </div>

        <div class="mb-3 col-md-6">
            <label for="estimated_cost" class="form-label">Estimated Unit Cost</label>
            <input type="number" step="0.01" name="estimated_cost" id="estimated_cost" class="form-control"
                   value="{{ $lineItem->EstimatedUnitCost }}" required>
        </div>

                <div class="mb-3 col-md-6">
                    <label for="schedule_period" class="form-label">Planned Quarter</label>
                    <input type="text" name="schedule_period" id="schedule_period" class="form-control"
                           value="{{ $lineItem->SchedulePeriod }}" required>
                </div>

                <div class="mb-3 col-md-6">
                    <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                    <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control"
                           value="{{ \Carbon\Carbon::parse($lineItem->ExpectedDeliveryDate)->format('Y-m-d') }}"
                           required>
                </div>

                <div class="mb-3 col-md-6">
                    <label for="budget_line_id" class="form-label">Budget Line</label>
                    <select name="budget_line_id" id="budget_line_id" class="form-select" required>
                        @foreach ($budgetLines as $budgetLine)
                            <option
                                value="{{ $budgetLine->BudgetLineID }}" {{ $lineItem->BudgetLineID == $budgetLine->BudgetLineID ? 'selected' : '' }}>
                                {{ $budgetLine->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 col-md-6">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control">{{ $lineItem->ChangeRemarks }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary">Update Item</button>
                <a href="{{ route('procurement.procurementplan.planconsolidation.manualentry.index') }}"
                   class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
