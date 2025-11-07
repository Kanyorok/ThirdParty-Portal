@extends('layouts.app')
@section('title', 'Procurement Periods List')
@section('content')
<div class="container">
    <h3>All Procurement Periods</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('procurement-periods.create') }}" class="btn btn-primary mb-3">+ New Procurement Period</a>

    @if($periods->count())
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Procurement Number</th>
                    <th>Period Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $period->ProcurementPeriodNumber }}</td>
                        <td><a href="{{ route('procurement-periods.show', $period->Id) }}">{{ $period->Title }}</a></td>
                        <td>{{ $period->StartDate }}</td>
                        <td>{{ $period->EndDate }}</td>                        <td>
                            <a href="{{ route('procurement-periods.edit', $period->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('procurement-periods.destroy', $period->Id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this procurement period?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                            <a href="{{ route('procurement-periods.assign-suppliers-form', $period->Id) }}" class="btn btn-sm btn-info">Link Periods</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No procurement periods found.</p>
    @endif
</div>
@endsection
