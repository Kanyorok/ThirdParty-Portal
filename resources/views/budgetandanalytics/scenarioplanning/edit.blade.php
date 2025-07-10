@extends('layouts.app')
@section('title', 'Edit Budget Scenario')
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

    <form method="post" action="{{ route('budgetscenerios.update', $scenario->Id) }}">
        @csrf
        @method('PUT')

        <div class="card p-4">
            <h5>✏️ Edit Scenario</h5>
            <p class="text-muted">Update the details of this budgeting scenario.</p>

            <div class="mb-3">
                <label for="scenarioName" class="form-label">Scenario Name</label>
                <input type="text" class="form-control" id="scenarioName" name="scenarioName"
                       value="{{ old('scenarioName', $scenario->scenarioName) }}"
                       placeholder="e.g., Base Case, Best Case, Worst Case">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" rows="3" name="description"
                          placeholder="Describe the scenario purpose or assumptions...">{{ old('description', $scenario->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label for="budgetPeriod" class="form-label">Budget Period</label>
                <select class="form-select" id="budgetPeriod" name="budgetPeriod">
                    <option disabled>Select Period</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->Id }}"
                            {{ old('budgetPeriod', $scenario->Id) == $period->fiscalYear ? 'selected' : '' }}>
                            {{ $period->fiscalYear }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Planning Method</label>
                <select class="form-select" id="planningMethod" name="planningMethod">
                    <option disabled>Select Method</option>
                    @foreach ($methods as $method)
                        <option value="{{ $method->Id }}"
                            {{ old('planningMethod', $scenario->Id) == $method->MethodName ? 'selected' : '' }}>
                            {{ $method->MethodName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="isDefault" name="isDefault"
                    {{ old('isDefault', $scenario->isDefault) ? 'checked' : '' }}>
                <label class="form-check-label" for="isDefault">Set as Default Scenario</label>
            </div>

            <div class="mb-2 d-flex justify-content-between">
                <button type="submit" class="btn btn-primary"
                        onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">🔁 Update
                </button>
                <a href="{{ route('budgetscenerios.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
@endsection
