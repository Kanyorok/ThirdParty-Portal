@extends('layouts.app')
@section('title', 'Edit Budget Period')
@section('content')

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

@if ($errors->any())
  <div class="alert alert-danger">
      <ul class="mb-0">
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
@endif

<form method="POST" action="{{ route('budgetperiod.update', $periods->Id) }}">
    @csrf
    @method('PUT')
    <div class="card p-4">
        <h5>✏️ Edit Budget Period</h5>

        <div class="mb-3">
            <label for="fiscalYear" class="form-label">Fiscal Year</label>
            <input type="number" class="form-control" id="fiscalYear" name="fiscalYear"
                value="{{ old('fiscalYear', $periods->fiscalYear) }}" required>
        </div>

        <div class="mb-3">
            <label for="periodType" class="form-label">Periods</label>
            <select class="form-select" id="periodType" name="periodType" required>
                <option disabled selected>Select Period</option>
                @foreach ($types as $type)
                    <option value="{{ $type->Id }}">{{ $type->PeriodType }}</option>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('budgetperiod.update', $periods->Id) }}">
        @csrf
        @method('PUT')
        <div class="card p-4">
            <h5>✏️ Edit Budget Period</h5>

            <div class="mb-3">
                <label for="fiscalYear" class="form-label">Fiscal Year</label>
                <input type="number" class="form-control" id="fiscalYear" name="fiscalYear"
                       value="{{ old('fiscalYear', $periods->fiscalYear) }}" required>
            </div>

            <div class="mb-3">
                <label for="periodType" class="form-label">Periods</label>
                <select class="form-select" id="periodType" name="periodType" required>
                    <option disabled selected>Select Period</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->PeriodType }}">{{ $type->PeriodType }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes"
                          rows="3">{{ old('notes', $periods->notes) }}</textarea>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary"
                        onclick="this.disabled=true; this.innerText='editing...'; this.form.submit();">💾 Update
                </button>
                <a href="{{ route('budgetperiod.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>

@endsection
