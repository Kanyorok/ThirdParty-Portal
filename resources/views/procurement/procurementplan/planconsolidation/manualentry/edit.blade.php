@extends('layouts.app')
@section('title', 'Edit Procurement Plan Item')
@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4>Edit Procurement Plan Item</h4>
        <form method="POST" action="{{ route('planmanualinput.update', $lineItem->LineItemID) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="PlanID" class="form-label">Procurement Plan</label>
                <select name="PlanID" id="PlanID" class="form-select" required>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->PlanID }}" {{ $lineItem->PlanID == $plan->PlanID ? 'selected' : '' }}>
                            {{ $plan->Title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="ItemID" class="form-label">Item</label>
                <select name="ItemID" id="ItemID" class="form-select" required>
                    @foreach ($items as $item)
                        <option value="{{ $item->Id }}" {{ $lineItem->ItemID == $item->Id ? 'selected' : '' }}>
                            {{ $item->ItemName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="CategoryID" class="form-label">Category</label>
                <select name="CategoryID" id="CategoryID" class="form-select" required>
                    @foreach ($categories as $category)
                        @foreach($categories as $category)
                            <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control"
                       value="{{ $lineItem->MergedQty }}" min="1" required>
            </div>

            <div class="mb-3">
                <label for="unit_of_measure" class="form-label">Unit of Measure</label>
                <input type="text" name="unit_of_measure" id="unit_of_measure" class="form-control"
                       value="{{ $lineItem->UnitOfMeasure }}" required>
            </div>

            <div class="mb-3">
                <label for="estimated_cost" class="form-label">Estimated Cost</label>
                <input type="number" step="0.01" name="estimated_cost" id="estimated_cost" class="form-control"
                       value="{{ $lineItem->EstimatedUnitCost }}" required>
            </div>

            <div class="mb-3">
                <label for="schedule_period" class="form-label">Planned Quarter</label>
                <input type="text" name="schedule_period" id="schedule_period" class="form-control"
                       value="{{ $lineItem->SchedulePeriod }}" required>
            </div>

            <div class="mb-3">
                <label for="expected_delivery_date" class="form-label">Expected Delivery Date</label>
                <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control"
                       value="{{ \Carbon\Carbon::parse($lineItem->ExpectedDeliveryDate)->format('Y-m-d') }}" required>
            </div>

            <div class="mb-3">
                <label for="budget_line_id" class="form-label">Budget Line</label>
                <select name="budget_line_id" id="budget_line_id" class="form-select" required>
                    @foreach ($budgetLines as $budgetLine)
                        <option value="{{ $budgetLine->BudgetLineID }}">
                            {{ $budgetLine->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" class="form-control">{{ $lineItem->ChangeRemarks }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Item</button>
            <a href="{{ route('procurement.procurementplan.planconsolidation.manualentry.index') }}"
               class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
