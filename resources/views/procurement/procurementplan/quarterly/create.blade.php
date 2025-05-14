@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>📆 Procurement Item Scheduling (Flexible by Quarter or Month)</h4>
    <a href="/planning" class="btn btn-sm btn-outline-secondary">← Back to Plan</a>
  </div>

  <!-- Plan Info -->
  <div class="mb-4 p-3 border rounded bg-light">
    <p><strong>Plan:</strong> Annual Procurement Plan - 2025</p>
    <p><strong>Status:</strong> DRAFT</p>
    <p><strong>Instructions:</strong> Choose whether to break down each item by Quarter or Month, and assign quantity accordingly.</p>
  </div>

  <form method="POST" action="/planning/schedule-flexible">

    <!-- Sample Row 1 -->
    <div class="card mb-4 shadow-sm p-3">
      <div class="mb-2">
        <strong>Item:</strong> Desktop Computers (100 units) — <em>Nairobi, ICT</em>
        <input type="hidden" name="lineItemIds[]" value="501">
      </div>

      <!-- Schedule Mode Toggle -->
      <div class="mb-3">
        <label class="form-label">Breakdown Mode</label>
        <select class="form-select schedule-mode" data-target="#scheduleBlock_501">
          <option value="quarter">By Quarter</option>
          <option value="month">By Month</option>
        </select>
      </div>

      <!-- Quarterly Breakdown -->
      <div id="scheduleBlock_501">
        <div class="row mb-2 schedule-quarter">
          <div class="col-md-3"><label>Q1 Qty</label><input type="number" class="form-control" name="q1_501"></div>
          <div class="col-md-3"><label>Q2 Qty</label><input type="number" class="form-control" name="q2_501"></div>
          <div class="col-md-3"><label>Q3 Qty</label><input type="number" class="form-control" name="q3_501"></div>
          <div class="col-md-3"><label>Q4 Qty</label><input type="number" class="form-control" name="q4_501"></div>
        </div>

        <!-- Monthly Breakdown (Initially hidden) -->
        <div class="row mb-2 schedule-month d-none">
          <div class="col-md-2"><label>Jan</label><input type="number" class="form-control" name="jan_501"></div>
          <div class="col-md-2"><label>Feb</label><input type="number" class="form-control" name="feb_501"></div>
          <div class="col-md-2"><label>Mar</label><input type="number" class="form-control" name="mar_501"></div>
          <div class="col-md-2"><label>Apr</label><input type="number" class="form-control" name="apr_501"></div>
          <div class="col-md-2"><label>May</label><input type="number" class="form-control" name="may_501"></div>
          <div class="col-md-2"><label>Jun</label><input type="number" class="form-control" name="jun_501"></div>
          <div class="col-md-2"><label>Jul</label><input type="number" class="form-control" name="jul_501"></div>
          <div class="col-md-2"><label>Aug</label><input type="number" class="form-control" name="aug_501"></div>
          <div class="col-md-2"><label>Sep</label><input type="number" class="form-control" name="sep_501"></div>
          <div class="col-md-2"><label>Oct</label><input type="number" class="form-control" name="oct_501"></div>
          <div class="col-md-2"><label>Nov</label><input type="number" class="form-control" name="nov_501"></div>
          <div class="col-md-2"><label>Dec</label><input type="number" class="form-control" name="dec_501"></div>
        </div>
      </div>
    </div>

    <!-- Submit -->
    <div class="d-flex justify-content-end">
      <button class="btn btn-primary">💾 Save Schedule</button>
    </div>
  </form>
</div>


<script>
document.querySelectorAll('.schedule-mode').forEach(select => {
  select.addEventListener('change', function () {
    const block = document.querySelector(this.dataset.target);
    const quarterSection = block.querySelector('.schedule-quarter');
    const monthSection = block.querySelector('.schedule-month');
    
    if (this.value === 'quarter') {
      quarterSection.classList.remove('d-none');
      monthSection.classList.add('d-none');
    } else {
      quarterSection.classList.add('d-none');
      monthSection.classList.remove('d-none');
    }
  });
});
</script>

@endsection
