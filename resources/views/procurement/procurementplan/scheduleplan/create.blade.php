@extends('layouts.app')
@section('title', 'Item Scheduling')
@section('content')

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📆 Procurement Item Scheduling (Flexible by Quarter or Month)</h4>
    <a href="{{ route('Procurement-Plan-Schedule.index') }}" class="btn btn-sm btn-outline-secondary">← Back to Plan</a>
  </div>

  <!-- Plan Info -->
  <div class="mb-4 p-3 border rounded bg-light">
    <p><strong>Plan:</strong> Annual Procurement Plan - 2025</p>
    <p><strong>Status:</strong> DRAFT</p>
    <p><strong>Instructions:</strong> Choose whether to break down each item by Quarter or Month, and assign quantity accordingly.</p>
  </div>

  <form action="{{ route('Procurement-Plan-Schedule.store') }}" method="POST">
    @csrf
    <input type="hidden" name="pending_plan_id" value="{{ $plan->PlanID }}">

    <table class="table table-bordered align-middle">
    <thead class="table-secondary">
        <tr>
            <th>Item Name</th>
            <th>Mode</th>
            <th>Schedule Breakdown</th>
        </tr>
        <tr>
            <th></th><th></th><th>
                <small>Quarterly: Q1, Q2, Q3, Q4 | Monthly: Jan to Dec</small>
            </th>
        </tr>
    </thead>
    <tbody>
        @php $months = ['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec']; @endphp
        @foreach ($plan->lineItems as $lineItem)
            <tr>
                <input type="hidden" name="lineItemIds[]" value="{{ $lineItem->LineItemID }}">
                <td>{{ $lineItem->item->ItemName ?? 'Unnamed' }}</td>
                <td>
                    <select name="mode_{{ $lineItem->LineItemID }}" class="form-control schedule-mode" data-id="{{ $lineItem->LineItemID }}">
                        <option value="quarter" selected>Quarterly</option>
                        <option value="month">Monthly</option>
                    </select>
                </td>

                <td>
                    {{-- Quarterly Inputs --}}
                    <div class="quarterly-input q-{{ $lineItem->LineItemID }}">
                        @for ($q = 1; $q <= 4; $q++)
                            <input type="number" name="q{{ $q }}_{{ $lineItem->LineItemID }}" class="form-control d-inline-block m-1" style="width: 60px;" min="0" placeholder="Q{{ $q }}">
                        @endfor
                    </div>

                    {{-- Monthly Inputs (hidden by default) --}}
                    <div class="monthly-input m-{{ $lineItem->LineItemID }}" style="display: none;">
                        @foreach ($months as $month)
                            <input type="number" name="{{ $month }}_{{ $lineItem->LineItemID }}" class="form-control d-inline-block m-1" style="width: 60px;" min="0" placeholder="{{ ucfirst($month) }}">
                        @endforeach
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


    <button type="submit" class="btn btn-primary">Save Schedule</button>
  </form>
</div>

@push('scripts')
<script>
  document.querySelectorAll('.schedule-mode').forEach(select => {
    select.addEventListener('change', function () {
      const itemId = this.dataset.id;
      const mode = this.value;

      // Show quarterly inputs if 'quarter' selected, else monthly
      document.querySelectorAll(`.quarterly-input.q-${itemId}`).forEach(el => {
        el.style.display = (mode === 'quarter') ? '' : 'none';
      });
      document.querySelectorAll(`.monthly-input.m-${itemId}`).forEach(el => {
        el.style.display = (mode === 'month') ? '' : 'none';
      });
    });

    // Trigger change on page load to set correct visibility
    select.dispatchEvent(new Event('change'));
  });
</script>
@endpush

@endsection
