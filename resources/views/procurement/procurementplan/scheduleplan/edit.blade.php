@extends('layouts.app')
@section('title', 'Edit Schedule')
@section('content')

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>✏️ Edit Procurement Item Schedule</h4>
    <a href="{{ route('Procurement-Plan-Schedule.index') }}" class="btn btn-sm btn-outline-secondary">
      ← Back
    </a>
  </div>

  <form action="{{ route('Procurement-Plan-Schedule.store') }}" method="POST" class="border rounded p-4 bg-white shadow-sm">
    @csrf
    <input type="hidden" name="pending_plan_id" value="{{ $plan->PlanID }}">
    <input type="hidden" name="lineItemIds[]" value="{{ $lineItem->LineItemID }}">

    @php
      $periods = $lineItem->schedulePlan->periods->keyBy('SchedulePeriod') ?? collect();
      $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'];
    @endphp

    <div class="mb-4 p-3 border rounded bg-light">
        <p><strong>Plan:</strong> {{ $plan->ReferenceNumber ?? 'N/A' }} <strong>Year: </strong> {{ $plan->FiscalYear }}</p>
        <p><strong>Item Name:</strong> {{ $lineItem->item->ItemName ?? 'Unnamed Item' }}</p>
        <p><strong>Instructions:</strong> Choose whether to break down each item by Quarter or Month, and update the quantity accordingly.</p>
    </div>

    <div class="form-group mb-4">
      <label for="modeSelect" class="form-label">Mode</label>
      <select id="modeSelect" name="mode_{{ $lineItem->LineItemID }}" class="form-control schedule-mode" data-id="{{ $lineItem->LineItemID }}">
        <option value="quarter" 
          {{ $periods->keys()->first() && str_starts_with($periods->keys()->first(), 'Q') ? 'selected' : '' }}>
          Quarterly
        </option>
        <option value="month" 
          {{ $periods->keys()->first() && in_array(strtolower($periods->keys()->first()), $months) ? 'selected' : '' }}>
          Monthly
        </option>
      </select>
    </div>

    {{-- Quarterly Inputs --}}
    <div class="quarterly-input q-{{ $lineItem->LineItemID }} mb-4">
      <label class="form-label d-block mb-2">Quarterly Breakdown</label>
      <div class="d-flex align-items-center gap-2 mb-2">
        @for ($q = 1; $q <= 4; $q++)
          <div class="text-center" style="width: 70px;">
            <strong>Q{{ $q }}</strong>
          </div>
        @endfor
      </div>
      <div class="d-flex align-items-center gap-2">
        @for ($q = 1; $q <= 4; $q++)
          <input 
            type="number" 
            name="q{{ $q }}_{{ $lineItem->LineItemID }}" 
            value="{{ $periods['Q' . $q]->ScheduleQTY ?? 0 }}"
            class="form-control text-center" 
            style="width: 70px;" 
            min="0"
            placeholder="0"
          >
        @endfor
      </div>
    </div>

    {{-- Monthly Inputs --}}
    <div class="monthly-input m-{{ $lineItem->LineItemID }}" style="display: none; margin-bottom: 1.5rem;">
      <label class="form-label d-block mb-2">Monthly Breakdown</label>
      <div class="d-flex flex-wrap gap-2 mb-2">
        @foreach ($months as $month)
          <div class="text-center" style="width: 70px;">
            <strong>{{ strtoupper($month) }}</strong>
          </div>
        @endforeach
      </div>
      <div class="d-flex flex-wrap gap-2">
        @foreach ($months as $month)
          <input 
            type="number" 
            name="{{ $month }}_{{ $lineItem->LineItemID }}" 
            value="{{ $periods[strtoupper($month)]->ScheduleQTY ?? 0 }}"
            class="form-control text-center" 
            style="width: 70px;" 
            min="0"
            placeholder="0"
          >
        @endforeach
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Schedule</button>
  </form>
</div>

@push('scripts')
<script>
  document.querySelectorAll('.schedule-mode').forEach(select => {
    select.addEventListener('change', function () {
      const itemId = this.dataset.id;
      const mode = this.value;

      document.querySelector(`.quarterly-input.q-${itemId}`).style.display = (mode === 'quarter') ? '' : 'none';
      document.querySelector(`.monthly-input.m-${itemId}`).style.display = (mode === 'month') ? '' : 'none';
    });

    // Trigger initial display on page load
    select.dispatchEvent(new Event('change'));
  });
</script>
@endpush

@endsection
