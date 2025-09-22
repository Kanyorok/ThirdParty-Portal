@extends('layouts.app')
@section('title', isset($rule) ? 'Edit Alert Rule' : 'New Alert Rule')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">{{ isset($rule) ? '✏️ Edit Alert Rule' : '➕ New Alert Rule' }}</h4>

        <form method="POST"
              action="{{ isset($rule) ? route('fleet.alert_rules.update', $rule->ID) : route('fleet.alert_rules.store') }}">
            @csrf
            @if(isset($rule))
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="AlertName" class="form-label">Rule Name</label>
                    <input type="text" name="AlertName" class="form-control"
                           value="{{ old('AlertName', $rule->AlertName ?? '') }}" required>
                </div>

                <div class="col-md-6">
                    <label for="TriggerType" class="form-label">Trigger Type</label>
                    <select name="TriggerType" class="form-select" required>
                        <option
                            value="Mileage" {{ old('TriggerType', $rule->TriggerType ?? '') == 'Mileage' ? 'selected' : '' }}>
                            Mileage
                        </option>
                        <option
                            value="Date" {{ old('TriggerType', $rule->TriggerType ?? '') == 'Date' ? 'selected' : '' }}>
                            Date
                        </option>
                        <option
                            value="Schedule" {{ old('TriggerType', $rule->TriggerType ?? '') == 'Schedule' ? 'selected' : '' }}>
                            Schedule
                        </option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="TriggerValue" class="form-label">Trigger Value</label>
                    <input type="number" name="TriggerValue" class="form-control"
                           value="{{ old('TriggerValue', $rule->TriggerValue ?? '') }}">
                </div>

                <div class="col-md-4">
                    <label for="FrequencyDays" class="form-label">Repeat Every (Days)</label>
                    <input type="number" name="FrequencyDays" class="form-control"
                           value="{{ old('FrequencyDays', $rule->FrequencyDays ?? '') }}">
                </div>

                <div class="col-md-4">
                    <label for="EscalationDays" class="form-label">Escalation After (Days)</label>
                    <input type="number" name="EscalationDays" class="form-control"
                           value="{{ old('EscalationDays', $rule->EscalationDays ?? '') }}">
                </div>

                <div class="col-md-12">
                    <label for="Description" class="form-label">Description</label>
                    <textarea name="Description" class="form-control"
                              rows="2">{{ old('Description', $rule->Description ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">💾 Save Rule</button>
            </div>
        </form>
    </div>
@endsection
