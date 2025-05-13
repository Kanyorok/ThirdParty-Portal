@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">📅 Add Budget Fiscal Year</h4>

  <div class="card shadow">
    <div class="card-body">
      <form>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label">Fiscal Year Name</label>
            <input type="text" class="form-control" placeholder="e.g. FY2024/25">
          </div>
          <div class="col-md-4">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-2">
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" id="isCurrent">
              <label class="form-check-label" for="isCurrent">Current Fiscal Year</label>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" id="isLocked">
              <label class="form-check-label" for="isLocked">Locked</label>
            </div>
          </div>
        </div>

        <div class="text-end">
          <button class="btn btn-success">💾 Save Fiscal Year</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection