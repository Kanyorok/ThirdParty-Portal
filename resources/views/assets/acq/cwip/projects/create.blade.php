{{-- resources/views/assets/acq/cwip/projects/create.blade.php --}}
@extends('layouts.app')
@section('title','New CWIP Project')

@section('content')
<div class="container my-3">
  <div class="card shadow-sm rounded-3">
    <div class="card-header bg-light py-2 px-3">
      <h6 class="mb-0 text-muted">Create CWIP Project</h6>
    </div>

    <div class="card-body p-3">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="post" action="{{ route('assets.acq.cwip-projects.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Project Code</label>
            <input name="ProjectCode" value="{{ old('ProjectCode') }}" class="form-control form-control-sm" required>
          </div>

          <div class="col-md-5">
            <label class="form-label">Project Name</label>
            <input name="ProjectName" value="{{ old('ProjectName') }}" class="form-control form-control-sm" required>
          </div>

          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="Status" class="form-select form-select-sm" required>
              @foreach(['Open','Closed'] as $s)
                <option value="{{ $s }}" @selected(old('Status','Open')===$s)>{{ $s }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="StartDate" value="{{ old('StartDate') }}" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="EndDate" value="{{ old('EndDate') }}" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label">Default Class</label>
            <select name="ClassID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($classes as $c)
                <option value="{{ $c->Id }}" @selected(old('ClassID')==$c->Id)>{{ $c->Name }} ({{ $c->Code ?? '' }})</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Default Location</label>
            <select name="LocationID" class="form-select form-select-sm">
              <option value="">—</option>
              @foreach($locations as $l)
                <option value="{{ $l->Id }}" @selected(old('LocationID')==$l->Id)>{{ $l->Code }} — {{ $l->Site }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Capex Budget</label>
            <input type="number" step="0.01" min="0" name="CapexBudget" value="{{ old('CapexBudget') }}" class="form-control form-control-sm">
          </div>

          <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="Notes" rows="2" class="form-control form-control-sm">{{ old('Notes') }}</textarea>
          </div>
        </div>

        <div class="mt-3">
          <a href="{{ route('assets.acq.cwip-projects.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
          <button class="btn btn-primary btn-sm">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
