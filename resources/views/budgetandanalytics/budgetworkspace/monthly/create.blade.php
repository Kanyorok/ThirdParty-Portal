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

<div class="card mt-4 p-4">
    <h5>📆 Monthly Allocations</h5>
    <p class="text-muted">Enter allocation amounts in Kenyan Shillings (KES)</p>

    <div class="row">
        @foreach ([
            'January', 'February', 'March', 'April',
            'May', 'June', 'July', 'August',
            'September', 'October', 'November', 'December'
        ] as $month)
            <div class="col-md-4 mb-3">
                <label class="form-label">{{ $month }} (KES)</label>
                <input type="number" name="MonthlyAllocations[{{ $month }}]" class="form-control"
                       placeholder="e.g. 100000" step="0.01" min="0" required>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('entrybyproduct.index') }}" class="btn btn-secondary">
            ← Back
        </a>
        <button type="submit" class="btn btn-primary">
            💾 Save Allocations
        </button>
    </div>
</div>

@endsection
