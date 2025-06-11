@extends('layouts.app')
@section('title', 'Budget Scenarios')
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


    <form method="post" action="{{route('budgetscenerios.store')}}">
    @csrf
    <div class="card p-4">
        <h5>📘 Scenario Planning Setup</h5>
        <p class="text-muted">Define budgeting scenarios and select the planning method (top-down, bottom-up, or
            hybrid).</p>

        <div class="mb-3">
            <label for="scenarioName" class="form-label">Scenario Name</label>
            <input type="text" class="form-control" id="scenarioName" name="scenarioName"
                   placeholder="e.g., Base Case, Best Case, Worst Case">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" rows="3" name="description"
                      placeholder="Describe the scenario purpose or assumptions..."></textarea>
        </div>

        <div class="mb-3">
            <label for="budgetPeriod" class="form-label">Budget Period</label>
            <select class="form-select" id="budgetPeriod" name="budgetPeriod">
                <option disabled selected>Select Period</option>
            @foreach ($periods as $period)
                <option value="{{$period->fiscalYear ?? '-'}}">{{$period->fiscalYear ?? '-'}}</option>                
            @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Planning Method</label>
            <select class="form-select" id="planningMethod" name="planningMethod">
                <option disabled selected>Select Method</option>
                @foreach ($methods as $method)
                    <option value="{{ $method->MethodName }}">{{ $method->MethodName }}</option>                  
                @endforeach
            </select>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="isDefault" name="isDefault">
            <label class="form-check-label" for="isDefault" value="1">Set as Default Scenario</label>
        </div>

        <div class="mb-2 d-flex justify-content-between">
             <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();" >💾 Save</button>
             <a href="{{ route('budgetperiod.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
    </form>
@endsection
