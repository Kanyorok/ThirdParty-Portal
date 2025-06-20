@extends('layouts.app')
@section('title', 'Budget Entry')
 
@section('content')
 
@if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
 
@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
 
<form action="{{route('monthly.store')}}" method="POST">
@csrf
@method('POST')
 <input type="hidden" name="projectionID" value="{{ $projectionID }}" >
<div class="card mt-4 p-4">
    <h5>📆 Monthly Allocations</h5>
    <p class="text-muted">Enter allocation amounts with your Specified Currency</p>
 
    <div class="row">
        @foreach ([
            'Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6',
            'Month 7', 'Month 8', 'Month 9', 'Month 10', 'Month 11', 'Month 12'
        ] as $month)
            <div class="col-md-4 mb-3">
                <label class="form-label">{{ $month }}</label>
                <input type="number" name="MonthlyAllocations[]" class="form-control"
                       placeholder="e.g. 100000" step="0.01" value="0.00" min="0" required>
            </div>
        @endforeach
        <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('budgetprojections.index') }}" class="btn btn-secondary">
            ← Back
        </a>
        <button type="submit" class="btn btn-primary" onclick="$this.disable = true; this.innerHTML = 'Saving...'; $this.form.submit();">
            💾 Save Allocations
        </button>
    </div>
  </div>
</div>
</form>
@endsection