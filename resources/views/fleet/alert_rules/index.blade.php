@extends('layouts.app')
@section('title', 'Alert Configuration')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">⚙️ Alert Rules</h4>
        <a href="{{ route('fleet.alert_rules.create') }}" class="btn btn-primary">➕ New Rule</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Trigger</th>
                <th>Value</th>
                <th>Frequency</th>
                <th>Escalation</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rules as $rule)
                <tr>
                    <td>{{ $rule->AlertName }}</td>
                    <td>{{ $rule->TriggerType }}</td>
                    <td>{{ $rule->TriggerValue }}</td>
                    <td>{{ $rule->FrequencyDays }} days</td>
                    <td>{{ $rule->EscalationDays }} days</td>
                    <td>{{ $rule->Description }}</td>
                    <td>
                        <a href="{{ route('fleet.alert_rules.edit', $rule->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
