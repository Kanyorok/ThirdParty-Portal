@extends('layouts.app')

@section('title', 'Add Procurement Plan')

@section('content')
<div class="container">
    <h3>Add Procurement Plan for Period: {{ $period->Name ?? $period->StartDate }}</h3>

    <form action="{{ route('procurement-periods.plans.store', $period->Id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="ItemId" class="form-label">Select Item</label>
            <select class="form-control" id="ItemId" name="ItemId" required>
                <option value="">-- Choose Item --</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}"
                        data-unit="{{ $item->UOM }}"
                        data-description="{{ $item->Description }}"
                        data-cost="{{ $item->UnitPrice }}"
                        data-name="{{ $item->Name }}">{{ $item->Name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Unit</label>
            <input type="text" id="Unit" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea id="Description" class="form-control" rows="2" readonly></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Estimated Unit Cost</label>
            <input type="text" id="EstimatedUnitCost" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label for="Quantity" class="form-label">Quantity</label>
            <input type="number" name="Quantity" id="Quantity" class="form-control" min="1" required>
        </div>

        <button class="btn btn-success">Save Plan</button>
        <a href="{{ route('procurement-periods.index', $period->Id) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('ItemId').addEventListener('change', function () {
        let selected = this.options[this.selectedIndex];
        document.getElementById('Unit').value = selected.getAttribute('data-unit');
        document.getElementById('Description').value = selected.getAttribute('data-description');
        document.getElementById('EstimatedUnitCost').value = selected.getAttribute('data-cost');
    });
});
</script>
@endsection
