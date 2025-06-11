@extends('layouts.app')
@section('title', 'New Budget Line & GL Mapping')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>There were some errors with your submission:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="card mb-4">
  <div class="card-header bg-primary text-white">➕ Add Budget Line & GL Mapping</div>
  <div class="card-body">
    <form action="{{ route('budgetlinemapping.store') }}" method="POST">
      @csrf
      @method('POST')
      <!-- 🧾 Budget Line Entry -->
      <div class="mb-3">
        <label class="form-label">Budget Line Name</label>
        <input type="text" class="form-control" name="LineName" placeholder="e.g. Interest Income, Loan Fees" required>
        @error('LineName')
             <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" rows="2" name="Description" placeholder="Describe this budget line..."></textarea>
        @error('Description')
             <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>

      <hr class="my-4">

      <!-- 🔗 CBS GL Mapping -->
      <h6>🔗 CBS GL Accounts (Multiple)</h6>
      <div class="mb-3">
        <label class="form-label">Select CBS GLs</label>
        <select multiple class="form-select" name="GLS[]" required>
          @foreach ($gls as $item)
              <option value="{{ $item->Id }}">GL00{{ $item->Id }} - {{ $item->Description }}</option>
          @endforeach
        </select>
        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple GLs.</div>
      </div>

      <!-- 🔗 ERP GL Mapping -->
      {{-- <div class="mb-3">
        <label class="form-label">ERP GL Account (Optional)</label>
        <select class="form-select">
          <option selected disabled>-- Select ERP GL --</option>
          <option value="ERP001">ERP001 - Interest Revenue</option>
          <option value="ERP002">ERP002 - Other Income</option>
        </select>
      </div> --}}

      <!-- 🔘 Primary Flag -->
      <div class="form-check mb-3">
        <input class="form-check-input" name="IsDefault" type="checkbox" id="primaryCheck">
        <label class="form-check-label" for="primaryCheck">
          Mark as Primary Mapping
        </label>
      </div>

      <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Submitting...'; this.form.submit(); }">
        💾 Save Budget Line & Mapping</button>
    </form>
  </div>
</div>
@endsection