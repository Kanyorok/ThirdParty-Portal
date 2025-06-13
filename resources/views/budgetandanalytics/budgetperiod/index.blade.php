@extends('layouts.app')
@section('title', 'Budget Periods List')
@section('content')

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('budgetperiod.create') }}" class="btn btn-success btn-sm">+ New Period</a>
        </div>
        <h5>📋 Budget Periods List</h5>
        @if ($periods->count())
        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Fiscal Year</th>
                <th>Period Type</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($periods as $period)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$period->fiscalYear ?? '-'}}</td>
                <td>{{ $period->periodTypeID?->PeriodType ?? '-' }}</td>
                <td>{{$period->notes ?? '-'}}</td>
                <td>
                    <div class="d-flex gap-2">
                    <a href="{{route('budgetperiod.edit', $period->Id)}}" class="btn btn-sm btn-info">✏️ Edit</a>
                    
                    <form action="{{route('budgetperiod.destroy', $period->Id)}}" method="POST" style="display: inline-flexbox" onsubmit="return confirm('Are you sure you want to delete this Period?');">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @else
        <div class="alert alert-info">
            No Saved Periods
        </div>
        @endif
    </div>

@endsection
