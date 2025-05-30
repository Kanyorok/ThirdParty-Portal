@extends('layouts.app')
@section('title', '')
@section('content')

<div class="card p-4 shadow rounded-4">
  <h4 class="mb-4">🧾 Create Head Office Procurement Plan</h4>

  <form method="POST" action="{{ route('procurementplanmaintain.store') }}">
  @csrf
    <div class="mb-3">
      <label class="form-label">Plan Title</label>
      <input type="text" class="form-control" name="Title" placeholder="e.g. Annual Procurement Plan - 2025">
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Plan Year</label>
        <select class="form-select" name="FiscalYear">
          <option>2025</option>
          <option>2026</option>
          <option>2027</option>
        </select> 
      </div>
      <div class="col-md-4 d-none">
        <label class="form-label">Created By</label>
        <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
        <input type="hidden" name="CreatedBy" value="{{ auth()->user()->Id }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">Status</label>
        <input type="text" class="form-control" value="Draft" readonly>
        <input type="hidden" name="Status" value="Draft">
      </div>
<div class="col-md-4 d-flex align-items-end">
    <button type="submit" class="btn btn-primary w-100">Save Plan</button>
  </div>

    <!-- Selection Mode -->
    <div class="mb-4">
      <label class="form-label">How would you like to create this plan?</label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="planSource" id="manualOption" checked>
        <label class="form-check-label" for="manualOption">
          Create Manually (Enter line items yourself)
        </label>
      </div>
      <div class="form-check mt-2">
        <input class="form-check-input" type="radio" name="planSource" id="fromNeedsOption">
        <label class="form-check-label" for="fromNeedsOption">
          Generate From Approved Needs (Raised by Branches)
        </label>
      </div>
    </div>

    <!-- Manual Entry Button -->
    <div id="manualActions" class="mt-4">
       <a href="{{ route('planmanualinput.index') }}" type="button" onclick="location.href='/planning/manual-entry'">
        Proceed to Manual Entry
</a>
    </div>

    <!-- From Needs Button -->
    <div id="autoActions" class="mt-4 d-none">
      <a href="{{ route('planfromneeds.create') }}" type="button" onclick="location.href='/planning/generate-from-needs'">
        Generate Plan from Approved Needs
</a>
    </div>
  </form>
</div>
<script>
  document.getElementById('manualOption').addEventListener('change', function () {
    document.getElementById('manualActions').classList.remove('d-none');
    document.getElementById('autoActions').classList.add('d-none');
  });

  document.getElementById('fromNeedsOption').addEventListener('change', function () {
    document.getElementById('manualActions').classList.add('d-none');
    document.getElementById('autoActions').classList.remove('d-none');
  });
</script>

@endsection