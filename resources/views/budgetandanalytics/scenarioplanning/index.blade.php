@extends('layouts.app')
@section('title', 'Budget Scenarios')
@section('content')
    <div class="card p-3">

        <div class="mb-2 d-flex justify-content-between">
            <a href="{{ route('budgetscenerios.create') }}" class="btn btn-success">➕ New Scenerio</a>

        </div>
        <h5>📋 Budget Scenarios</h5>
        @if($scenarios->count())
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Scenario Name</th>
                    <th>Description</th>
                    <th>Budget Period</th>
                    <th>Planning Method</th>
                    <th>Default?</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($scenarios as $scenario)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$scenario->scenarioName ?? '-'}}</td>
                        <td>{{$scenario->description ?? '-'}}</td>
                        <td>{{ $scenario->budgetPeriodRef?->periodType ?? '-' }}</td>
                        <td>{{ $scenario->planningMethodRef?->MethodName ?? '-' }}</td>
                        @if ($scenario->isDefault)
                            <td><span class="badge bg-success">Default</span></td>
                        @else
                            <td><span class="badge bg-danger">Not Default</span></td>
                        @endif
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{route('budgetscenerios.edit', $scenario->Id)}}"
                                   class="btn btn-sm btn-outline-primary">✏️ Edit</a>

                                <form action="{{ route('budgetscenerios.destroy', $scenario->Id) }}" method="POST"
                                      style="display:inline-block;"
                                      onsubmit="return confirm('Are you sure you want to delete this scenario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">
                No Scenarios Created
            </div>
        @endif
    </div>
@endsection
